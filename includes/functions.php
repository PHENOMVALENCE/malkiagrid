<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function current_user(): ?array
{
    start_secure_session();
    $userId = $_SESSION['user_id'] ?? null;
    if (!is_numeric($userId)) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $userId]);
    $user = $stmt->fetch();

    return is_array($user) ? $user : null;
}

function current_admin(): ?array
{
    start_secure_session();
    $adminId = $_SESSION['admin_id'] ?? null;
    if (!is_numeric($adminId)) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM admins WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $adminId]);
    $admin = $stmt->fetch();

    return is_array($admin) ? $admin : null;
}

