<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

function user_login(string $firstName, string $password): bool
{
    start_secure_session();

    $stmt = db()->prepare('SELECT * FROM users WHERE first_name = :first_name LIMIT 1');
    $stmt->execute([':first_name' => $firstName]);
    $user = $stmt->fetch();

    if (!is_array($user)) {
        return false;
    }

    $passwordHash = $user['password_hash'] ?? null;
    if (!is_string($passwordHash) || $passwordHash === '') {
        return false;
    }

    if (!password_verify($password, $passwordHash)) {
        return false;
    }

    session_regenerate_id(true);
    unset($_SESSION['admin_id']);
    $_SESSION['user_id'] = (int) $user['id'];

    return true;
}

function admin_login(string $email, string $password): bool
{
    start_secure_session();

    $stmt = db()->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    if (!is_array($admin)) {
        return false;
    }

    $passwordHash = $admin['password_hash'] ?? null;
    if (!is_string($passwordHash) || $passwordHash === '') {
        return false;
    }

    if (!password_verify($password, $passwordHash)) {
        return false;
    }

    session_regenerate_id(true);
    unset($_SESSION['user_id']);
    $_SESSION['admin_id'] = (int) $admin['id'];

    return true;
}

function user_logout(): void
{
    start_secure_session();
    unset($_SESSION['user_id']);
}

function admin_logout(): void
{
    start_secure_session();
    unset($_SESSION['admin_id']);
}

function is_user_logged_in(): bool
{
    start_secure_session();
    return isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

function is_admin_logged_in(): bool
{
    start_secure_session();
    return isset($_SESSION['admin_id']) && is_numeric($_SESSION['admin_id']);
}

