<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

init_session();

// Já logado? Redirecionar
if (is_logged_in()) redirect(FORUM_URL . '/');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('Invalid request. Please try again.', 'Requisição inválida. Tente novamente.');
    } else {
        $identity = trim($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = login_user($identity, $password);

        if ($result['ok']) {
            flash('success', t('Welcome back!', 'Bem-vindo de volta!'));
            redirect(FORUM_URL . '/');
        } else {
            $errors = $result['errors'];
        }
    }
}

$page_title = t('Sign In', 'Entrar');
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container">
    <div class="form-card">
        <h1><?= t('Sign In', 'Entrar') ?></h1>

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
                <label for="identity"><?= t('Username or Email', 'Usuário ou Email') ?></label>
                <input type="text" id="identity" name="identity" required autofocus
                       value="<?= sanitize($_POST['identity'] ?? '') ?>"
                       placeholder="<?= t('your_username or email@example.com', 'seu_usuario ou email@exemplo.com') ?>">
            </div>

            <div class="form-group">
                <label for="password"><?= t('Password', 'Senha') ?></label>
                <input type="password" id="password" name="password" required
                       placeholder="<?= t('Your password', 'Sua senha') ?>">
            </div>

            <button type="submit" class="btn btn-primary">
                <?= t('Sign In', 'Entrar') ?>
            </button>
        </form>

        <div class="form-footer">
            <?= t("Don't have an account?", 'Não tem uma conta?') ?>
            <a href="/forum/register.php"><?= t('Create one', 'Criar uma') ?></a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
