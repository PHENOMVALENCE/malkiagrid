<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function is_https_request(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
        return true;
    }
    return false;
}

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        enforce_session_idle_timeout();
        return;
    }

    session_name(SESSION_NAME);

    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_start();
    enforce_session_idle_timeout();
}

function enforce_session_idle_timeout(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $role = (string) ($_SESSION['role'] ?? '');
    if ($role !== 'user' && $role !== 'admin') {
        return;
    }

    $now = time();
    $lastActivity = (int) ($_SESSION['_last_activity_at'] ?? 0);
    if ($lastActivity > 0 && ($now - $lastActivity) > SESSION_IDLE_TIMEOUT) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                (bool) ($params['secure'] ?? false),
                (bool) ($params['httponly'] ?? true)
            );
        }
        session_destroy();
        session_start();
        $_SESSION['_flash']['error'][] = 'Umetolewa kwa kutotumia mfumo kwa muda. Tafadhali ingia tena.';
        return;
    }

    $_SESSION['_last_activity_at'] = $now;
}

