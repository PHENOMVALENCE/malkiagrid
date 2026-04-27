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

function url(string $path = ''): string
{
    $clean = ltrim($path, '/');
    if (APP_URL !== '') {
        return APP_URL . ($clean !== '' ? '/' . $clean : '');
    }

    static $basePath = null;
    if ($basePath === null) {
        $projectRoot = realpath(__DIR__ . '/..') ?: '';
        $docRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? '')) ?: '';
        $basePath = '';

        if ($projectRoot !== '' && $docRoot !== '') {
            $projectNorm = str_replace('\\', '/', $projectRoot);
            $docNorm = rtrim(str_replace('\\', '/', $docRoot), '/');
            if ($docNorm !== '' && str_starts_with($projectNorm, $docNorm)) {
                $basePath = substr($projectNorm, strlen($docNorm)) ?: '';
            }
        }

        $basePath = '/' . trim(str_replace('\\', '/', (string) $basePath), '/');
        if ($basePath === '/') {
            $basePath = '';
        }
    }

    if ($clean === '') {
        return $basePath !== '' ? $basePath . '/' : '/';
    }

    return ($basePath !== '' ? $basePath : '') . '/' . $clean;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
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

function auth_actor(): ?array
{
    start_secure_session();
    $role = (string) ($_SESSION['role'] ?? '');

    if ($role === 'admin' && isset($_SESSION['admin_id'])) {
        $admin = current_admin();
        if (!is_array($admin)) {
            return null;
        }
        $fullName = trim((string) ($admin['full_name'] ?? 'Msimamizi'));

        return [
            'account_type' => 'admin',
            'admin_id' => (int) ($admin['id'] ?? 0),
            'full_name' => $fullName !== '' ? $fullName : 'Msimamizi',
            'admin_code' => 'ADM-' . str_pad((string) ((int) ($admin['id'] ?? 0)), 4, '0', STR_PAD_LEFT),
            'email' => (string) ($admin['email'] ?? ''),
            'role' => (string) ($admin['role'] ?? 'admin'),
        ];
    }

    if ($role === 'user' && isset($_SESSION['user_id'])) {
        $user = current_user();
        if (!is_array($user)) {
            return null;
        }
        $fullName = trim(
            (string) (($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['surname'] ?? ''))
        );

        return [
            'account_type' => 'user',
            'user_id' => (int) ($user['id'] ?? 0),
            'full_name' => $fullName !== '' ? $fullName : 'Mwanachama',
            'm_id' => (string) ($user['m_id'] ?? ''),
            'status' => (string) ($user['status'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
        ];
    }

    return null;
}

