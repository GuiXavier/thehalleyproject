<?php
require_once __DIR__ . '/includes/config.php';
init_session();
$lang = ($_GET['lang'] ?? 'en');
$_SESSION['lang'] = in_array($lang, ['en', 'pt-BR']) ? $lang : 'en';
header('Content-Type: application/json');
echo '{"ok":true}';