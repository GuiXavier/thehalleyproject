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
    elseif ($action === 'delete_comment') {
        $cid = (int) ($_POST['comment_id'] ?? 0);
        if ($cid > 0) {
            $can_delete = is_admin();
            if (!$can_delete) {
                $stmt = db()->prepare('SELECT user_id FROM comments WHERE id = ?');
                $stmt->execute([$cid]);
                $co = $stmt->fetch();
                $can_delete = ($co && (int)$co['user_id'] === current_user_id());
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
            flash('success', t('Comment visibility changed.', 'Visibilidade alterada.'));
        }
        redirect(FORUM_URL . '/article.php?slug=' . urlencode($slug) . '#comments');
    }
    elseif ($action === 'comment' && isset($_POST['comment_body'])) {
        if (!is_logged_in()) {
            $comment_errors[] = t('Please log in to comment.', 'Faça login para comentar.');
        } else {
            $body = clean_html($_POST['comment_body']);
            $parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
            $result = add_comment($article['id'], current_user_id(), $body, $parent_id);
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

<!-- Quill CSS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    .comment-editor-wrap .ql-toolbar.ql-snow {
        background: rgba(15,23,42,0.8);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
        padding: 4px;
    }
    .comment-editor-wrap .ql-toolbar .ql-stroke { stroke: var(--text-secondary); }
    .comment-editor-wrap .ql-toolbar .ql-fill { fill: var(--text-secondary); }
    .comment-editor-wrap .ql-toolbar .ql-picker-label { color: var(--text-secondary); }
    .comment-editor-wrap .ql-toolbar .ql-picker-options { background: var(--primary-dark); border-color: var(--border-color); }
    .comment-editor-wrap .ql-toolbar button:hover .ql-stroke { stroke: var(--accent-cyan); }
    .comment-editor-wrap .ql-toolbar button:hover .ql-fill { fill: var(--accent-cyan); }
    .comment-editor-wrap .ql-toolbar button.ql-active .ql-stroke { stroke: var(--accent-cyan); }
    .comment-editor-wrap .ql-toolbar button.ql-active .ql-fill { fill: var(--accent-cyan); }
    .comment-editor {
        background: rgba(15,23,42,0.6);
        border: 1px solid var(--border-color);
        border-top: none;
        border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        color: var(--text-primary);
        font-family: var(--font-primary);
    }
    .comment-editor .ql-editor {
        min-height: 100px;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    .comment-editor .ql-editor.ql-blank::before {
        color: var(--text-muted);
        font-style: normal;
    }
    .comment-editor .ql-editor a { color: var(--accent-cyan); }
    .comment-editor .ql-editor blockquote {
        border-left: 3px solid var(--accent-purple);
        padding-left: 0.8rem;
        color: var(--text-muted);
    }
    .comment-editor .ql-editor img { max-width: 100%; border-radius: 6px; }
    .ql-snow .ql-tooltip {
        background: var(--primary-dark);
        border-color: var(--border-color);
        color: var(--text-primary);
        box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    }
    .ql-snow .ql-tooltip input[type=text] {
        background: rgba(15,23,42,0.6);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    .ql-snow .ql-tooltip a { color: var(--accent-cyan); }
    .comment-body img { max-width: 100%; border-radius: 6px; margin: 0.5rem 0; }
    .comment-body a { color: var(--accent-cyan); }
    .comment-body blockquote { border-left: 3px solid var(--accent-purple); padding-left: 0.8rem; color: var(--text-muted); }
    .comment-upload-row { display:flex; align-items:center; gap:0.5rem; margin-top:0.4rem; }
    .comment-upload-row input[type=file] { font-size:0.8rem; color:var(--text-muted); max-width:200px; }
    .comment-upload-status { font-size:0.75rem; color:var(--text-muted); }
</style>

<!-- Breadcrumb + status -->
<div style="margin-bottom:var(--spacing-md); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
    <a href="/forum/" style="color:var(--text-muted); text-decoration:none; font-size:0.9rem;">&larr; <?= t('Back to Forum','Voltar ao Fórum') ?></a>
    <?php if ($article['status'] !== 'published'): ?>
        <span class="article-category-badge" style="background:rgba(245,158,11,0.2); color:#f59e0b;">⚠️ <?= ucfirst($article['status']) ?></span>
    <?php endif; ?>
</div>

<!-- Admin Toolbar -->
<?php if (is_admin()): ?>
    <div style="background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2); border-radius:var(--radius-sm); padding:0.75rem 1rem; margin-bottom:var(--spacing-md); display:flex; align-items:center; gap:var(--spacing-sm); flex-wrap:wrap;">
        <span style="color:#f59e0b; font-size:0.85rem; font-weight:600;">⚙️ Admin:</span>
        <form method="POST" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="action" value="change_status">
            <select name="status" onchange="this.form.submit()" style="background:rgba(15,23,42,0.6); border:1px solid var(--border-color); color:var(--text-secondary); border-radius:4px; padding:0.3rem 0.5rem; font-size:0.8rem; cursor:pointer;">
                <?php foreach (['draft'=>t('Draft','Rascunho'),'review'=>t('Review','Revisão'),'published'=>t('Published','Publicado'),'rejected'=>t('Rejected','Rejeitado')] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $article['status']===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select></form>
        <form method="POST" style="display:inline;" onsubmit="return confirm('<?= t('PERMANENTLY delete?','EXCLUIR permanentemente?') ?>')"><?= csrf_field() ?><input type="hidden" name="action" value="delete_article">
            <button type="submit" class="btn btn-reject btn-small" style="padding:0.3rem 0.7rem; font-size:0.8rem;">🗑️ <?= t('Delete','Excluir') ?></button></form>
        <a href="/forum/admin.php" style="color:var(--text-muted); font-size:0.8rem; text-decoration:none; margin-left:auto;">⚙️ <?= t('Admin Panel','Painel Admin') ?> →</a>
    </div>
<?php endif; ?>

<!-- Article -->
<article class="article-full">
    <div class="article-card-header" style="margin-bottom:var(--spacing-sm);">
        <span class="article-category-badge"><?= $article['category_icon'] ?> <?= current_lang()==='pt-BR' ? $article['category_pt'] : $article['category_en'] ?></span>
        <span>&bull;</span>
        <span><?= $article['language']==='pt-BR' ? '🇧🇷 Português' : '🇬🇧 English' ?></span>
    </div>
    <h1><?= sanitize($article['title']) ?></h1>
    <div class="article-meta" style="margin-bottom:var(--spacing-lg);">
        <span>✍️ <?= sanitize($article['author']) ?></span>
        <span>📅 <?= date('M j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?></span>
        <span>👁️ <?= $article['views'] ?> <?= t('views','views') ?></span>
        <span>⏱️ <?= reading_time($article['body']) ?></span>
    </div>
    <div class="article-body"><?= render_article_body($article['body']) ?></div>
    <div class="author-box">
        <div class="author-avatar"><?= strtoupper(mb_substr($article['author'],0,1)) ?></div>
        <div class="author-info">
            <strong><?= sanitize($article['author']) ?></strong>
            <p><?= $article['author_bio'] ? sanitize($article['author_bio']) : t('Member of The Halley Project','Membro do The Halley Project') ?></p>
        </div>
    </div>
</article>

<!-- Comments Section -->
<section class="comments-section" id="comments">
    <h3>💬 <?= t('Comments','Comentários') ?> (<?= count_flat_comments($comments) ?>)</h3>

    <?php if (!empty($comment_errors)): ?>
        <div class="form-errors"><ul><?php foreach ($comment_errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <!-- Main Comment Form -->
    <?php if (is_logged_in()): ?>
        <form method="POST" action="" id="main-comment-form" style="margin-bottom:var(--spacing-lg);">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="comment">
            <input type="hidden" name="comment_body" id="main-comment-hidden">
            <div class="comment-editor-wrap">
                <div id="main-comment-editor" class="comment-editor"></div>
            </div>
            <div class="comment-upload-row">
                <input type="file" id="main-upload" accept="image/jpeg,image/png,image/webp,image/gif">
                <span id="main-upload-status" class="comment-upload-status"></span>
                <button type="submit" class="btn btn-secondary btn-small" style="margin-left:auto;">
                    <?= t('Post Comment','Publicar Comentário') ?>
                </button>
            </div>
        </form>
    <?php else: ?>
        <p style="color:var(--text-muted); margin-bottom:var(--spacing-md);">
            <a href="/forum/login.php" style="color:var(--accent-cyan);"><?= t('Sign in to comment','Entre para comentar') ?></a>
        </p>
    <?php endif; ?>

    <!-- Comments List -->
    <?php if (empty($comments)): ?>
        <div class="empty-state" style="padding:var(--spacing-md);"><p><?= t('No comments yet. Start the discussion!','Nenhum comentário ainda. Inicie a discussão!') ?></p></div>
    <?php else: ?>
        <?php render_comments($comments, $article['id'], $slug); ?>
    <?php endif; ?>
</section>

<!-- Quill JS -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
const CSRF_NAME  = '<?= CSRF_TOKEN_NAME ?>';
const UPLOAD_URL = '/forum/upload.php';
const TOOLBAR_COMMENT = [
    ['bold', 'italic', 'underline', 'strike'],
    [{ 'color': [] }, { 'background': [] }],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    [{ 'align': [] }],
    ['blockquote', 'link', 'image'],
    ['clean']
];

// Track all Quill instances
const editors = {};

// Initialize main comment editor
<?php if (is_logged_in()): ?>
(function() {
    const q = new Quill('#main-comment-editor', {
        theme: 'snow',
        placeholder: '<?= t('Write a comment...','Escreva um comentário...') ?>',
        modules: { toolbar: { container: TOOLBAR_COMMENT, handlers: { image: function() { imagePrompt(q); } } } }
    });
    editors['main'] = q;

    // Upload button
    document.getElementById('main-upload').addEventListener('change', function() {
        if (this.files[0]) uploadImage(this.files[0], q, document.getElementById('main-upload-status'));
    });

    // Submit
    document.getElementById('main-comment-form').addEventListener('submit', function(e) {
        const text = q.getText().trim();
        const html = q.root.innerHTML;
        if (text.length < 3 || html === '<p><br></p>') {
            e.preventDefault();
            alert('<?= t('Comment must be at least 3 characters.','Comentário deve ter pelo menos 3 caracteres.') ?>');
            return;
        }
        document.getElementById('main-comment-hidden').value = html;
    });
})();
<?php endif; ?>

// Toggle reply form and init Quill on first open
function toggleReply(id) {
    const wrapper = document.getElementById('reply-wrap-' + id);
    const isHidden = wrapper.style.display === 'none';
    wrapper.style.display = isHidden ? 'block' : 'none';

    if (isHidden && !editors['reply-' + id]) {
        const editorEl = document.getElementById('reply-editor-' + id);
        const q = new Quill(editorEl, {
            theme: 'snow',
            placeholder: '<?= t('Write a reply...','Escreva uma resposta...') ?>',
            modules: { toolbar: { container: TOOLBAR_COMMENT, handlers: { image: function() { imagePrompt(q); } } } }
        });
        editors['reply-' + id] = q;

        // Upload for reply
        const uploadInput = document.getElementById('reply-upload-' + id);
        const uploadStatus = document.getElementById('reply-status-' + id);
        if (uploadInput) {
            uploadInput.addEventListener('change', function() {
                if (this.files[0]) uploadImage(this.files[0], q, uploadStatus);
            });
        }

        // Submit for reply
        document.getElementById('reply-form-' + id).addEventListener('submit', function(e) {
            const text = q.getText().trim();
            const html = q.root.innerHTML;
            if (text.length < 3 || html === '<p><br></p>') {
                e.preventDefault();
                alert('<?= t('Comment must be at least 3 characters.','Comentário deve ter pelo menos 3 caracteres.') ?>');
                return;
            }
            document.getElementById('reply-hidden-' + id).value = html;
        });
    }

    if (isHidden && editors['reply-' + id]) {
        editors['reply-' + id].focus();
    }
}

// Image prompt (toolbar button)
function imagePrompt(quillInstance) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/jpeg,image/png,image/webp,image/gif';
    input.click();
    input.onchange = () => {
        if (input.files[0]) uploadImage(input.files[0], quillInstance, null);
    };
}

// Upload image via AJAX
function uploadImage(file, quillInstance, statusEl) {
    if (file.size > 2 * 1024 * 1024) {
        if (statusEl) { statusEl.textContent = '<?= t('Max 2MB','Máx 2MB') ?>'; statusEl.style.color = '#ef4444'; }
        return;
    }
    if (statusEl) { statusEl.textContent = '<?= t('Uploading...','Enviando...') ?>'; statusEl.style.color = 'var(--accent-cyan)'; }

    const fd = new FormData();
    fd.append('image', file);
    fd.append(CSRF_NAME, CSRF_TOKEN);

    fetch(UPLOAD_URL, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                const range = quillInstance.getSelection(true);
                quillInstance.insertEmbed(range.index, 'image', data.url);
                quillInstance.setSelection(range.index + 1);
                if (statusEl) { statusEl.textContent = '✓'; statusEl.style.color = '#22c55e'; }
            } else {
                if (statusEl) { statusEl.textContent = '✕ ' + data.error; statusEl.style.color = '#ef4444'; }
            }
        })
        .catch(() => {
            if (statusEl) { statusEl.textContent = '<?= t('Failed','Falha') ?>'; statusEl.style.color = '#ef4444'; }
        });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
// ── Helper functions ─────────────────────────────────────────

function render_comments(array $comments, int $article_id, string $slug, int $depth = 0): void {
    $wrapper_class = $depth > 0 ? 'comment-replies' : '';
    if ($wrapper_class) echo "<div class=\"{$wrapper_class}\">";
    
    foreach ($comments as $c) {
        $is_own = (is_logged_in() && current_user_id() === (int)$c['user_id']);
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

                <?php if (is_admin() || $is_own): ?>
                    <span style="margin-left:auto; display:flex; gap:0.3rem; align-items:center;">
                        <?php if (is_admin()): ?>
                            <form method="POST" style="display:inline;"><?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_comment">
                                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                <button type="submit" title="<?= t('Hide/Show','Ocultar/Mostrar') ?>" style="background:none; border:none; cursor:pointer; font-size:0.75rem; color:var(--text-muted); padding:0.1rem 0.3rem;">🙈</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('<?= t('Delete?','Excluir?') ?>')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_comment">
                            <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                            <button type="submit" title="<?= t('Delete','Excluir') ?>" style="background:none; border:none; cursor:pointer; font-size:0.75rem; color:#ef4444; padding:0.1rem 0.3rem;">🗑️</button>
                        </form>
                    </span>
                <?php endif; ?>
            </div>

            <div class="comment-body"><?= render_comment_body($c['body']) ?></div>
            
            <?php if (is_logged_in() && $depth < 3): ?>
                <button class="reply-btn" onclick="toggleReply(<?= $c['id'] ?>)">
                    <?= t('Reply','Responder') ?>
                </button>
                <div id="reply-wrap-<?= $c['id'] ?>" style="display:none; margin-top:0.5rem;">
                    <form method="POST" action="" id="reply-form-<?= $c['id'] ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="comment">
                        <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">
                        <input type="hidden" name="comment_body" id="reply-hidden-<?= $c['id'] ?>">
                        <div class="comment-editor-wrap">
                            <div id="reply-editor-<?= $c['id'] ?>" class="comment-editor"></div>
                        </div>
                        <div class="comment-upload-row">
                            <input type="file" id="reply-upload-<?= $c['id'] ?>" accept="image/jpeg,image/png,image/webp,image/gif">
                            <span id="reply-status-<?= $c['id'] ?>" class="comment-upload-status"></span>
                            <button type="submit" class="btn btn-secondary btn-small" style="margin-left:auto;">
                                <?= t('Reply','Responder') ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (!empty($c['replies'])): ?>
                <?php render_comments($c['replies'], $article_id, $slug, $depth + 1); ?>
            <?php endif; ?>
        </div>
        <?php
    }
    if ($wrapper_class) echo "</div>";
}

function render_comment_body(string $body): string {
    // Se tem HTML (editor rico), renderiza limpo
    if (preg_match('/<(p|strong|em|ul|ol|blockquote|a|img)\b/i', $body)) {
        return clean_html($body);
    }
    // Texto puro legado
    return nl2br(sanitize($body));
}

function count_flat_comments(array $comments): int {
    $count = count($comments);
    foreach ($comments as $c) {
        if (!empty($c['replies'])) $count += count_flat_comments($c['replies']);
    }
    return $count;
}
?>