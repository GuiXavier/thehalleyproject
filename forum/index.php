<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

init_session();

// Filtro por categoria
$cat_slug   = $_GET['category'] ?? null;
$cat_id     = null;
$active_cat = null;

if ($cat_slug) {
    $active_cat = get_category_by_slug($cat_slug);
    $cat_id = $active_cat ? $active_cat['id'] : null;
}

// Paginação
$page = max(1, (int) ($_GET['page'] ?? 1));
$data = get_articles($page, 10, $cat_id);

$categories = get_categories();
$stats = forum_stats();

$page_title = t('Forum', 'Fórum');
$page_description = t(
    'Community articles about Halley\'s Comet and astronomy.',
    'Artigos da comunidade sobre o Cometa Halley e astronomia.'
);
require_once __DIR__ . '/includes/header.php';
?>

<!-- Forum Header -->
<div class="forum-header">
    <h1><?= t('Community Articles', 'Artigos da Comunidade') ?></h1>
    <p><?= t('Research, observations, and discussions about Halley\'s Comet and astronomy',
             'Pesquisas, observações e discussões sobre o Cometa Halley e astronomia') ?></p>
</div>

<!-- Stats -->
<div class="forum-stats">
    <div class="stat-item">
        <span class="stat-value"><?= $stats['total_articles'] ?></span>
        <span class="stat-label"><?= t('Articles', 'Artigos') ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $stats['total_users'] ?></span>
        <span class="stat-label"><?= t('Members', 'Membros') ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $stats['total_comments'] ?></span>
        <span class="stat-label"><?= t('Comments', 'Comentários') ?></span>
    </div>
</div>

<!-- Categories -->
<div class="categories-bar">
    <a href="/forum/" class="category-chip <?= !$cat_slug ? 'active' : '' ?>">
        <?= t('All', 'Todos') ?>
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="/forum/?category=<?= $cat['slug'] ?>" 
           class="category-chip <?= $cat_slug === $cat['slug'] ? 'active' : '' ?>">
            <?= $cat['icon'] ?> <?= category_name($cat) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Articles List -->
<div class="articles-list">
    <?php if (empty($data['articles'])): ?>
        <div class="empty-state">
            <div class="icon">📝</div>
            <p><?= t('No articles yet. Be the first to write one!', 'Nenhum artigo ainda. Seja o primeiro a escrever!') ?></p>
            <?php if (is_logged_in()): ?>
                <a href="/forum/submit.php" class="btn btn-primary" style="margin-top:1rem; display:inline-block; width:auto;">
                    <?= t('Write Article', 'Escrever Artigo') ?>
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($data['articles'] as $article): ?>
            <a href="/forum/article.php?slug=<?= urlencode($article['slug']) ?>" class="article-card">
                <div class="article-card-header">
                    <span class="article-category-badge">
                        <?= $article['category_icon'] ?> 
                        <?= current_lang() === 'pt-BR' ? $article['category_pt'] : $article['category_en'] ?>
                    </span>
                    <span>&bull;</span>
                    <span><?= $article['language'] === 'pt-BR' ? '🇧🇷' : '🇬🇧' ?></span>
                    <span>&bull;</span>
                    <span><?= time_ago($article['published_at'] ?? $article['created_at']) ?></span>
                </div>

                <h2><?= sanitize($article['title']) ?></h2>

                <?php if ($article['excerpt']): ?>
                    <div class="excerpt"><?= sanitize($article['excerpt']) ?></div>
                <?php endif; ?>

                <div class="article-meta">
                    <span>✍️ <?= sanitize($article['author']) ?></span>
                    <span>👁️ <?= $article['views'] ?> <?= t('views', 'visualizações') ?></span>
                    <span>💬 <?= $article['comment_count'] ?> <?= t('comments', 'comentários') ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($data['total_pages'] > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_filter(['category' => $cat_slug, 'page' => $page - 1])) ?>">
                &laquo; <?= t('Prev', 'Ant') ?>
            </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $data['total_pages']; $i++): ?>
            <?php if ($i === $page): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?<?= http_build_query(array_filter(['category' => $cat_slug, 'page' => $i])) ?>">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $data['total_pages']): ?>
            <a href="?<?= http_build_query(array_filter(['category' => $cat_slug, 'page' => $page + 1])) ?>">
                <?= t('Next', 'Próx') ?> &raquo;
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
