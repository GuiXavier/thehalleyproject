<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

init_session();

if (is_logged_in()) redirect(FORUM_URL . '/');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('Invalid request.', 'Requisição inválida.');
    } else {
        $username = $_POST['username'] ?? '';
        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if ($password !== $confirm) {
            $errors[] = t('Passwords do not match.', 'As senhas não conferem.');
        } else {
            $result = register_user($username, $email, $password);
            if ($result['ok']) {
                flash('success', t('Welcome to The Halley Project!', 'Bem-vindo ao The Halley Project!'));
                redirect(FORUM_URL . '/');
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

$page_title = t('Create Account', 'Criar Conta');
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <div class="form-card">
        <h1><?= t('Join the Community', 'Junte-se à Comunidade') ?></h1>

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
                <label for="username"><?= t('Username', 'Nome de Usuário') ?></label>
                <input type="text" id="username" name="username" required autofocus
                       value="<?= sanitize($_POST['username'] ?? '') ?>"
                       placeholder="<?= t('halley_observer', 'observador_halley') ?>"
                       minlength="3" maxlength="30" pattern="[a-zA-Z0-9_]+">
                <div class="form-hint"><?= t('3-30 characters. Letters, numbers, underscore.', '3-30 caracteres. Letras, números, underscore.') ?></div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?= sanitize($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com">
            </div>

            <div class="form-group">
                <label for="password"><?= t('Password', 'Senha') ?></label>
                <input type="password" id="password" name="password" required
                       minlength="8" placeholder="<?= t('Minimum 8 characters', 'Mínimo 8 caracteres') ?>">
            </div>

            <div class="form-group">
                <label for="password_confirm"><?= t('Confirm Password', 'Confirmar Senha') ?></label>
                <input type="password" id="password_confirm" name="password_confirm" required
                       minlength="8" placeholder="<?= t('Repeat your password', 'Repita sua senha') ?>">
            </div>

            <button type="submit" class="btn btn-primary">
                <?= t('Create Account', 'Criar Conta') ?>
            </button>
        </form>

        <div class="form-footer">
            <?= t('Already have an account?', 'Já tem uma conta?') ?>
            <a href="/forum/login.php"><?= t('Sign in', 'Entrar') ?></a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
