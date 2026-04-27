<?php
declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';

if (!is_post() || !csrf_verify((string) ($_POST['_csrf'] ?? $_POST['_token'] ?? ''))) {
    flash_set('error', 'Ombi limekataliwa. Jaribu tena.');
    redirect(url('user/opportunities.php'));
}

$pdo = db();
$uid = (int) auth_user()['user_id'];
$oppId = (int) ($_POST['opportunity_id'] ?? 0);
$note = clean_string($_POST['user_message'] ?? '');

if ($oppId <= 0) {
    flash_set('error', 'Fursa haijachaguliwa.');
    redirect(url('user/opportunities.php'));
}

if (opportunities_user_has_active_application($pdo, $uid, $oppId)) {
    flash_set('error', 'Tayari una ombi hai kwa fursa hii.');
    redirect(url('user/my_opportunities.php'));
}

$stmt = $pdo->prepare('INSERT INTO opportunity_applications (user_id, opportunity_id, status, application_note, created_at, updated_at)
                       VALUES (:uid, :oid, "submitted", :note, NOW(), NOW())');
$stmt->execute(['uid' => $uid, 'oid' => $oppId, 'note' => $note !== '' ? $note : null]);

flash_set('success', 'Ombi la fursa limetumwa kikamilifu.');
redirect(url('user/my_opportunities.php'));
