<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/functions.php';

$docId = (int) ($_GET['id'] ?? 0);
if ($docId <= 0) {
    http_response_code(404);
    exit('Nyaraka haijapatikana.');
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_SESSION['admin_id']);
$isUser = isset($_SESSION['role']) && $_SESSION['role'] === 'user' && isset($_SESSION['user_id']);

if (!$isAdmin && !$isUser) {
    http_response_code(403);
    exit('Huna ruhusa.');
}

$sql = 'SELECT id, user_id, file_path, mime_type, COALESCE(original_name, "") AS title FROM user_documents WHERE id = :id LIMIT 1';
$stmt = db()->prepare($sql);
$stmt->execute([':id' => $docId]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    http_response_code(404);
    exit('Nyaraka haijapatikana.');
}

if ($isUser && (int) $doc['user_id'] !== (int) $_SESSION['user_id']) {
    http_response_code(403);
    exit('Huna ruhusa ya kuona nyaraka hii.');
}

$relativePath = (string) ($doc['file_path'] ?? '');
$fullPath = realpath(__DIR__ . '/' . ltrim($relativePath, '/'));

if ($fullPath === false || !is_file($fullPath)) {
    http_response_code(404);
    exit('Faili haipo.');
}

$uploadsRoot = realpath(__DIR__ . '/uploads');
if ($uploadsRoot === false || strpos($fullPath, $uploadsRoot) !== 0) {
    http_response_code(403);
    exit('Njia ya faili hairuhusiwi.');
}

$mime = (string) ($doc['mime_type'] ?? '');
if ($mime === '') {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($fullPath);
}

$download = isset($_GET['download']) && $_GET['download'] === '1';
$filename = basename($fullPath);

header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . ($mime !== '' ? $mime : 'application/octet-stream'));
header('Content-Length: ' . (string) filesize($fullPath));
header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . rawurlencode($filename) . '"');

readfile($fullPath);
exit;

