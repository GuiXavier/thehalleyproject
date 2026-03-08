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

<div class="form-container wide">
    <div class="form-card">
        <h1><?= t('Write an Article', 'Escrever um Artigo') ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="form-errors">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= sanitize($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
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

            <div class="form-group">
                <label for="body"><?= t('Article Body', 'Corpo do Artigo') ?></label>
                <textarea id="body" name="body" required minlength="50"
                          placeholder="<?= t('Write your article here. Markdown is supported: **bold**, *italic*, ## headers, - lists, > quotes, [links](url), `code`',
                                              'Escreva seu artigo aqui. Markdown é suportado: **negrito**, *itálico*, ## títulos, - listas, > citações, [links](url), `código`') ?>"
                ><?= sanitize($_POST['body'] ?? '') ?></textarea>
                <div class="form-hint"><?= t('Minimum 50 characters. Use Markdown for formatting.', 'Mínimo 50 caracteres. Use Markdown para formatação.') ?></div>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= is_editor() ? t('Publish Article', 'Publicar Artigo') : t('Submit for Review', 'Enviar para Revisão') ?>
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
