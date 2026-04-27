<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';

function flash_set(string $type, string $message): void
{
    start_secure_session();
    $_SESSION['_flash'][$type][] = $message;
}

function flash_success(string $message): void
{
    flash_set('success', $message);
}

function flash_error(string $message): void
{
    flash_set('error', $message);
}

function flash_get(?string $type = null): array
{
    start_secure_session();
    $all = $_SESSION['_flash'] ?? [];

    if (!is_array($all)) {
        $_SESSION['_flash'] = [];
        return [];
    }

    if ($type === null) {
        unset($_SESSION['_flash']);
        return $all;
    }

    $messages = $all[$type] ?? [];
    unset($_SESSION['_flash'][$type]);
    if (empty($_SESSION['_flash'])) {
        unset($_SESSION['_flash']);
    }
    return is_array($messages) ? $messages : [];
}

