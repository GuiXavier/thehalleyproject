<?php
/**
 * The Halley Project — Authentication System
 * Registro, login, sessão e funções de usuário
 */

require_once __DIR__ . '/config.php';

// ── Registrar novo usuário ───────────────────────────────────
function register_user(string $username, string $email, string $password): array {
    $errors = [];

    // Validações
    $username = trim($username);
    $email    = trim(strtolower($email));

    if (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = t('Username must be 3-30 characters.', 'Nome de usuário deve ter 3-30 caracteres.');
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = t('Username can only contain letters, numbers and underscore.', 'Nome de usuário só pode conter letras, números e underscore.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('Invalid email address.', 'Endereço de email inválido.');
    }
    if (strlen($password) < 8) {
        $errors[] = t('Password must be at least 8 characters.', 'Senha deve ter pelo menos 8 caracteres.');
    }

    if (!empty($errors)) return ['ok' => false, 'errors' => $errors];

    // Verificar duplicatas
    $stmt = db()->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $errors[] = t('Username or email already registered.', 'Nome de usuário ou email já registrado.');
        return ['ok' => false, 'errors' => $errors];
    }

    // Inserir
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    $stmt = db()->prepare(
        'INSERT INTO users (username, email, password, language) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$username, $email, $hash, current_lang()]);
    $user_id = db()->lastInsertId();

    // Login automático após registro
    login_session((int) $user_id, $username, 'user');

    return ['ok' => true, 'user_id' => $user_id];
}

// ── Login ────────────────────────────────────────────────────
function login_user(string $identity, string $password): array {
    init_session();

    // Rate limiting
    $attempts_key = 'login_attempts_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
    $attempts = $_SESSION[$attempts_key] ?? ['count' => 0, 'time' => 0];

    if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
        $elapsed = time() - $attempts['time'];
        if ($elapsed < LOGIN_LOCKOUT_MINUTES * 60) {
            $remaining = ceil((LOGIN_LOCKOUT_MINUTES * 60 - $elapsed) / 60);
            return ['ok' => false, 'errors' => [
                t("Too many attempts. Try again in {$remaining} minutes.",
                  "Muitas tentativas. Tente novamente em {$remaining} minutos.")
            ]];
        }
        $attempts = ['count' => 0, 'time' => 0];
    }

    // Buscar usuário por username ou email
    $stmt = db()->prepare(
        'SELECT id, username, email, password, role, is_active FROM users 
         WHERE (username = ? OR email = ?) LIMIT 1'
    );
    $stmt->execute([$identity, $identity]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $attempts['count']++;
        $attempts['time'] = time();
        $_SESSION[$attempts_key] = $attempts;

        return ['ok' => false, 'errors' => [
            t('Invalid username/email or password.', 'Usuário/email ou senha inválidos.')
        ]];
    }

    if (!$user['is_active']) {
        return ['ok' => false, 'errors' => [
            t('Account is suspended.', 'Conta suspensa.')
        ]];
    }

    // Limpar tentativas
    unset($_SESSION[$attempts_key]);

    // Atualizar último login
    $stmt = db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
    $stmt->execute([$user['id']]);

    // Criar sessão
    login_session((int) $user['id'], $user['username'], $user['role']);

    return ['ok' => true];
}

// ── Sessão de Login ──────────────────────────────────────────
function login_session(int $id, string $username, string $role): void {
    init_session();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $id;
    $_SESSION['username']  = $username;
    $_SESSION['role']      = $role;
    $_SESSION['logged_in'] = true;
    $_SESSION['_created']  = time();
}

// ── Logout ───────────────────────────────────────────────────
function logout(): void {
    init_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}

// ── Verificações ─────────────────────────────────────────────
function is_logged_in(): bool {
    init_session();
    return !empty($_SESSION['logged_in']);
}

function current_user_id(): ?int {
    init_session();
    return $_SESSION['user_id'] ?? null;
}

function current_username(): ?string {
    init_session();
    return $_SESSION['username'] ?? null;
}

function current_role(): string {
    init_session();
    return $_SESSION['role'] ?? 'guest';
}

function is_admin(): bool {
    return current_role() === 'admin';
}

function is_editor(): bool {
    return in_array(current_role(), ['editor', 'admin']);
}

function require_login(): void {
    if (!is_logged_in()) {
        flash('error', t('Please log in to continue.', 'Faça login para continuar.'));
        redirect(FORUM_URL . '/login.php');
    }
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        flash('error', t('Access denied.', 'Acesso negado.'));
        redirect(FORUM_URL . '/');
    }
}

// ── Perfil do Usuário ────────────────────────────────────────
function get_user(int $id): ?array {
    $stmt = db()->prepare(
        'SELECT id, username, email, role, bio, avatar_url, language, created_at, last_login 
         FROM users WHERE id = ? AND is_active = 1'
    );
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
