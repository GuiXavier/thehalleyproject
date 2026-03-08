<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$user = get_user(current_user_id());
if (!$user) { logout(); redirect(FORUM_URL . '/'); }

// Meus artigos
$stmt = db()->prepare("
    SELECT a.id, a.title, a.slug, a.status, a.views, a.created_at,
           c.icon AS category_icon
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([current_user_id()]);
$my_articles = $stmt->fetchAll();

$page_title = t('My Profile', 'Meu Perfil');
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container wide">
    <!-- Profile Card -->
    <div class="form-card" style="text-align: center; margin-bottom: var(--spacing-md);">
        <div class="author-avatar" style="width:64px; height:64px; font-size:1.8rem; margin:0 auto var(--spacing-sm);">
            <?= strtoupper(mb_substr($user['username'], 0, 1)) ?>
        </div>
        <h1 style="font-size:1.5rem;"><?= sanitize($user['username']) ?></h1>
        <p style="color: var(--text-muted); margin-bottom: var(--spacing-xs);">
            <?= sanitize($user['email']) ?> &bull; 
            <?= ucfirst($user['role']) ?> &bull;
            <?= t('Joined', 'Membro desde') ?> <?= date('M Y', strtotime($user['created_at'])) ?>
        </p>
        
        <div style="margin-top: var(--spacing-md); display:flex; gap:var(--spacing-xs); justify-content:center; flex-wrap:wrap;">
            <a href="/forum/submit.php" class="btn btn-primary" style="width:auto;">
                <?= t('+ Write Article', '+ Escrever Artigo') ?>
            </a>
            <?php if (is_admin()): ?>
                <a href="/forum/admin.php" class="btn btn-secondary btn-small">
                    <?= t('Admin Panel', 'Painel Admin') ?>
                </a>
            <?php endif; ?>
            <a href="/forum/logout.php" class="btn btn-secondary btn-small">
                <?= t('Sign Out', 'Sair') ?>
            </a>
        </div>
    </div>

    <!-- My Articles -->
    <div class="form-card">
        <h2 style="color: var(--accent-cyan); margin-bottom: var(--spacing-md);">
            <?= t('My Articles', 'Meus Artigos') ?> (<?= count($my_articles) ?>)
        </h2>

        <?php if (empty($my_articles)): ?>
            <div class="empty-state">
                <p><?= t('You haven\'t written any articles yet.', 'Você ainda não escreveu nenhum artigo.') ?></p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= t('Title', 'Título') ?></th>
                        <th><?= t('Status', 'Status') ?></th>
                        <th><?= t('Views', 'Views') ?></th>
                        <th><?= t('Date', 'Data') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_articles as $a): ?>
                        <tr>
                            <td>
                                <?= $a['category_icon'] ?>
                                <?php if ($a['status'] === 'published'): ?>
                                    <a href="/forum/article.php?slug=<?= urlencode($a['slug']) ?>" style="color: var(--accent-cyan); text-decoration: none;">
                                        <?= sanitize($a['title']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= sanitize($a['title']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status_colors = [
                                    'draft'     => '#64748b',
                                    'review'    => '#f59e0b',
                                    'published' => '#22c55e',
                                    'rejected'  => '#ef4444',
                                ];
                                $color = $status_colors[$a['status']] ?? '#64748b';
                                $label = [
                                    'draft' => t('Draft', 'Rascunho'),
                                    'review' => t('In Review', 'Em Revisão'),
                                    'published' => t('Published', 'Publicado'),
                                    'rejected' => t('Rejected', 'Rejeitado'),
                                ][$a['status']] ?? $a['status'];
                                ?>
                                <span style="color: <?= $color ?>;">● <?= $label ?></span>
                            </td>
                            <td><?= $a['views'] ?></td>
                            <td><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
