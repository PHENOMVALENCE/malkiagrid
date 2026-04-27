<?php
declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';

if (!is_post() || !csrf_verify((string) ($_POST['_csrf'] ?? $_POST['_token'] ?? ''))) {
    flash_set('error', 'Ombi limekataliwa. Jaribu tena.');
    redirect(url('user/benefits.php'));
}

$pdo = db();
$uid = (int) auth_user()['user_id'];
$benefitId = (int) ($_POST['benefit_offer_id'] ?? 0);
$note = clean_string($_POST['user_notes'] ?? '');

if ($benefitId <= 0) {
    flash_set('error', 'Ofa haijachaguliwa.');
    redirect(url('user/benefits.php'));
}

$offer = mbenefits_get_offer($pdo, $benefitId);
if (!$offer || (string) ($offer['status'] ?? '') !== 'published') {
    flash_set('error', 'Ofa haipatikani.');
    redirect(url('user/benefits.php'));
}

$ev = mbenefits_evaluate_eligibility($pdo, $uid, $offer);
if (!$ev['ok']) {
    flash_set('error', 'Hujakidhi vigezo vya kuomba ofa hii.');
    redirect(url('user/benefit_detail.php?id=' . $benefitId));
}

$stmt = $pdo->prepare('INSERT INTO benefit_claims (user_id, benefit_id, status, claim_note, created_at, updated_at)
                       VALUES (:uid, :bid, "pending", :note, NOW(), NOW())');
$stmt->execute(['uid' => $uid, 'bid' => $benefitId, 'note' => $note !== '' ? $note : null]);

flash_set('success', 'Ombi la manufaa limetumwa.');
redirect(url('user/my_benefits.php'));
