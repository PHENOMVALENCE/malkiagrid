<?php
declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';

if (!is_post() || !csrf_verify((string) ($_POST['_csrf'] ?? $_POST['_token'] ?? ''))) {
    flash_set('error', 'Ombi limekataliwa. Jaribu tena.');
    redirect(url('user/trainings.php'));
}

$pdo = db();
$uid = (int) auth_user()['user_id'];
$trainingId = (int) ($_POST['training_program_id'] ?? 0);

if ($trainingId <= 0) {
    flash_set('error', 'Mafunzo hayajachaguliwa.');
    redirect(url('user/trainings.php'));
}

if (trainings_user_has_active_registration($pdo, $uid, $trainingId)) {
    flash_set('error', 'Tayari umejisajili kwa mafunzo haya.');
    redirect(url('user/my_trainings.php'));
}

$stmt = $pdo->prepare('INSERT INTO training_registrations (user_id, training_id, registration_status, participation_status, certificate_status, created_at, updated_at)
                       VALUES (:uid, :tid, "pending", "not_started", "none", NOW(), NOW())');
$stmt->execute(['uid' => $uid, 'tid' => $trainingId]);

flash_set('success', 'Usajili wa mafunzo umetumwa.');
redirect(url('user/my_trainings.php'));
