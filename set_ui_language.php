<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_post()) {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$token = (string) ($payload['_csrf'] ?? '');
if (!verify_csrf($token)) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'message' => 'CSRF token invalid']);
    exit;
}

$lang = (string) ($payload['lang'] ?? 'sw');
$lang = strtolower(trim($lang));
$lang = in_array($lang, ['sw', 'en'], true) ? $lang : 'sw';

$_SESSION['preferred_language'] = $lang;

echo json_encode(['ok' => true, 'lang' => $lang]);
