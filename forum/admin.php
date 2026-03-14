<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';

require_admin();

$tab = $_GET['tab'] ?? 'articles';
if (!in_array($tab, ['articles', 'users', 'comments', 'trash', 'log'])) $tab = 'articles';

// ── Processar ações POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $redirect_tab = $_POST['tab'] ?? $tab;

    // Artigos
    if ($action === 'delete_article') {
        $id = (int) ($_POST['article_id'] ?? 0);
        if ($id > 0) { admin_delete_article($id); flash('success', t('Article moved to trash.', 'Artigo movido para a lixeira.')); }
    }
    elseif ($action === 'change_article_status') {
        $id = (int) ($_POST['article_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id > 0) { admin_update_article_status($id, $status); flash('success', t('Status updated.', 'Status atualizado.')); }
    }

    // Usuários
    elseif ($action === 'update_user') {
        $uid = (int) ($_POST['user_id'] ?? 0);
        if ($uid > 0) {
            $result = admin_update_user($uid, [
                'username'  => $_POST['username'] ?? '',
                'email'     => $_POST['email'] ?? '',
                'password'  => $_POST['new_password'] ?? '',
                'role'      => $_POST['role'] ?? '',
                'bio'       => $_POST['bio'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ]);
            if ($result['ok']) { flash('success', t('User updated.', 'Usuário atualizado.')); }
            else { foreach ($result['errors'] as $e) flash('error', $e); }
        }
    }
    elseif ($action === 'delete_user') {
        $uid = (int) ($_POST['user_id'] ?? 0);
        if ($uid > 0) {
            $result = admin_delete_user($uid);
            if ($result['ok']) { flash('success', t('User banned.', 'Usuário banido.')); }
            else { foreach ($result['errors'] as $e) flash('error', $e); }
        }
    }

    // Comentários
    elseif ($action === 'delete_comment') {
        $cid = (int) ($_POST['comment_id'] ?? 0);
        if ($cid > 0) { admin_delete_comment($cid); flash('success', t('Comment moved to trash.', 'Comentário movido para a lixeira.')); }
    }
    elseif ($action === 'toggle_comment') {
        $cid = (int) ($_POST['comment_id'] ?? 0);
        if ($cid > 0) { admin_toggle_comment($cid); flash('success', t('Visibility toggled.', 'Visibilidade alterada.')); }
    }

    // Lixeira
    elseif ($action === 'restore_article') {
        $id = (int) ($_POST['article_id'] ?? 0);
        if ($id > 0) { restore_article($id); flash('success', t('Article restored.', 'Artigo restaurado.')); }
    }
    elseif ($action === 'restore_comment') {
        $cid = (int) ($_POST['comment_id'] ?? 0);
        if ($cid > 0) { restore_comment($cid); flash('success', t('Comment restored.', 'Comentário restaurado.')); }
    }
    elseif ($action === 'destroy_article') {
        $id = (int) ($_POST['article_id'] ?? 0);
        if ($id > 0) { permanently_delete_article($id); flash('info', t('Article permanently deleted.', 'Artigo excluído permanentemente.')); }
    }
    elseif ($action === 'destroy_comment') {
        $cid = (int) ($_POST['comment_id'] ?? 0);
        if ($cid > 0) { permanently_delete_comment($cid); flash('info', t('Comment permanently deleted.', 'Comentário excluído permanentemente.')); }
    }

    redirect(FORUM_URL . '/admin.php?tab=' . $redirect_tab);
}

$stats = forum_stats();

$page_title = t('Admin Panel', 'Painel Admin');
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 960px; margin: 0 auto;">
    <div class="forum-header">
        <h1>⚙️ <?= t('Admin Panel', 'Painel de Administração') ?></h1>
    </div>

    <!-- Stats -->
    <div class="forum-stats" style="margin-bottom: var(--spacing-md);">
        <div class="stat-item"><span class="stat-value"><?= $stats['total_users'] ?></span><span class="stat-label"><?= t('Users', 'Usuários') ?></span></div>
        <div class="stat-item"><span class="stat-value"><?= $stats['total_articles'] ?></span><span class="stat-label"><?= t('Published', 'Publicados') ?></span></div>
        <div class="stat-item"><span class="stat-value" style="color:#f59e0b;"><?= $stats['pending_articles'] ?></span><span class="stat-label"><?= t('Pending', 'Pendentes') ?></span></div>
        <div class="stat-item"><span class="stat-value"><?= $stats['total_comments'] ?></span><span class="stat-label"><?= t('Comments', 'Comentários') ?></span></div>
    </div>

    <!-- Tabs -->
    <div class="categories-bar" style="margin-bottom: var(--spacing-lg);">
        <a href="?tab=articles" class="category-chip <?= $tab === 'articles' ? 'active' : '' ?>">📝 <?= t('Articles', 'Artigos') ?></a>
        <a href="?tab=users" class="category-chip <?= $tab === 'users' ? 'active' : '' ?>">👥 <?= t('Users', 'Usuários') ?></a>
        <a href="?tab=comments" class="category-chip <?= $tab === 'comments' ? 'active' : '' ?>">💬 <?= t('Comments', 'Comentários') ?></a>
        <a href="?tab=trash" class="category-chip <?= $tab === 'trash' ? 'active' : '' ?>">🗑️ <?= t('Trash', 'Lixeira') ?></a>
        <a href="?tab=log" class="category-chip <?= $tab === 'log' ? 'active' : '' ?>">📋 <?= t('Mod Log', 'Log') ?></a>
    </div>

    <!-- ═══ TAB: ARTIGOS ═══════════════════════════════════════ -->
    <?php if ($tab === 'articles'): ?>
        <?php $articles = get_all_articles(); ?>
        <div class="form-card">
            <h2 style="color: var(--accent-cyan); margin-bottom: var(--spacing-md);"><?= t('All Articles', 'Todos os Artigos') ?> (<?= count($articles) ?>)</h2>
            <?php if (empty($articles)): ?>
                <div class="empty-state"><p><?= t('No articles yet.', 'Nenhum artigo ainda.') ?></p></div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="admin-table"><thead><tr>
                    <th>#</th><th><?= t('Title', 'Título') ?></th><th><?= t('Author', 'Autor') ?></th>
                    <th><?= t('Status', 'Status') ?></th><th>Views</th><th><?= t('Date', 'Data') ?></th><th><?= t('Actions', 'Ações') ?></th>
                </tr></thead><tbody>
                <?php foreach ($articles as $a): ?>
                    <?php
                    $sc = ['draft'=>'#64748b','review'=>'#f59e0b','published'=>'#22c55e','rejected'=>'#ef4444'];
                    $sl = ['draft'=>t('Draft','Rascunho'),'review'=>t('Review','Revisão'),'published'=>t('Published','Publicado'),'rejected'=>t('Rejected','Rejeitado')];
                    ?>
                    <tr>
                        <td><?= $a['id'] ?></td>
                        <td><?= $a['category_icon'] ?> <a href="/forum/article.php?slug=<?= urlencode($a['slug']) ?>" style="color:var(--text-primary); text-decoration:none;" target="_blank"><?= sanitize(mb_substr($a['title'],0,40)) ?><?= mb_strlen($a['title'])>40?'...':'' ?></a> <?= $a['language']==='pt-BR'?'🇧🇷':'🇬🇧' ?></td>
                        <td><?= sanitize($a['author']) ?></td>
                        <td><span style="color:<?= $sc[$a['status']] ?>;">● <?= $sl[$a['status']] ?></span></td>
                        <td><?= $a['views'] ?></td>
                        <td><?= date('d/m/y', strtotime($a['created_at'])) ?></td>
                        <td><div class="admin-actions" style="flex-wrap:wrap;">
                            <form method="POST" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="tab" value="articles"><input type="hidden" name="article_id" value="<?= $a['id'] ?>"><input type="hidden" name="action" value="change_article_status">
                                <select name="status" onchange="this.form.submit()" style="background:rgba(15,23,42,0.6); border:1px solid var(--border-color); color:var(--text-secondary); border-radius:4px; padding:0.2rem; font-size:0.75rem;">
                                <?php foreach(['draft','review','published','rejected'] as $s): ?><option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= $sl[$s] ?></option><?php endforeach; ?>
                                </select></form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= t('Move to trash?','Mover para lixeira?') ?>')"><?= csrf_field() ?><input type="hidden" name="tab" value="articles"><input type="hidden" name="article_id" value="<?= $a['id'] ?>"><input type="hidden" name="action" value="delete_article">
                                <button type="submit" class="btn btn-reject btn-small" style="padding:0.2rem 0.5rem; font-size:0.75rem;">🗑️</button></form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>

    <!-- ═══ TAB: USUÁRIOS ══════════════════════════════════════ -->
    <?php elseif ($tab === 'users'): ?>
        <?php
        $users = get_all_users();
        $edit_id = (int)($_GET['edit'] ?? 0);
        $edit_user = null;
        if ($edit_id > 0) { $stmt = db()->prepare('SELECT * FROM users WHERE id = ?'); $stmt->execute([$edit_id]); $edit_user = $stmt->fetch(); }
        ?>

        <?php if ($edit_user): ?>
        <div class="form-card" style="margin-bottom:var(--spacing-md);">
            <h2 style="color:var(--accent-cyan); margin-bottom:var(--spacing-md);">✏️ <?= t('Edit User','Editar Usuário') ?>: <?= sanitize($edit_user['username']) ?>
                <a href="?tab=users" style="float:right; color:var(--text-muted); text-decoration:none;">&times;</a></h2>
            <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="update_user"><input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>"><input type="hidden" name="tab" value="users">
                <div style="display:flex; gap:var(--spacing-sm); flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:200px;"><label><?= t('Username','Usuário') ?></label><input type="text" name="username" value="<?= sanitize($edit_user['username']) ?>" minlength="3" maxlength="30"></div>
                    <div class="form-group" style="flex:1; min-width:200px;"><label>Email</label><input type="email" name="email" value="<?= sanitize($edit_user['email']) ?>"></div>
                </div>
                <div style="display:flex; gap:var(--spacing-sm); flex-wrap:wrap;">
                    <div class="form-group" style="flex:1; min-width:200px;"><label><?= t('New Password','Nova Senha') ?></label><input type="password" name="new_password" minlength="8" placeholder="<?= t('Leave empty to keep','Vazio mantém atual') ?>"></div>
                    <div class="form-group" style="flex:1; min-width:200px;"><label><?= t('Role','Cargo') ?></label>
                        <select name="role"><option value="user" <?= $edit_user['role']==='user'?'selected':'' ?>>👤 User</option><option value="editor" <?= $edit_user['role']==='editor'?'selected':'' ?>>✏️ Editor</option><option value="admin" <?= $edit_user['role']==='admin'?'selected':'' ?>>⭐ Admin</option></select></div>
                </div>
                <div class="form-group"><label>Bio</label><input type="text" name="bio" value="<?= sanitize($edit_user['bio'] ?? '') ?>" maxlength="500"></div>
                <div style="margin-bottom:var(--spacing-md);"><label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; color:var(--text-secondary);"><input type="checkbox" name="is_active" value="1" <?= $edit_user['is_active']?'checked':'' ?> style="width:auto;"> <?= t('Account Active','Conta Ativa') ?></label></div>
                <button type="submit" class="btn btn-primary" style="width:auto;">💾 <?= t('Save','Salvar') ?></button>
                <a href="?tab=users" class="btn btn-secondary btn-small"><?= t('Cancel','Cancelar') ?></a>
            </form>
        </div>
        <?php endif; ?>

        <div class="form-card">
            <h2 style="color:var(--accent-cyan); margin-bottom:var(--spacing-md);">👥 <?= t('All Users','Todos os Usuários') ?> (<?= count($users) ?>)</h2>
            <div style="overflow-x:auto;">
            <table class="admin-table"><thead><tr>
                <th>#</th><th><?= t('User','Usuário') ?></th><th>Email</th><th><?= t('Role','Cargo') ?></th><th><?= t('Articles','Artigos') ?></th><th>Status</th><th><?= t('Joined','Desde') ?></th><th><?= t('Actions','Ações') ?></th>
            </tr></thead><tbody>
            <?php foreach ($users as $u): ?>
                <?php $rb = ['admin'=>['⭐ Admin','#f59e0b'],'editor'=>['✏️ Editor','#a78bfa'],'user'=>['👤 User','#64748b']]; $b = $rb[$u['role']] ?? $rb['user']; ?>
                <tr style="<?= !$u['is_active']?'opacity:0.5;':'' ?>">
                    <td><?= $u['id'] ?></td>
                    <td><strong style="color:var(--text-primary);"><?= sanitize($u['username']) ?></strong></td>
                    <td style="font-size:0.8rem;"><?= sanitize($u['email']) ?></td>
                    <td><span style="color:<?= $b[1] ?>; font-size:0.8rem;"><?= $b[0] ?></span></td>
                    <td><?= $u['article_count'] ?></td>
                    <td><?= $u['is_active'] ? '<span style="color:#22c55e;">● '.t('Active','Ativo').'</span>' : '<span style="color:#ef4444;">● '.t('Banned','Banido').'</span>' ?></td>
                    <td style="font-size:0.8rem;"><?= date('d/m/y', strtotime($u['created_at'])) ?></td>
                    <td><div class="admin-actions">
                        <a href="?tab=users&edit=<?= $u['id'] ?>" class="btn btn-secondary btn-small" style="padding:0.2rem 0.5rem; font-size:0.75rem;">✏️</a>
                        <?php if ($u['id'] !== current_user_id()): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('<?= t('Ban this user?','Banir este usuário?') ?>')"><?= csrf_field() ?><input type="hidden" name="tab" value="users"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="delete_user">
                            <button type="submit" class="btn btn-reject btn-small" style="padding:0.2rem 0.5rem; font-size:0.75rem;">🚫</button></form>
                        <?php endif; ?>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
            <div style="margin-top:var(--spacing-md); padding:var(--spacing-sm); background:rgba(15,23,42,0.4); border-radius:var(--radius-sm); font-size:0.8rem; color:var(--text-muted);">
                <strong style="color:var(--text-secondary);"><?= t('Roles:','Cargos:') ?></strong><br>
                <span style="color:#f59e0b;">⭐ Admin</span> — <?= t('Full control','Controle total') ?> |
                <span style="color:#a78bfa;">✏️ Editor</span> — <?= t('Publish without review','Publica sem revisão') ?> |
                <span style="color:#64748b;">👤 User</span> — <?= t('Needs approval','Precisa de aprovação') ?>
            </div>
        </div>

    <!-- ═══ TAB: COMENTÁRIOS ═══════════════════════════════════ -->
    <?php elseif ($tab === 'comments'): ?>
        <?php $comments = get_all_comments_admin(); ?>
        <div class="form-card">
            <h2 style="color:var(--accent-cyan); margin-bottom:var(--spacing-md);">💬 <?= t('Comments','Comentários') ?> (<?= count($comments) ?>)</h2>
            <?php if (empty($comments)): ?>
                <div class="empty-state"><p><?= t('No comments.','Nenhum comentário.') ?></p></div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="admin-table"><thead><tr>
                    <th>#</th><th><?= t('Author','Autor') ?></th><th><?= t('Comment','Comentário') ?></th><th><?= t('Article','Artigo') ?></th><th><?= t('Date','Data') ?></th><th><?= t('Actions','Ações') ?></th>
                </tr></thead><tbody>
                <?php foreach ($comments as $c): ?>
                    <tr style="<?= $c['is_hidden']?'opacity:0.5;':'' ?>">
                        <td><?= $c['id'] ?></td>
                        <td style="color:var(--accent-cyan);"><?= sanitize($c['author']) ?></td>
                        <td style="max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= sanitize(mb_substr($c['body'],0,60)) ?></td>
                        <td style="font-size:0.8rem;"><a href="/forum/article.php?slug=<?= urlencode($c['article_slug']) ?>#comment-<?= $c['id'] ?>" style="color:var(--text-secondary); text-decoration:none;" target="_blank"><?= sanitize(mb_substr($c['article_title'],0,25)) ?></a></td>
                        <td style="font-size:0.8rem;"><?= time_ago($c['created_at']) ?></td>
                        <td><div class="admin-actions">
                            <form method="POST" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="tab" value="comments"><input type="hidden" name="comment_id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="toggle_comment">
                                <button type="submit" class="btn btn-secondary btn-small" style="padding:0.2rem 0.5rem; font-size:0.75rem;"><?= $c['is_hidden']?'👁️':'🙈' ?></button></form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= t('Move to trash?','Mover para lixeira?') ?>')"><?= csrf_field() ?><input type="hidden" name="tab" value="comments"><input type="hidden" name="comment_id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="delete_comment">
                                <button type="submit" class="btn btn-reject btn-small" style="padding:0.2rem 0.5rem; font-size:0.75rem;">🗑️</button></form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>

    <!-- ═══ TAB: LIXEIRA ═══════════════════════════════════════ -->
    <?php elseif ($tab === 'trash'): ?>
        <?php $trashed_articles = get_trashed_articles(); $trashed_comments = get_trashed_comments(); ?>

        <!-- Artigos na lixeira -->
        <div class="form-card" style="margin-bottom:var(--spacing-md);">
            <h2 style="color:#f59e0b; margin-bottom:var(--spacing-md);">🗑️ <?= t('Trashed Articles','Artigos na Lixeira') ?> (<?= count($trashed_articles) ?>)</h2>
            <?php if (empty($trashed_articles)): ?>
                <div class="empty-state"><p><?= t('Trash is empty.','Lixeira vazia.') ?></p></div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="admin-table"><thead><tr>
                    <th>#</th><th><?= t('Title','Título') ?></th><th><?= t('Author','Autor') ?></th><th><?= t('Deleted by','Excluído por') ?></th><th><?= t('When','Quando') ?></th><th><?= t('Actions','Ações') ?></th>
                </tr></thead><tbody>
                <?php foreach ($trashed_articles as $a): ?>
                    <tr>
                        <td><?= $a['id'] ?></td>
                        <td><?= sanitize(mb_substr($a['title'],0,45)) ?> <?= $a['language']==='pt-BR'?'🇧🇷':'🇬🇧' ?></td>
                        <td><?= sanitize($a['author']) ?></td>
                        <td style="color:var(--accent-purple);"><?= sanitize($a['deleted_by_name'] ?? '?') ?></td>
                        <td style="font-size:0.8rem;"><?= time_ago($a['deleted_at']) ?></td>
                        <td><div class="admin-actions">
                            <form method="POST" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="tab" value="trash"><input type="hidden" name="article_id" value="<?= $a['id'] ?>"><input type="hidden" name="action" value="restore_article">
                                <button type="submit" class="btn btn-approve btn-small" style="padding:0.2rem 0.5rem; font-size:0.75rem;">♻️ <?= t('Restore','Restaurar') ?></button></form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= t('PERMANENTLY delete? This cannot be undone!','Excluir PERMANENTEMENTE? Isso não pode ser desfeito!') ?>')"><?= csrf_field() ?><input type="hidden" name="tab" value="trash"><input type="hidden" name="article_id" value="<?= $a['id'] ?>"><input type="hidden" name="action" value="destroy_article">
                                <button type="submit" class="btn btn-reject btn-small" style="padding:0.2rem 0.5rem; font-size:0.75rem;">💀</button></form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>

        <!-- Comentários na lixeira -->
        <div class="form-card">
            <h2 style="color:#f59e0b; margin-bottom:var(--spacing-md);">🗑️ <?= t('Trashed Comments','Comentários na Lixeira') ?> (<?= count($trashed_comments) ?>)</h2>
            <?php if (empty($trashed_comments)): ?>
                <div class="empty-state"><p><?= t('No trashed comments.','Nenhum comentário na lixeira.') ?></p></div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="admin-table"><thead><tr>
                    <th>#</th><th><?= t('Author','Autor') ?></th><th><?= t('Comment','Comentário') ?></th><th><?= t('Deleted by','Excluído por') ?></th><th><?= t('When','Quando') ?></th><th><?= t('Actions','Ações') ?></th>
                </tr></thead><tbody>
                <?php foreach ($trashed_comments as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td style="color:var(--accent-cyan);"><?= sanitize($c['author']) ?></td>
                        <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= sanitize(mb_substr($c['body'],0,50)) ?></td>
                        <td style="color:var(--accent-purple);"><?= sanitize($c['deleted_by_name'] ?? '?') ?></td>
                        <td style="font-size:0.8rem;"><?= time_ago($c['deleted_at']) ?></td>
                        <td><div class="admin-actions">
                            <form method="POST" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="tab" value="trash"><input type="hidden" name="comment_id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="restore_comment">
                                <button type="submit" class="btn btn-approve btn-small" style="padding:0.2rem 0.5rem; font-size:0.75rem;">♻️</button></form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= t('Permanently delete?','Excluir permanentemente?') ?>')"><?= csrf_field() ?><input type="hidden" name="tab" value="trash"><input type="hidden" name="comment_id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="destroy_comment">
                                <button type="submit" class="btn btn-reject btn-small" style="padding:0.2rem 0.5rem; font-size:0.75rem;">💀</button></form>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>

    <!-- ═══ TAB: LOG DE MODERAÇÃO ══════════════════════════════ -->
    <?php elseif ($tab === 'log'): ?>
        <?php $logs = get_mod_log(50); ?>
        <div class="form-card">
            <h2 style="color:var(--accent-cyan); margin-bottom:var(--spacing-md);">📋 <?= t('Moderation Log','Log de Moderação') ?> (<?= t('last 50','últimos 50') ?>)</h2>
            <?php if (empty($logs)): ?>
                <div class="empty-state"><p><?= t('No actions logged yet.','Nenhuma ação registrada.') ?></p></div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                <table class="admin-table"><thead><tr>
                    <th><?= t('When','Quando') ?></th><th><?= t('Admin','Admin') ?></th><th><?= t('Action','Ação') ?></th><th><?= t('Target','Alvo') ?></th><th><?= t('Details','Detalhes') ?></th>
                </tr></thead><tbody>
                <?php
                $action_labels = [
                    'article_trashed'   => ['🗑️ Trashed article',   '🗑️ Artigo na lixeira'],
                    'article_restored'  => ['♻️ Restored article',  '♻️ Artigo restaurado'],
                    'article_destroyed' => ['💀 Destroyed article', '💀 Artigo destruído'],
                    'article_status'    => ['📝 Status changed',    '📝 Status alterado'],
                    'comment_trashed'   => ['🗑️ Trashed comment',   '🗑️ Comentário na lixeira'],
                    'comment_restored'  => ['♻️ Restored comment',  '♻️ Comentário restaurado'],
                    'comment_destroyed' => ['💀 Destroyed comment', '💀 Comentário destruído'],
                    'comment_toggled'   => ['🙈 Toggled comment',   '🙈 Visibilidade alterada'],
                    'user_updated'      => ['✏️ Updated user',      '✏️ Usuário editado'],
                    'user_banned'       => ['🚫 Banned user',       '🚫 Usuário banido'],
                ];
                ?>
                <?php foreach ($logs as $log): ?>
                    <?php $lbl = $action_labels[$log['action']] ?? [$log['action'], $log['action']]; ?>
                    <tr>
                        <td style="font-size:0.8rem; white-space:nowrap;"><?= date('d/m H:i', strtotime($log['created_at'])) ?></td>
                        <td style="color:var(--accent-purple);"><?= sanitize($log['admin_name']) ?></td>
                        <td><?= t($lbl[0], $lbl[1]) ?></td>
                        <td style="font-size:0.8rem;"><?= ucfirst($log['target_type']) ?> #<?= $log['target_id'] ?></td>
                        <td style="font-size:0.75rem; color:var(--text-muted); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= sanitize($log['details']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>