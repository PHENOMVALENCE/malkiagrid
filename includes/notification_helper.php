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
    static $adminLogColumns = null;
    if (!is_array($adminLogColumns)) {
        $adminLogColumns = [];
        $columns = $pdo->query('SHOW COLUMNS FROM admin_logs')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($columns as $column) {
            $field = (string) ($column['Field'] ?? '');
            if ($field !== '') {
                $adminLogColumns[$field] = true;
            }
        }
    }

    $hasExtendedSchema = isset($adminLogColumns['target_type'], $adminLogColumns['target_id'], $adminLogColumns['details']);
    if ($hasExtendedSchema) {
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
        return;
    }

    $description = trim($targetType . '#' . $targetId . ($details !== null && $details !== '' ? ' - ' . $details : ''));
    $stmt = $pdo->prepare(
        'INSERT INTO admin_logs (admin_id, action, description, created_at)
         VALUES (:admin_id, :action, :description, NOW())'
    );
    $stmt->execute([
        ':admin_id' => $adminId,
        ':action' => $action,
        ':description' => $description !== '' ? $description : null,
    ]);
}

function notifications_module_ready(PDO $pdo): bool
{
    try {
        $stmt = $pdo->query('SHOW TABLES LIKE "notifications"');
        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function notification_type_badge_class(string $type): string
{
    return match (strtolower(trim($type))) {
        'success' => 'success',
        'warning' => 'warning',
        'error', 'danger' => 'danger',
        default => 'primary',
    };
}

function notifications_unread_count(int $userId): int
{
    $pdo = db();
    if (!notifications_module_ready($pdo)) {
        return 0;
    }
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0');
    $stmt->execute(['uid' => $userId]);
    return (int) $stmt->fetchColumn();
}

function getUnreadNotifications(int $userId, int $limit = 8): array
{
    return notifications_list_for_user($userId, true, $limit, 0);
}

function notifications_list_for_user(int $userId, ?bool $unreadOnly = null, int $limit = 25, int $offset = 0): array
{
    $pdo = db();
    if (!notifications_module_ready($pdo)) {
        return [];
    }

    $columns = [];
    foreach (($pdo->query('SHOW COLUMNS FROM notifications')->fetchAll(PDO::FETCH_ASSOC) ?: []) as $col) {
        $name = (string) ($col['Field'] ?? '');
        if ($name !== '') {
            $columns[$name] = true;
        }
    }

    $selectType = isset($columns['type']) ? 'type' : '"info" AS type';
    if (isset($columns['source_module'])) {
        $selectSource = 'source_module';
    } elseif (isset($columns['related_module'])) {
        $selectSource = 'related_module AS source_module';
    } else {
        $selectSource = '"system" AS source_module';
    }
    $selectUrl = isset($columns['action_url']) ? 'action_url' : 'NULL AS action_url';

    $sql = "SELECT id, user_id, title, message, is_read, created_at, {$selectType}, {$selectSource}, {$selectUrl}
            FROM notifications
            WHERE user_id = :uid";
    if ($unreadOnly === true) {
        $sql .= ' AND is_read = 0';
    } elseif ($unreadOnly === false) {
        $sql .= ' AND is_read = 1';
    }
    $sql .= ' ORDER BY created_at DESC LIMIT :lim OFFSET :off';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':lim', max(1, $limit), PDO::PARAM_INT);
    $stmt->bindValue(':off', max(0, $offset), PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return is_array($rows) ? $rows : [];
}

function markAllNotificationsReadForUser(int $userId): void
{
    $pdo = db();
    if (!notifications_module_ready($pdo)) {
        return;
    }
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0');
    $stmt->execute(['uid' => $userId]);
}

function markNotificationAsRead(int $notificationId, int $userId): void
{
    $pdo = db();
    if (!notifications_module_ready($pdo)) {
        return;
    }
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid');
    $stmt->execute(['id' => $notificationId, 'uid' => $userId]);
}

