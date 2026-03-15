<?php
/**
 * The Halley Project — Forum Configuration
 * Conexão ao banco, constantes e configurações globais
 */

// ── Modo de erro (desativar em produção) ─────────────────────
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ── Constantes do Site ───────────────────────────────────────
define('SITE_NAME',     'The Halley Project');
define('SITE_URL',      'https://thehalleyproject.org');
define('FORUM_URL',     SITE_URL . '/forum');
define('FORUM_PATH',    dirname(__DIR__));       // /var/www/html/forum
define('UPLOADS_PATH',  FORUM_PATH . '/uploads');
define('MAX_UPLOAD_MB',  2);

// ── Segurança ────────────────────────────────────────────────
define('CSRF_TOKEN_NAME', 'halley_csrf');
define('SESSION_LIFETIME', 86400 * 7);           // 7 dias
define('BCRYPT_COST', 10);                        // Bom para Atom N2600
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// ── Banco de Dados ───────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'halley_forum');
define('DB_USER',    'root');                     // Trocar em produção
define('DB_PASS',    'Residentevil4');            // Trocar em produção
define('DB_CHARSET', 'utf8mb4');

// ── Conexão PDO ──────────────────────────────────────────────
function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die('DB Error: ' . $e->getMessage());
            }
            // Redirecionar para página de manutenção
            http_response_code(503);
            header('Retry-After: 30');
            readfile($_SERVER['DOCUMENT_ROOT'] . '/errors/50x.html');
            exit;
        }
    }

    return $pdo;
}

// ── Sessão Segura ────────────────────────────────────────────
function init_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;

    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure',  0);        // HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

    session_start();

    // Regenerar ID a cada 30 minutos
    if (!isset($_SESSION['_created'])) {
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
}

// ── CSRF Protection ──────────────────────────────────────────
function csrf_token(): string {
    init_session();
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrf_field(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}

function csrf_verify(): bool {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    return hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
}

// ── Helpers ──────────────────────────────────────────────────
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void {
    init_session();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array {
    init_session();
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function current_lang(): string {
    init_session();
    return $_SESSION['lang'] ?? 'en';
}

function t(string $en, string $pt): string {
    return current_lang() === 'pt-BR' ? $pt : $en;
}