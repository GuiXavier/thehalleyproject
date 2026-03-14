<?php
/**
 * The Halley Project — Forum Functions
 * Artigos, comentários, categorias, paginação
 */

require_once __DIR__ . '/config.php';

// ══════════════════════════════════════════════════════════════
// ARTIGOS
// ══════════════════════════════════════════════════════════════

function get_articles(int $page = 1, int $per_page = 10, ?int $category_id = null, string $status = 'published'): array {
    $offset = ($page - 1) * $per_page;
    $params = [];
    $where  = ['a.status = ?', 'a.deleted_at IS NULL'];
    $params[] = $status;

    if ($category_id) {
        $where[] = 'a.category_id = ?';
        $params[] = $category_id;
    }

    $where_sql = implode(' AND ', $where);

    // Total para paginação
    $stmt = db()->prepare("SELECT COUNT(*) FROM articles a WHERE {$where_sql}");
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    // Artigos com autor e categoria
    $params[] = $per_page;
    $params[] = $offset;

    $stmt = db()->prepare("
        SELECT a.id, a.title, a.slug, a.excerpt, a.language, a.views, 
               a.published_at, a.created_at,
               u.username AS author, u.id AS author_id,
               c.slug AS category_slug,
               c.name_en AS category_en, c.name_pt AS category_pt, c.icon AS category_icon,
               (SELECT COUNT(*) FROM comments cm WHERE cm.article_id = a.id AND cm.is_hidden = 0 AND cm.deleted_at IS NULL) AS comment_count
        FROM articles a
        JOIN users u ON a.user_id = u.id
        JOIN categories c ON a.category_id = c.id
        WHERE {$where_sql}
        ORDER BY a.published_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

    return [
        'articles'   => $articles,
        'total'      => $total,
        'page'       => $page,
        'per_page'   => $per_page,
        'total_pages' => max(1, ceil($total / $per_page))
    ];
}

function get_article_by_slug(string $slug): ?array {
    $stmt = db()->prepare("
        SELECT a.*, 
               u.username AS author, u.bio AS author_bio, u.id AS author_id,
               c.name_en AS category_en, c.name_pt AS category_pt, 
               c.slug AS category_slug, c.icon AS category_icon
        FROM articles a
        JOIN users u ON a.user_id = u.id
        JOIN categories c ON a.category_id = c.id
        WHERE a.slug = ? AND a.deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();

    if ($article && $article['status'] === 'published') {
        // Incrementar views (fire-and-forget)
        db()->prepare('UPDATE articles SET views = views + 1 WHERE id = ?')
            ->execute([$article['id']]);
    }

    return $article ?: null;
}

function create_article(int $user_id, int $category_id, string $title, string $body, string $language, string $excerpt = ''): array {
    $errors = [];

    $title = trim($title);
    $body  = trim($body);
    $excerpt = trim($excerpt);

    if (strlen($title) < 5 || strlen($title) > 200) {
        $errors[] = t('Title must be 5-200 characters.', 'Título deve ter 5-200 caracteres.');
    }
    if (strlen($body) < 50) {
        $errors[] = t('Article must be at least 50 characters.', 'Artigo deve ter pelo menos 50 caracteres.');
    }

    if (!empty($errors)) return ['ok' => false, 'errors' => $errors];

    $slug = create_slug($title);

    // Auto-excerpt se vazio
    if (empty($excerpt)) {
        $excerpt = mb_substr(strip_tags($body), 0, 300) . '...';
    }

    $stmt = db()->prepare(
        'INSERT INTO articles (user_id, category_id, title, slug, excerpt, body, language, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    // Editores e admins publicam direto; usuários vão para revisão
    $status = (is_editor() || is_admin()) ? 'published' : 'review';

    $stmt->execute([
        $user_id, $category_id, $title, $slug, $excerpt, $body, $language, $status
    ]);

    if ($status === 'published') {
        $id = db()->lastInsertId();
        db()->prepare('UPDATE articles SET published_at = NOW() WHERE id = ?')->execute([$id]);
    }

    return [
        'ok'     => true,
        'slug'   => $slug,
        'status' => $status
    ];
}

function create_slug(string $text): string {
    $slug = mb_strtolower($text, 'UTF-8');
    // Transliterar caracteres acentuados
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');

    // Garantir unicidade
    $base = $slug;
    $i = 1;
    while (true) {
        $stmt = db()->prepare('SELECT id FROM articles WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) break;
        $slug = $base . '-' . $i++;
    }

    return $slug;
}

// ══════════════════════════════════════════════════════════════
// COMENTÁRIOS
// ══════════════════════════════════════════════════════════════

function get_comments(int $article_id): array {
    $stmt = db()->prepare("
        SELECT c.id, c.body, c.parent_id, c.created_at,
               u.username, u.id AS user_id, u.role
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.article_id = ? AND c.is_hidden = 0 AND c.deleted_at IS NULL
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$article_id]);
    $flat = $stmt->fetchAll();

    // Organizar em árvore
    return build_comment_tree($flat);
}

function build_comment_tree(array $flat, ?int $parent_id = null): array {
    $tree = [];
    foreach ($flat as $comment) {
        if ($comment['parent_id'] == $parent_id) {
            $comment['replies'] = build_comment_tree($flat, $comment['id']);
            $tree[] = $comment;
        }
    }
    return $tree;
}

function add_comment(int $article_id, int $user_id, string $body, ?int $parent_id = null): array {
    $body = trim($body);

    if (strlen($body) < 3 || strlen($body) > 5000) {
        return ['ok' => false, 'errors' => [
            t('Comment must be 3-5000 characters.', 'Comentário deve ter 3-5000 caracteres.')
        ]];
    }

    $stmt = db()->prepare(
        'INSERT INTO comments (article_id, user_id, body, parent_id) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$article_id, $user_id, $body, $parent_id]);

    return ['ok' => true, 'id' => db()->lastInsertId()];
}

// ══════════════════════════════════════════════════════════════
// CATEGORIAS
// ══════════════════════════════════════════════════════════════

function get_categories(): array {
    static $cats = null;
    if ($cats === null) {
        $cats = db()->query('SELECT * FROM categories ORDER BY sort_order')->fetchAll();
    }
    return $cats;
}

function get_category_by_slug(string $slug): ?array {
    $stmt = db()->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function category_name(array $cat): string {
    return current_lang() === 'pt-BR' ? $cat['name_pt'] : $cat['name_en'];
}

// ══════════════════════════════════════════════════════════════
// ADMIN / MODERAÇÃO
// ══════════════════════════════════════════════════════════════

function get_pending_articles(): array {
    $stmt = db()->prepare("
        SELECT a.id, a.title, a.slug, a.created_at, a.language,
               u.username AS author
        FROM articles a
        JOIN users u ON a.user_id = u.id
        WHERE a.status = 'review' AND a.deleted_at IS NULL
        ORDER BY a.created_at ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function publish_article(int $id): void {
    $stmt = db()->prepare(
        "UPDATE articles SET status = 'published', published_at = NOW() WHERE id = ?"
    );
    $stmt->execute([$id]);
}

function reject_article(int $id): void {
    $stmt = db()->prepare("UPDATE articles SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
}

// ══════════════════════════════════════════════════════════════
// STATS (para dashboard)
// ══════════════════════════════════════════════════════════════

function forum_stats(): array {
    $stats = db()->query("
        SELECT 
            (SELECT COUNT(*) FROM users WHERE is_active = 1) AS total_users,
            (SELECT COUNT(*) FROM articles WHERE status = 'published' AND deleted_at IS NULL) AS total_articles,
            (SELECT COUNT(*) FROM comments WHERE is_hidden = 0 AND deleted_at IS NULL) AS total_comments,
            (SELECT COUNT(*) FROM articles WHERE status = 'review' AND deleted_at IS NULL) AS pending_articles
    ")->fetch();
    return $stats;
}

// ══════════════════════════════════════════════════════════════
// FORMATAÇÃO
// ══════════════════════════════════════════════════════════════

function time_ago(string $datetime): string {
    $now  = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);

    if ($diff->y > 0) return $diff->y . t(' year(s) ago', ' ano(s) atrás');
    if ($diff->m > 0) return $diff->m . t(' month(s) ago', ' mês(es) atrás');
    if ($diff->d > 0) return $diff->d . t(' day(s) ago', ' dia(s) atrás');
    if ($diff->h > 0) return $diff->h . t(' hour(s) ago', ' hora(s) atrás');
    if ($diff->i > 0) return $diff->i . t(' minute(s) ago', ' minuto(s) atrás');
    return t('Just now', 'Agora mesmo');
}

function reading_time(string $text): string {
    $words = str_word_count(strip_tags($text));
    $minutes = max(1, ceil($words / 200));
    return $minutes . ' min ' . t('read', 'de leitura');
}

// Markdown básico → HTML (sem dependências externas)
function parse_markdown(string $text): string {
    $text = sanitize($text);

    // Headers
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m',  '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.+)$/m',   '<h1>$1</h1>', $text);

    // Bold & Italic
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.+?)\*/',     '<em>$1</em>', $text);

    // Code blocks
    $text = preg_replace('/```(.+?)```/s', '<pre><code>$1</code></pre>', $text);
    $text = preg_replace('/`(.+?)`/',      '<code>$1</code>', $text);

    // Links
    $text = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" rel="noopener">$1</a>', $text);

    // Line breaks / paragraphs
    $text = preg_replace('/\n{2,}/', '</p><p>', $text);
    $text = nl2br($text);
    $text = '<p>' . $text . '</p>';
    $text = str_replace('<p></p>', '', $text);

    // Lists (basic)
    $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.+<\/li>)/s', '<ul>$1</ul>', $text);

    // Blockquotes
    $text = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $text);

    return $text;
}

// Sanitizar HTML do editor rico (permitir formatação segura, bloquear XSS)
function clean_html(string $html): string {
    // Tags permitidas
    $allowed = '<h1><h2><h3><p><br><strong><b><em><i><u><s><strike>'
             . '<ul><ol><li><blockquote><pre><code>'
             . '<a><img><span><div><sub><sup><hr>';

    $html = strip_tags($html, $allowed);

    // Limpar atributos perigosos (onclick, onerror, javascript:, etc.)
    $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
    $html = preg_replace('/javascript\s*:/i', '', $html);

    // Permitir style apenas com propriedades seguras (cor, alinhamento, tamanho)
    $html = preg_replace_callback('/\s*style\s*=\s*"([^"]*)"/i', function($m) {
        $safe_props = [];
        $props = explode(';', $m[1]);
        foreach ($props as $prop) {
            $prop = trim($prop);
            if (empty($prop)) continue;
            if (preg_match('/^(color|background-color|text-align|font-size)\s*:/i', $prop)) {
                // Bloquear expression() e url() dentro de valores
                if (!preg_match('/expression|url\s*\(/i', $prop)) {
                    $safe_props[] = $prop;
                }
            }
        }
        return empty($safe_props) ? '' : ' style="' . implode('; ', $safe_props) . '"';
    }, $html);

    // Garantir que links tenham rel="noopener"
    $html = preg_replace('/<a\s+(?![^>]*rel=)/i', '<a rel="noopener" ', $html);

    // Garantir que imagens tenham loading="lazy"
    $html = preg_replace('/<img\s+(?![^>]*loading=)/i', '<img loading="lazy" ', $html);

    return $html;
}

// Renderizar corpo do artigo (detecta se é HTML rico ou Markdown legado)
function render_article_body(string $body): string {
    // Se contém tags HTML do editor rico, é HTML — só limpar
    if (preg_match('/<(p|h[1-3]|ul|ol|blockquote|strong|em)\b/i', $body)) {
        return clean_html($body);
    }
    // Senão, é Markdown legado — converter
    return parse_markdown($body);
}