<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/admin_functions.php';

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

// ── Processar ações POST ─────────────────────────────────────
$comment_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? 'comment';

    // ── Ações de Admin no Artigo ─────────────────────────────
    if ($action === 'delete_article' && is_admin()) {
        admin_delete_article($article['id']);
        flash('success', t('Article deleted.', 'Artigo excluído.'));
        redirect(FORUM_URL . '/');
    }
    elseif ($action === 'change_status' && is_admin()) {
        $new_status = $_POST['status'] ?? '';
        admin_update_article_status($article['id'], $new_status);
        flash('success', t('Status updated.', 'Status atualizado.'));
        redirect(FORUM_URL . '/article.php?slug=' . urlencode($slug));
    }

    // ── Ações de Moderação em Comentários ────────────────────
    elseif ($action === 'delete_comment') {
        $cid = (int) ($_POST['comment_id'] ?? 0);
        // Admin pode deletar qualquer um; autor pode deletar o próprio
        if ($cid > 0) {
            $can_delete = is_admin();
            if (!$can_delete) {
                $stmt = db()->prepare('SELECT user_id FROM comments WHERE id = ?');
                $stmt->execute([$cid]);
                $comment_owner = $stmt->fetch();
                $can_delete = ($comment_owner && (int)$comment_owner['user_id'] === current_user_id());
            }
            if ($can_delete) {
                admin_delete_comment($cid);
                flash('success', t('Comment deleted.', 'Comentário excluído.'));
            }
        }
        redirect(FORUM_URL . '/article.php?slug=' . urlencode($slug) . '#comments');
    }
    elseif ($action === 'toggle_comment' && is_admin()) {
        $cid = (int) ($_POST['comment_id'] ?? 0);
        if ($cid > 0) {
            admin_toggle_comment($cid);
            flash('success', t('Comment visibility changed.', 'Visibilidade do comentário alterada.'));
        }
        redirect(FORUM_URL . '/article.php?slug=' . urlencode($slug) . '#comments');
    }

    // ── Novo Comentário ──────────────────────────────────────
    elseif ($action === 'comment' && isset($_POST['comment_body'])) {
        if (!is_logged_in()) {
            $comment_errors[] = t('Please log in to comment.', 'Faça login para comentar.');
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
}

$comments = get_comments($article['id']);

$page_title = $article['title'];
$page_description = $article['excerpt'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<div style="margin-bottom: var(--spacing-md); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
    <a href="/forum/" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
        &larr; <?= t('Back to Forum', 'Voltar ao Fórum') ?>
    </a>

    <?php if ($article['status'] !== 'published'): ?>
        <span class="article-category-badge" style="background:rgba(245,158,11,0.2); color:#f59e0b;">
            ⚠️ <?= ucfirst($article['status']) ?>
        </span>
    <?php endif; ?>
</div>

<!-- ══ Admin Toolbar ══════════════════════════════════════════ -->
<?php if (is_admin()): ?>
    <div style="background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2); border-radius:var(--radius-sm); padding:0.75rem 1rem; margin-bottom:var(--spacing-md); display:flex; align-items:center; gap:var(--spacing-sm); flex-wrap:wrap;">
        <span style="color:#f59e0b; font-size:0.85rem; font-weight:600;">⚙️ Admin:</span>

        <!-- Change Status -->
        <form method="POST" style="display:inline;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="change_status">
            <select name="status" onchange="this.form.submit()"
                    style="background:rgba(15,23,42,0.6); border:1px solid var(--border-color); color:var(--text-secondary); border-radius:4px; padding:0.3rem 0.5rem; font-size:0.8rem; cursor:pointer;">
                <?php
                $statuses = [
                    'draft'     => t('Draft', 'Rascunho'),
                    'review'    => t('Review', 'Revisão'),
                    'published' => t('Published', 'Publicado'),
                    'rejected'  => t('Rejected', 'Rejeitado'),
                ];
                foreach ($statuses as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $article['status'] === $val ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Delete Article -->
        <form method="POST" style="display:inline;"
              onsubmit="return confirm('<?= t('PERMANENTLY delete this article and all its comments?', 'EXCLUIR permanentemente este artigo e todos os comentários?') ?>')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete_article">
            <button type="submit" class="btn btn-reject btn-small" style="padding:0.3rem 0.7rem; font-size:0.8rem;">
                🗑️ <?= t('Delete Article', 'Excluir Artigo') ?>
            </button>
        </form>

        <!-- Link to Admin Panel -->
        <a href="/forum/admin.php" style="color:var(--text-muted); font-size:0.8rem; text-decoration:none; margin-left:auto;">
            ⚙️ <?= t('Admin Panel', 'Painel Admin') ?> →
        </a>
    </div>
<?php endif; ?>

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
        <?= render_article_body($article['body']) ?>
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
            <input type="hidden" name="action" value="comment">
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
        <?php render_comments($comments, $article['id'], $slug); ?>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
// ── Helper functions for this page ──────────────────────────

function render_comments(array $comments, int $article_id, string $slug, int $depth = 0): void {
    $wrapper_class = $depth > 0 ? 'comment-replies' : '';
    if ($wrapper_class) echo "<div class=\"{$wrapper_class}\">";
    
    foreach ($comments as $c) {
        $is_own_comment = (is_logged_in() && current_user_id() === (int)$c['user_id']);
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

                <!-- ── Moderation buttons (inline) ──────────── -->
                <?php if (is_admin() || $is_own_comment): ?>
                    <span style="margin-left:auto; display:flex; gap:0.3rem; align-items:center;">
                        <?php if (is_admin()): ?>
                            <!-- Toggle Hide/Show -->
                            <form method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_comment">
                                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                <button type="submit" title="<?= t('Hide/Show','Ocultar/Mostrar') ?>"
                                        style="background:none; border:none; cursor:pointer; font-size:0.75rem; color:var(--text-muted); padding:0.1rem 0.3rem;">
                                    🙈
                                </button>
                            </form>
                        <?php endif; ?>
                        <!-- Delete (admin or own comment) -->
                        <form method="POST" style="display:inline;" 
                              onsubmit="return confirm('<?= t('Delete this comment?','Excluir este comentário?') ?>')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_comment">
                            <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                            <button type="submit" title="<?= t('Delete','Excluir') ?>"
                                    style="background:none; border:none; cursor:pointer; font-size:0.75rem; color:#ef4444; padding:0.1rem 0.3rem;">
                                🗑️
                            </button>
                        </form>
                    </span>
                <?php endif; ?>
            </div>

            <div class="comment-body"><?= nl2br(sanitize($c['body'])) ?></div>
            
            <?php if (is_logged_in() && $depth < 3): ?>
                <button class="reply-btn" onclick="toggleReply(<?= $c['id'] ?>)">
                    <?= t('Reply', 'Responder') ?>
                </button>
                <form method="POST" action="" id="reply-form-<?= $c['id'] ?>" style="display:none; margin-top:0.5rem;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="comment">
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
                <?php render_comments($c['replies'], $article_id, $slug, $depth + 1); ?>
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