<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

function csrf_token(): string
{
    start_secure_session();

    $token = $_SESSION[CSRF_TOKEN_KEY] ?? null;
    $createdAt = $_SESSION[CSRF_TOKEN_KEY . '_time'] ?? 0;
    $expired = !is_int($createdAt) || (time() - $createdAt > CSRF_TOKEN_TTL);

    if (!is_string($token) || $token === '' || $expired) {
        $token = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_KEY] = $token;
        $_SESSION[CSRF_TOKEN_KEY . '_time'] = time();
    }

    return $token;
}

function csrf_input(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(?string $token): bool
{
    start_secure_session();

    $sessionToken = $_SESSION[CSRF_TOKEN_KEY] ?? '';
    if (!is_string($sessionToken) || $sessionToken === '' || !is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($sessionToken, $token);
}

function require_csrf(): void
{
    $token = $_POST['_token'] ?? '';
    if (!verify_csrf(is_string($token) ? $token : null)) {
        http_response_code(419);
        exit('CSRF token si sahihi.');
    }
}

