<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

init_session();

$slug = $_GET['slug'] ?? '';
$article = get_article_by_slug($slug);

if (!$article) {
    flash('error', t('Article not found.', 'Artigo não encontrado.'));
    redirect(FORUM_URL . '/');
}

// Só mostra se publicado (ou se é o autor/admin)
if ($article['status'] !== 'published') {
    if (!is_logged_in() || (current_user_id() !== (int)$article['user_id'] && !is_admin())) {
        flash('error', t('Article not available.', 'Artigo não disponível.'));
        redirect(FORUM_URL . '/');
    }
}

// Processar novo comentário
$comment_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_body'])) {
    if (!is_logged_in()) {
        $comment_errors[] = t('Please log in to comment.', 'Faça login para comentar.');
    } elseif (!csrf_verify()) {
        $comment_errors[] = t('Invalid request.', 'Requisição inválida.');
    } else {
        $parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $result = add_comment($article['id'], current_user_id(), $_POST['comment_body'], $parent_id);
        if ($result['ok']) {
            flash('success', t('Comment posted!', 'Comentário publicado!'));
            redirect(FORUM_URL . '/article.php?slug=' . urlencode($slug) . '#comments');
        } else {
            $comment_errors = $result['errors'];
        }
    }
}

$comments = get_comments($article['id']);

$page_title = $article['title'];
$page_description = $article['excerpt'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom: var(--spacing-md);">
    <a href="/forum/" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
        &larr; <?= t('Back to Forum', 'Voltar ao Fórum') ?>
    </a>
</div>

<!-- Article -->
<article class="article-full">
    <!-- Header -->
    <div class="article-card-header" style="margin-bottom: var(--spacing-sm);">
        <span class="article-category-badge">
            <?= $article['category_icon'] ?> 
            <?= current_lang() === 'pt-BR' ? $article['category_pt'] : $article['category_en'] ?>
        </span>
        <span>&bull;</span>
        <span><?= $article['language'] === 'pt-BR' ? '🇧🇷 Português' : '🇬🇧 English' ?></span>
    </div>

    <h1><?= sanitize($article['title']) ?></h1>

    <div class="article-meta" style="margin-bottom: var(--spacing-lg);">
        <span>✍️ <?= sanitize($article['author']) ?></span>
        <span>📅 <?= date('M j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?></span>
        <span>👁️ <?= $article['views'] ?> <?= t('views', 'views') ?></span>
        <span>⏱️ <?= reading_time($article['body']) ?></span>
    </div>

    <!-- Body -->
    <div class="article-body">
        <?= parse_markdown($article['body']) ?>
    </div>

    <!-- Author box -->
    <div class="author-box">
        <div class="author-avatar">
            <?= strtoupper(mb_substr($article['author'], 0, 1)) ?>
        </div>
        <div class="author-info">
            <strong><?= sanitize($article['author']) ?></strong>
            <?php if ($article['author_bio']): ?>
                <p><?= sanitize($article['author_bio']) ?></p>
            <?php else: ?>
                <p><?= t('Member of The Halley Project', 'Membro do The Halley Project') ?></p>
            <?php endif; ?>
        </div>
    </div>
</article>

<!-- Comments Section -->
<section class="comments-section" id="comments">
    <h3>💬 <?= t('Comments', 'Comentários') ?> (<?= count_flat_comments($comments) ?>)</h3>

    <?php if (!empty($comment_errors)): ?>
        <div class="form-errors">
            <ul>
                <?php foreach ($comment_errors as $e): ?>
                    <li><?= sanitize($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Comment Form -->
    <?php if (is_logged_in()): ?>
        <form method="POST" action="" style="margin-bottom: var(--spacing-lg);">
            <?= csrf_field() ?>
            <div class="form-group">
                <textarea name="comment_body" rows="3" required
                          placeholder="<?= t('Write a comment...', 'Escreva um comentário...') ?>"
                          minlength="3" maxlength="5000"></textarea>
            </div>
            <button type="submit" class="btn btn-secondary btn-small">
                <?= t('Post Comment', 'Publicar Comentário') ?>
            </button>
        </form>
    <?php else: ?>
        <p style="color: var(--text-muted); margin-bottom: var(--spacing-md);">
            <a href="/forum/login.php" style="color: var(--accent-cyan);">
                <?= t('Sign in to comment', 'Entre para comentar') ?>
            </a>
        </p>
    <?php endif; ?>

    <!-- Comments List -->
    <?php if (empty($comments)): ?>
        <div class="empty-state" style="padding: var(--spacing-md);">
            <p><?= t('No comments yet. Start the discussion!', 'Nenhum comentário ainda. Inicie a discussão!') ?></p>
        </div>
    <?php else: ?>
        <?php render_comments($comments, $article['id']); ?>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
// ── Helper functions for this page ──────────────────────────

function render_comments(array $comments, int $article_id, int $depth = 0): void {
    $wrapper_class = $depth > 0 ? 'comment-replies' : '';
    if ($wrapper_class) echo "<div class=\"{$wrapper_class}\">";
    
    foreach ($comments as $c) {
        ?>
        <div class="comment" id="comment-<?= $c['id'] ?>">
            <div class="comment-header">
                <span class="comment-author"><?= sanitize($c['username']) ?></span>
                <?php if ($c['role'] !== 'user'): ?>
                    <span class="article-category-badge" style="font-size:0.7rem;">
                        <?= $c['role'] === 'admin' ? '⭐ Admin' : '✏️ Editor' ?>
                    </span>
                <?php endif; ?>
                <span class="comment-time">&bull; <?= time_ago($c['created_at']) ?></span>
            </div>
            <div class="comment-body"><?= nl2br(sanitize($c['body'])) ?></div>
            
            <?php if (is_logged_in() && $depth < 3): ?>
                <button class="reply-btn" onclick="toggleReply(<?= $c['id'] ?>)">
                    <?= t('Reply', 'Responder') ?>
                </button>
                <form method="POST" action="" id="reply-form-<?= $c['id'] ?>" style="display:none; margin-top:0.5rem;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">
                    <textarea name="comment_body" rows="2" required minlength="3" maxlength="5000"
                              style="width:100%; margin-bottom:0.5rem;"
                              placeholder="<?= t('Write a reply...', 'Escreva uma resposta...') ?>"></textarea>
                    <button type="submit" class="btn btn-secondary btn-small">
                        <?= t('Reply', 'Responder') ?>
                    </button>
                </form>
            <?php endif; ?>

            <?php if (!empty($c['replies'])): ?>
                <?php render_comments($c['replies'], $article_id, $depth + 1); ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    if ($wrapper_class) echo "</div>";
}

function count_flat_comments(array $comments): int {
    $count = count($comments);
    foreach ($comments as $c) {
        if (!empty($c['replies'])) {
            $count += count_flat_comments($c['replies']);
        }
    }
    return $count;
}
?>

<script>
function toggleReply(id) {
    const form = document.getElementById('reply-form-' + id);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    if (form.style.display === 'block') form.querySelector('textarea').focus();
}
</script>
