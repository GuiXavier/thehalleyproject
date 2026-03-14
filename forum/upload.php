<?php
/**
 * The Halley Project — Image Upload Handler
 * Endpoint AJAX para upload de imagens do editor
 */

require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

// Verificações
if (!is_logged_in()) {
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Invalid method']);
    exit;
}

if (!csrf_verify()) {
    echo json_encode(['ok' => false, 'error' => 'Invalid token']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];

// Validar tamanho (2MB max)
if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) {
    echo json_encode(['ok' => false, 'error' => 'File too large (max ' . MAX_UPLOAD_MB . 'MB)']);
    exit;
}

// Validar tipo MIME real
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

if (!isset($allowed[$mime])) {
    echo json_encode(['ok' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP']);
    exit;
}

// Gerar nome único
$ext = $allowed[$mime];
$filename = date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

// Criar pasta de uploads se não existe
$upload_dir = __DIR__ . '/uploads';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}

$dest = $upload_dir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['ok' => false, 'error' => 'Failed to save file']);
    exit;
}

// URL pública
$url = FORUM_URL . '/uploads/' . $filename;

echo json_encode(['ok' => true, 'url' => $url, 'filename' => $filename]);
