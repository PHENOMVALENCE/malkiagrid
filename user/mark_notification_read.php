<?php
declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';

if (!is_post() || !csrf_verify((string) ($_POST['_csrf'] ?? $_POST['_token'] ?? ''))) {
    redirect(url('user/notifications.php'));
}

$uid = (int) auth_user()['user_id'];
$nid = (int) ($_POST['notification_id'] ?? 0);
if ($nid > 0) {
    markNotificationAsRead($nid, $uid);
}

$redirect = (string) ($_POST['redirect'] ?? '');
if ($redirect !== '' && str_starts_with($redirect, '/')) {
    redirect($redirect);
}
redirect(url('user/notifications.php'));
