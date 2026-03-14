<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$errors = [];
$categories = get_categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('Invalid request.', 'Requisição inválida.');
    } else {
        $title       = $_POST['title'] ?? '';
        $category_id = (int) ($_POST['category_id'] ?? 0);
        $body        = $_POST['body'] ?? '';
        $excerpt     = $_POST['excerpt'] ?? '';
        $language    = in_array($_POST['language'] ?? '', ['en', 'pt-BR']) ? $_POST['language'] : 'en';

        // Limpar HTML perigoso mas manter formatação
        $body = clean_html($body);

        $result = create_article(current_user_id(), $category_id, $title, $body, $language, $excerpt);

        if ($result['ok']) {
            if ($result['status'] === 'published') {
                flash('success', t('Article published!', 'Artigo publicado!'));
                redirect(FORUM_URL . '/article.php?slug=' . urlencode($result['slug']));
            } else {
                flash('info', t('Article submitted for review. It will be published after approval.',
                                'Artigo enviado para revisão. Será publicado após aprovação.'));
                redirect(FORUM_URL . '/');
            }
        } else {
            $errors = $result['errors'];
        }
    }
}

$page_title = t('Write Article', 'Escrever Artigo');
require_once __DIR__ . '/includes/header.php';
?>

<!-- Quill.js CSS -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    /* Editor styling to match Halley theme */
    .ql-toolbar.ql-snow {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
    }
    .ql-toolbar .ql-stroke { stroke: var(--text-secondary); }
    .ql-toolbar .ql-fill { fill: var(--text-secondary); }
    .ql-toolbar .ql-picker-label { color: var(--text-secondary); }
    .ql-toolbar .ql-picker-options {
        background: var(--primary-dark);
        border-color: var(--border-color);
    }
    .ql-toolbar .ql-picker-item { color: var(--text-secondary); }
    .ql-toolbar button:hover .ql-stroke,
    .ql-toolbar .ql-picker-label:hover .ql-stroke { stroke: var(--accent-cyan); }
    .ql-toolbar button:hover .ql-fill { fill: var(--accent-cyan); }
    .ql-toolbar button.ql-active .ql-stroke { stroke: var(--accent-cyan); }
    .ql-toolbar button.ql-active .ql-fill { fill: var(--accent-cyan); }
    .ql-toolbar .ql-picker-label:hover { color: var(--accent-cyan); }

    #editor-container {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--border-color);
        border-top: none;
        border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        color: var(--text-primary);
        font-family: var(--font-primary);
        font-size: 1.05rem;
        min-height: 350px;
    }
    #editor-container .ql-editor {
        min-height: 350px;
        line-height: 1.8;
    }
    #editor-container .ql-editor.ql-blank::before {
        color: var(--text-muted);
        font-style: normal;
    }
    #editor-container .ql-editor h1 { color: var(--text-primary); font-size: 1.8rem; }
    #editor-container .ql-editor h2 { color: var(--accent-cyan); font-size: 1.4rem; }
    #editor-container .ql-editor h3 { color: var(--text-primary); font-size: 1.2rem; }
    #editor-container .ql-editor blockquote {
        border-left: 4px solid var(--accent-purple);
        padding-left: 1rem;
        color: var(--text-muted);
    }
    #editor-container .ql-editor a { color: var(--accent-cyan); }
    #editor-container .ql-editor code {
        background: rgba(139, 92, 246, 0.15);
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
    }
    #editor-container .ql-editor pre {
        background: rgba(15, 23, 42, 0.8);
        border-radius: var(--radius-sm);
    }
    #editor-container .ql-editor img {
        max-width: 100%;
        border-radius: var(--radius-sm);
    }

    /* Tooltip/picker dark theme */
    .ql-snow .ql-tooltip {
        background: var(--primary-dark);
        border-color: var(--border-color);
        color: var(--text-primary);
        box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    }
    .ql-snow .ql-tooltip input[type=text] {
        background: rgba(15, 23, 42, 0.6);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    .ql-snow .ql-tooltip a { color: var(--accent-cyan); }

    .editor-word-count {
        text-align: right;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.3rem;
    }

    /* Image upload button */
    .ql-image-upload {
        position: relative;
    }
</style>

<div class="form-container wide">
    <div class="form-card">
        <h1><?= t('Write an Article', 'Escrever um Artigo') ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="form-errors">
                <ul><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="article-form" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title"><?= t('Title', 'Título') ?></label>
                <input type="text" id="title" name="title" required
                       value="<?= sanitize($_POST['title'] ?? '') ?>"
                       placeholder="<?= t('The orbital mechanics of Halley\'s Comet', 'A mecânica orbital do Cometa Halley') ?>"
                       minlength="5" maxlength="200">
            </div>

            <div style="display: flex; gap: var(--spacing-sm);">
                <div class="form-group" style="flex: 1;">
                    <label for="category_id"><?= t('Category', 'Categoria') ?></label>
                    <select id="category_id" name="category_id" required>
                        <option value=""><?= t('Select...', 'Selecionar...') ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                    <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= $cat['icon'] ?> <?= category_name($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="language"><?= t('Language', 'Idioma') ?></label>
                    <select id="language" name="language" required>
                        <option value="en" <?= (($_POST['language'] ?? current_lang()) === 'en') ? 'selected' : '' ?>>
                            🇬🇧 English
                        </option>
                        <option value="pt-BR" <?= (($_POST['language'] ?? current_lang()) === 'pt-BR') ? 'selected' : '' ?>>
                            🇧🇷 Português
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="excerpt"><?= t('Excerpt (optional)', 'Resumo (opcional)') ?></label>
                <input type="text" id="excerpt" name="excerpt"
                       value="<?= sanitize($_POST['excerpt'] ?? '') ?>"
                       placeholder="<?= t('Brief summary shown in the article list', 'Breve resumo exibido na lista de artigos') ?>"
                       maxlength="500">
                <div class="form-hint"><?= t('If empty, auto-generated from article body.', 'Se vazio, gerado automaticamente do corpo do artigo.') ?></div>
            </div>

            <!-- Rich Text Editor -->
            <div class="form-group">
                <label><?= t('Article Body', 'Corpo do Artigo') ?></label>
                <div id="editor-container"></div>
                <div class="editor-word-count" id="word-count">0 <?= t('words', 'palavras') ?></div>
                <!-- Hidden field that receives the HTML -->
                <input type="hidden" name="body" id="body-hidden">
            </div>

            <!-- Image Upload -->
            <div class="form-group">
                <label><?= t('Upload Image (optional)', 'Enviar Imagem (opcional)') ?></label>
                <div style="display:flex; align-items:center; gap:var(--spacing-sm);">
                    <input type="file" id="image-upload" accept="image/jpeg,image/png,image/webp,image/gif"
                           style="font-size:0.9rem; color:var(--text-secondary);">
                    <span id="upload-status" style="font-size:0.8rem; color:var(--text-muted);"></span>
                </div>
                <div class="form-hint"><?= t('Max 2MB. Click the image icon in the toolbar or upload here to insert.', 'Máx 2MB. Clique no ícone de imagem na toolbar ou envie aqui para inserir.') ?></div>
            </div>

            <button type="submit" class="btn btn-primary" id="submit-btn">
                <?= is_editor() ? t('Publish Article', 'Publicar Artigo') : t('Submit for Review', 'Enviar para Revisão') ?>
            </button>
        </form>
    </div>
</div>

<!-- Quill.js -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
    // Initialize Quill editor
    const quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: '<?= t('Write your article here...', 'Escreva seu artigo aqui...') ?>',
        modules: {
            toolbar: {
                container: [
                    [{ 'header': [1, 2, 3, false] }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['blockquote', 'code-block'],
                    ['link', 'image'],
                    ['clean']
                ],
                handlers: {
                    'image': imageHandler
                }
            }
        }
    });

    // Word counter
    quill.on('text-change', function() {
        const text = quill.getText().trim();
        const words = text ? text.split(/\s+/).length : 0;
        document.getElementById('word-count').textContent = words + ' <?= t('words', 'palavras') ?>';
    });

    // Load existing content if form was resubmitted with errors
    <?php if (!empty($_POST['body'])): ?>
        quill.root.innerHTML = <?= json_encode($_POST['body']) ?>;
    <?php endif; ?>

    // Image handler — upload via AJAX and insert
    function imageHandler() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/jpeg,image/png,image/webp,image/gif');
        input.click();
        input.onchange = () => {
            if (input.files && input.files[0]) {
                uploadImage(input.files[0]);
            }
        };
    }

    // File input upload
    document.getElementById('image-upload').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            uploadImage(this.files[0]);
        }
    });

    function uploadImage(file) {
        const status = document.getElementById('upload-status');
        
        if (file.size > 2 * 1024 * 1024) {
            status.textContent = '<?= t('File too large (max 2MB)', 'Arquivo muito grande (máx 2MB)') ?>';
            status.style.color = '#ef4444';
            return;
        }

        status.textContent = '<?= t('Uploading...', 'Enviando...') ?>';
        status.style.color = 'var(--accent-cyan)';

        const formData = new FormData();
        formData.append('image', file);
        formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= csrf_token() ?>');

        fetch('/forum/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                const range = quill.getSelection(true);
                quill.insertEmbed(range.index, 'image', data.url);
                quill.setSelection(range.index + 1);
                status.textContent = '✓ <?= t('Image inserted', 'Imagem inserida') ?>';
                status.style.color = '#22c55e';
            } else {
                status.textContent = '✕ ' + data.error;
                status.style.color = '#ef4444';
            }
        })
        .catch(() => {
            status.textContent = '<?= t('Upload failed', 'Falha no envio') ?>';
            status.style.color = '#ef4444';
        });
    }

    // On form submit, copy editor HTML to hidden field
    document.getElementById('article-form').addEventListener('submit', function(e) {
        const html = quill.root.innerHTML;
        
        // Check minimum content
        const text = quill.getText().trim();
        if (text.length < 50) {
            e.preventDefault();
            alert('<?= t('Article must be at least 50 characters.', 'Artigo deve ter pelo menos 50 caracteres.') ?>');
            return;
        }
        
        document.getElementById('body-hidden').value = html;
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>