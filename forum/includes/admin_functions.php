<?php
/**
 * The Halley Project — Admin Functions
 * Gerenciamento de usuários, artigos e comentários
 */

require_once __DIR__ . '/config.php';

// ══════════════════════════════════════════════════════════════
// GERENCIAMENTO DE USUÁRIOS
// ══════════════════════════════════════════════════════════════

function get_all_users(): array {
    return db()->query("
        SELECT u.*, 
               (SELECT COUNT(*) FROM articles a WHERE a.user_id = u.id) AS article_count,
               (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id) AS comment_count
        FROM users u
        ORDER BY u.created_at DESC
    ")->fetchAll();
}

function admin_update_user(int $user_id, array $data): array {
    $errors = [];

    // Não pode editar a si mesmo para remover admin
    if ($user_id === current_user_id() && isset($data['role']) && $data['role'] !== 'admin') {
        $errors[] = t('You cannot remove your own admin role.', 'Você não pode remover seu próprio cargo de admin.');
        return ['ok' => false, 'errors' => $errors];
    }

    $fields = [];
    $params = [];

    // Username
    if (!empty($data['username'])) {
        $username = trim($data['username']);
        if (strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = t('Username must be 3-30 characters.', 'Nome de usuário deve ter 3-30 caracteres.');
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = t('Username can only contain letters, numbers and underscore.', 'Nome de usuário só pode conter letras, números e underscore.');
        } else {
            // Verificar duplicata
            $stmt = db()->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
            $stmt->execute([$username, $user_id]);
            if ($stmt->fetch()) {
                $errors[] = t('Username already taken.', 'Nome de usuário já em uso.');
            } else {
                $fields[] = 'username = ?';
                $params[] = $username;
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
        }
    }

    // Role
    if (!empty($data['role']) && in_array($data['role'], ['user', 'editor', 'admin'])) {
        $fields[] = 'role = ?';
        $params[] = $data['role'];
    }

    // Bio
    if (isset($data['bio'])) {
        $fields[] = 'bio = ?';
        $params[] = trim($data['bio']);
    }

    // Active status
    if (isset($data['is_active'])) {
        // Não pode desativar a si mesmo
        if ($user_id === current_user_id() && !$data['is_active']) {
            $errors[] = t('You cannot deactivate your own account.', 'Você não pode desativar sua própria conta.');
        } else {
            $fields[] = 'is_active = ?';
            $params[] = (int) $data['is_active'];
        }
    }

    if (!empty($errors)) return ['ok' => false, 'errors' => $errors];
    if (empty($fields)) return ['ok' => false, 'errors' => [t('Nothing to update.', 'Nada para atualizar.')]];

    $params[] = $user_id;
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
    db()->prepare($sql)->execute($params);

    return ['ok' => true];
}

function admin_delete_user(int $user_id): array {
    // Não pode deletar a si mesmo
    if ($user_id === current_user_id()) {
        return ['ok' => false, 'errors' => [
            t('You cannot delete your own account.', 'Você não pode deletar sua própria conta.')
        ]];
    }

    db()->prepare('DELETE FROM users WHERE id = ?')->execute([$user_id]);
    return ['ok' => true];
}

// ══════════════════════════════════════════════════════════════
// GERENCIAMENTO DE ARTIGOS (COMPLETO)
// ══════════════════════════════════════════════════════════════

function get_all_articles(): array {
    return db()->query("
        SELECT a.id, a.title, a.slug, a.status, a.views, a.language,
               a.created_at, a.published_at,
               u.username AS author, u.id AS author_id,
               c.icon AS category_icon, c.name_en AS category_en, c.name_pt AS category_pt,
               (SELECT COUNT(*) FROM comments cm WHERE cm.article_id = a.id) AS comment_count
        FROM articles a
        JOIN users u ON a.user_id = u.id
        JOIN categories c ON a.category_id = c.id
        ORDER BY a.created_at DESC
    ")->fetchAll();
}

function admin_delete_article(int $article_id): void {
    // Comentários são deletados em cascata pela FK
    db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$article_id]);
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
}

// ══════════════════════════════════════════════════════════════
// GERENCIAMENTO DE COMENTÁRIOS
// ══════════════════════════════════════════════════════════════

function get_all_comments_admin(): array {
    return db()->query("
        SELECT c.id, c.body, c.is_hidden, c.created_at,
               u.username AS author, u.id AS user_id,
               a.title AS article_title, a.slug AS article_slug
        FROM comments c
        JOIN users u ON c.user_id = u.id
        JOIN articles a ON c.article_id = a.id
        ORDER BY c.created_at DESC
        LIMIT 100
    ")->fetchAll();
}

function admin_delete_comment(int $comment_id): void {
    // Primeiro remove referências de parent_id
    db()->prepare('UPDATE comments SET parent_id = NULL WHERE parent_id = ?')->execute([$comment_id]);
    db()->prepare('DELETE FROM comments WHERE id = ?')->execute([$comment_id]);
}

function admin_toggle_comment(int $comment_id): void {
    db()->prepare('UPDATE comments SET is_hidden = NOT is_hidden WHERE id = ?')->execute([$comment_id]);
}