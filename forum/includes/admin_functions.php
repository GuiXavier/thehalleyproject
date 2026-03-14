<?php
/**
 * The Halley Project — Admin Functions
 * Soft-delete, lixeira, restauração, log de moderação
 */

require_once __DIR__ . '/config.php';

// ══════════════════════════════════════════════════════════════
// LOG DE MODERAÇÃO
// ══════════════════════════════════════════════════════════════

function mod_log(string $action, string $target_type, int $target_id, string $details = ''): void {
    $stmt = db()->prepare(
        'INSERT INTO mod_log (admin_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([current_user_id(), $action, $target_type, $target_id, $details]);
}

function get_mod_log(int $limit = 50): array {
    $stmt = db()->prepare("
        SELECT ml.*, u.username AS admin_name
        FROM mod_log ml
        JOIN users u ON ml.admin_id = u.id
        ORDER BY ml.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// ══════════════════════════════════════════════════════════════
// GERENCIAMENTO DE USUÁRIOS
// ══════════════════════════════════════════════════════════════

function get_all_users(): array {
    return db()->query("
        SELECT u.*, 
               (SELECT COUNT(*) FROM articles a WHERE a.user_id = u.id AND a.deleted_at IS NULL) AS article_count,
               (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id AND c.deleted_at IS NULL) AS comment_count
        FROM users u
        ORDER BY u.created_at DESC
    ")->fetchAll();
}

function admin_update_user(int $user_id, array $data): array {
    $errors = [];

    if ($user_id === current_user_id() && isset($data['role']) && $data['role'] !== 'admin') {
        $errors[] = t('You cannot remove your own admin role.', 'Você não pode remover seu próprio cargo de admin.');
        return ['ok' => false, 'errors' => $errors];
    }

    $fields = [];
    $params = [];
    $changes = [];

    // Username
    if (!empty($data['username'])) {
        $username = trim($data['username']);
        if (strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = t('Username must be 3-30 characters.', 'Nome de usuário deve ter 3-30 caracteres.');
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = t('Username can only contain letters, numbers and underscore.', 'Nome de usuário só pode conter letras, números e underscore.');
        } else {
            $stmt = db()->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
            $stmt->execute([$username, $user_id]);
            if ($stmt->fetch()) {
                $errors[] = t('Username already taken.', 'Nome de usuário já em uso.');
            } else {
                $fields[] = 'username = ?';
                $params[] = $username;
                $changes[] = "username → {$username}";
            }
        }
    }

    // Email
    if (!empty($data['email'])) {
        $email = trim(strtolower($data['email']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = t('Invalid email address.', 'Endereço de email inválido.');
        } else {
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $errors[] = t('Email already in use.', 'Email já em uso.');
            } else {
                $fields[] = 'email = ?';
                $params[] = $email;
                $changes[] = "email changed";
            }
        }
    }

    // Password
    if (!empty($data['password'])) {
        if (strlen($data['password']) < 8) {
            $errors[] = t('Password must be at least 8 characters.', 'Senha deve ter pelo menos 8 caracteres.');
        } else {
            $fields[] = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
            $changes[] = "password reset";
        }
    }

    // Role
    if (!empty($data['role']) && in_array($data['role'], ['user', 'editor', 'admin'])) {
        $fields[] = 'role = ?';
        $params[] = $data['role'];
        $changes[] = "role → {$data['role']}";
    }

    // Bio
    if (isset($data['bio'])) {
        $fields[] = 'bio = ?';
        $params[] = trim($data['bio']);
    }

    // Active status
    if (isset($data['is_active'])) {
        if ($user_id === current_user_id() && !$data['is_active']) {
            $errors[] = t('You cannot deactivate your own account.', 'Você não pode desativar sua própria conta.');
        } else {
            $fields[] = 'is_active = ?';
            $params[] = (int) $data['is_active'];
            $changes[] = $data['is_active'] ? "reactivated" : "banned";
        }
    }

    if (!empty($errors)) return ['ok' => false, 'errors' => $errors];
    if (empty($fields)) return ['ok' => false, 'errors' => [t('Nothing to update.', 'Nada para atualizar.')]];

    $params[] = $user_id;
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
    db()->prepare($sql)->execute($params);

    // Log
    if (!empty($changes)) {
        mod_log('user_updated', 'user', $user_id, implode(', ', $changes));
    }

    return ['ok' => true];
}

function admin_delete_user(int $user_id): array {
    if ($user_id === current_user_id()) {
        return ['ok' => false, 'errors' => [
            t('You cannot delete your own account.', 'Você não pode deletar sua própria conta.')
        ]];
    }

    // Pegar info antes de deletar
    $stmt = db()->prepare('SELECT username FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Desativar em vez de deletar (preservar histórico)
    db()->prepare('UPDATE users SET is_active = 0 WHERE id = ?')->execute([$user_id]);

    mod_log('user_banned', 'user', $user_id, 'user: ' . ($user['username'] ?? 'unknown'));

    return ['ok' => true];
}

// ══════════════════════════════════════════════════════════════
// GERENCIAMENTO DE ARTIGOS (SOFT DELETE)
// ══════════════════════════════════════════════════════════════

function get_all_articles(): array {
    return db()->query("
        SELECT a.id, a.title, a.slug, a.status, a.views, a.language,
               a.created_at, a.published_at,
               u.username AS author, u.id AS author_id,
               c.icon AS category_icon, c.name_en AS category_en, c.name_pt AS category_pt,
               (SELECT COUNT(*) FROM comments cm WHERE cm.article_id = a.id AND cm.deleted_at IS NULL) AS comment_count
        FROM articles a
        JOIN users u ON a.user_id = u.id
        JOIN categories c ON a.category_id = c.id
        WHERE a.deleted_at IS NULL
        ORDER BY a.created_at DESC
    ")->fetchAll();
}

function admin_delete_article(int $article_id): void {
    // Pegar info para o log
    $stmt = db()->prepare('SELECT title, user_id FROM articles WHERE id = ?');
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();

    // Soft delete artigo
    $stmt = db()->prepare('UPDATE articles SET deleted_at = NOW(), deleted_by = ? WHERE id = ?');
    $stmt->execute([current_user_id(), $article_id]);

    // Soft delete comentários do artigo
    $stmt = db()->prepare('UPDATE comments SET deleted_at = NOW(), deleted_by = ? WHERE article_id = ? AND deleted_at IS NULL');
    $stmt->execute([current_user_id(), $article_id]);

    mod_log('article_trashed', 'article', $article_id, 'title: ' . ($article['title'] ?? ''));
}

function admin_update_article_status(int $article_id, string $status): void {
    $valid = ['draft', 'review', 'published', 'rejected'];
    if (!in_array($status, $valid)) return;

    $stmt = db()->prepare('UPDATE articles SET status = ? WHERE id = ?');
    $stmt->execute([$status, $article_id]);

    if ($status === 'published') {
        db()->prepare('UPDATE articles SET published_at = NOW() WHERE id = ? AND published_at IS NULL')
            ->execute([$article_id]);
    }

    mod_log('article_status', 'article', $article_id, "status → {$status}");
}

// ══════════════════════════════════════════════════════════════
// GERENCIAMENTO DE COMENTÁRIOS (SOFT DELETE)
// ══════════════════════════════════════════════════════════════

function get_all_comments_admin(): array {
    return db()->query("
        SELECT c.id, c.body, c.is_hidden, c.created_at,
               u.username AS author, u.id AS user_id,
               a.title AS article_title, a.slug AS article_slug
        FROM comments c
        JOIN users u ON c.user_id = u.id
        JOIN articles a ON c.article_id = a.id
        WHERE c.deleted_at IS NULL
        ORDER BY c.created_at DESC
        LIMIT 100
    ")->fetchAll();
}

function admin_delete_comment(int $comment_id): void {
    // Pegar info para o log
    $stmt = db()->prepare('SELECT c.body, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?');
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    // Soft delete
    $stmt = db()->prepare('UPDATE comments SET deleted_at = NOW(), deleted_by = ? WHERE id = ?');
    $stmt->execute([current_user_id(), $comment_id]);

    // Soft delete respostas filhas
    $stmt = db()->prepare('UPDATE comments SET deleted_at = NOW(), deleted_by = ? WHERE parent_id = ? AND deleted_at IS NULL');
    $stmt->execute([current_user_id(), $comment_id]);

    $detail = 'by: ' . ($comment['username'] ?? '') . ' | ' . mb_substr($comment['body'] ?? '', 0, 80);
    mod_log('comment_trashed', 'comment', $comment_id, $detail);
}

function admin_toggle_comment(int $comment_id): void {
    db()->prepare('UPDATE comments SET is_hidden = NOT is_hidden WHERE id = ?')->execute([$comment_id]);
    mod_log('comment_toggled', 'comment', $comment_id, '');
}

// ══════════════════════════════════════════════════════════════
// LIXEIRA
// ══════════════════════════════════════════════════════════════

function get_trashed_articles(): array {
    return db()->query("
        SELECT a.id, a.title, a.slug, a.status, a.language, a.deleted_at,
               u.username AS author,
               del.username AS deleted_by_name
        FROM articles a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN users del ON a.deleted_by = del.id
        WHERE a.deleted_at IS NOT NULL
        ORDER BY a.deleted_at DESC
    ")->fetchAll();
}

function get_trashed_comments(): array {
    return db()->query("
        SELECT c.id, c.body, c.deleted_at,
               u.username AS author,
               del.username AS deleted_by_name,
               a.title AS article_title, a.slug AS article_slug
        FROM comments c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN users del ON c.deleted_by = del.id
        JOIN articles a ON c.article_id = a.id
        WHERE c.deleted_at IS NOT NULL
        ORDER BY c.deleted_at DESC
        LIMIT 100
    ")->fetchAll();
}

function restore_article(int $article_id): void {
    db()->prepare('UPDATE articles SET deleted_at = NULL, deleted_by = NULL WHERE id = ?')
        ->execute([$article_id]);
    // Restaurar comentários que foram deletados junto
    db()->prepare('UPDATE comments SET deleted_at = NULL, deleted_by = NULL WHERE article_id = ? AND deleted_at IS NOT NULL')
        ->execute([$article_id]);
    mod_log('article_restored', 'article', $article_id, '');
}

function restore_comment(int $comment_id): void {
    db()->prepare('UPDATE comments SET deleted_at = NULL, deleted_by = NULL WHERE id = ?')
        ->execute([$comment_id]);
    mod_log('comment_restored', 'comment', $comment_id, '');
}

function permanently_delete_article(int $article_id): void {
    $stmt = db()->prepare('SELECT title FROM articles WHERE id = ?');
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();

    db()->prepare('DELETE FROM comments WHERE article_id = ?')->execute([$article_id]);
    db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$article_id]);
    mod_log('article_destroyed', 'article', $article_id, 'title: ' . ($article['title'] ?? ''));
}

function permanently_delete_comment(int $comment_id): void {
    db()->prepare('UPDATE comments SET parent_id = NULL WHERE parent_id = ?')->execute([$comment_id]);
    db()->prepare('DELETE FROM comments WHERE id = ?')->execute([$comment_id]);
    mod_log('comment_destroyed', 'comment', $comment_id, '');
}