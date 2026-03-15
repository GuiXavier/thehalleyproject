<?php
/**
 * The Halley Project — Forum Header
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

init_session();

$_page_title = $page_title ?? 'Forum';
$_full_title = $_page_title . ' | ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="<?= current_lang() === 'pt-BR' ? 'pt-BR' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($_full_title) ?></title>
    <meta name="description" content="<?= sanitize($page_description ?? t('Community articles and discussions about Halley\'s Comet and astronomy.', 'Artigos e discussões da comunidade sobre o Cometa Halley e astronomia.')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/forum/css/forum.css">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#0a0e27">
</head>
<body>
    <div class="stars"></div>
    <nav>
        <div class="nav-container">
            <a href="/" class="nav-logo">The Halley Project</a>
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/about.php"><?= t('About', 'Sobre') ?></a>
                <a href="/articles/history.php"><?= t('History', 'História') ?></a>
                <a href="/articles/observation.php"><?= t('Observation', 'Observação') ?></a>
                <a href="/forum/" class="active"><?= t('Forum', 'Fórum') ?></a>
                <?php if (is_logged_in()): ?>
                    <a href="/forum/submit.php" class="nav-submit-btn"><?= t('+ Write', '+ Escrever') ?></a>
                    <a href="/forum/profile.php" class="nav-user"><?= sanitize(current_username()) ?></a>
                <?php else: ?>
                    <a href="/forum/login.php" class="nav-login-btn"><?= t('Sign In', 'Entrar') ?></a>
                <?php endif; ?>
                <a href="#" onclick="event.preventDefault(); fetch('/forum/lang.php?lang=<?= current_lang() === 'pt-BR' ? 'en' : 'pt-BR' ?>').then(()=>location.reload())" class="lang-switch">
                    <img src="/images/<?= current_lang() === 'pt-BR' ? 'gb.png' : 'br.png' ?>" alt="<?= current_lang() === 'pt-BR' ? 'English' : 'Português' ?>" class="lang-flag">
                    <span><?= current_lang() === 'pt-BR' ? 'English' : 'Português' ?></span>
                </a>
            </div>
        </div>
    </nav>
    <?php $flashes = get_flashes(); ?>
    <?php if (!empty($flashes)): ?>
        <div class="flash-container">
            <?php foreach ($flashes as $f): ?>
                <div class="flash flash-<?= $f['type'] ?>"><?= sanitize($f['message']) ?><button class="flash-close" onclick="this.parentElement.remove()">&times;</button></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <main class="forum-main">