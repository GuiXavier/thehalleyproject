<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_admin();

// Ações de moderação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $article_id = (int) ($_POST['article_id'] ?? 0);

    if ($article_id > 0) {
        if ($action === 'publish') {
            publish_article($article_id);
            flash('success', t('Article published.', 'Artigo publicado.'));
        } elseif ($action === 'reject') {
            reject_article($article_id);
            flash('info', t('Article rejected.', 'Artigo rejeitado.'));
        }
    }
    redirect(FORUM_URL . '/admin.php');
}

$pending = get_pending_articles();
$stats   = forum_stats();

$page_title = t('Admin Panel', 'Painel Admin');
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container wide">
    <div class="forum-header">
        <h1><?= t('Admin Panel', 'Painel de Administração') ?></h1>
    </div>

    <!-- Stats -->
    <div class="forum-stats" style="margin-bottom: var(--spacing-lg);">
        <div class="stat-item">
            <span class="stat-value"><?= $stats['total_users'] ?></span>
            <span class="stat-label"><?= t('Users', 'Usuários') ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-value"><?= $stats['total_articles'] ?></span>
            <span class="stat-label"><?= t('Published', 'Publicados') ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-value" style="color: #f59e0b;"><?= $stats['pending_articles'] ?></span>
            <span class="stat-label"><?= t('Pending', 'Pendentes') ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-value"><?= $stats['total_comments'] ?></span>
            <span class="stat-label"><?= t('Comments', 'Comentários') ?></span>
        </div>
    </div>

    <!-- Pending Articles -->
    <div class="form-card">
        <h2 style="color: var(--accent-cyan); margin-bottom: var(--spacing-md);">
            <?= t('Pending Review', 'Aguardando Revisão') ?> (<?= count($pending) ?>)
        </h2>

        <?php if (empty($pending)): ?>
            <div class="empty-state">
                <p><?= t('No articles pending review.', 'Nenhum artigo aguardando revisão.') ?></p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= t('Title', 'Título') ?></th>
                        <th><?= t('Author', 'Autor') ?></th>
                        <th><?= t('Date', 'Data') ?></th>
                        <th><?= t('Actions', 'Ações') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $a): ?>
                        <tr>
                            <td>
                                <?= $a['language'] === 'pt-BR' ? '🇧🇷' : '🇬🇧' ?>
                                <a href="/forum/article.php?slug=<?= urlencode($a['slug']) ?>" 
                                   style="color: var(--text-primary); text-decoration: none;" target="_blank">
                                    <?= sanitize($a['title']) ?>
                                </a>
                            </td>
                            <td><?= sanitize($a['author']) ?></td>
                            <td><?= time_ago($a['created_at']) ?></td>
                            <td>
                                <div class="admin-actions">
                                    <form method="POST" action="" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="article_id" value="<?= $a['id'] ?>">
                                        <input type="hidden" name="action" value="publish">
                                        <button type="submit" class="btn btn-approve btn-small">
                                            ✓ <?= t('Publish', 'Publicar') ?>
                                        </button>
                                    </form>
                                    <form method="POST" action="" style="display:inline;" 
                                          onsubmit="return confirm('<?= t('Reject this article?', 'Rejeitar este artigo?') ?>')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="article_id" value="<?= $a['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-reject btn-small">
                                            ✕ <?= t('Reject', 'Rejeitar') ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
