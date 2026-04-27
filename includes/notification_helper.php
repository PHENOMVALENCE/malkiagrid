<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function push_notification(PDO $pdo, int $userId, string $title, string $message, string $type = 'info'): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
         VALUES (:user_id, :title, :message, :type, 0, NOW())'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':message' => $message,
        ':type' => $type,
    ]);
}

function write_admin_log(PDO $pdo, int $adminId, string $action, string $targetType, int $targetId, ?string $details = null): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, created_at)
         VALUES (:admin_id, :action, :target_type, :target_id, :details, NOW())'
    );
    $stmt->execute([
        ':admin_id' => $adminId,
        ':action' => $action,
        ':target_type' => $targetType,
        ':target_id' => $targetId,
        ':details' => $details,
    ]);
}

