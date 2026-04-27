<?php
declare(strict_types=1);

require __DIR__ . '/user/includes/init_member.php';

if (!is_post() || !csrf_verify((string) ($_POST['_csrf'] ?? $_POST['_token'] ?? ''))) {
    flash_set('error', 'Ombi limekataliwa. Jaribu tena.');
    redirect(url('user/apply_funding.php'));
}

$uid = (int) auth_user()['user_id'];
$elig = checkFundingEligibility($uid);
if (!$elig['eligible']) {
    flash_set('error', 'Bado hukidhi vigezo vya M-FUND.');
    redirect(url('user/funding_overview.php'));
}

$amount = (float) ($_POST['requested_amount'] ?? 0);
$purpose = clean_string($_POST['purpose'] ?? '');
$businessName = clean_string($_POST['business_name'] ?? '');
$businessSector = clean_string($_POST['business_sector'] ?? '');
$businessSummary = trim($businessName . ($businessSector !== '' ? ' - ' . $businessSector : ''));
$repaymentPlan = clean_string($_POST['supporting_notes'] ?? '');

if ($amount <= 0 || $purpose === '') {
    flash_set('error', 'Jaza kiasi na madhumuni ya ombi.');
    redirect(url('user/apply_funding.php'));
}

$pdo = db();
$reference = 'MF-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
$stmt = $pdo->prepare('INSERT INTO funding_applications (reference_number, user_id, amount_requested, purpose, business_summary, repayment_plan, status, created_at, updated_at)
                       VALUES (:ref, :uid, :amount, :purpose, :summary, :repay, "submitted", NOW(), NOW())');
$stmt->execute([
    'ref' => $reference,
    'uid' => $uid,
    'amount' => $amount,
    'purpose' => $purpose,
    'summary' => $businessSummary !== '' ? $businessSummary : null,
    'repay' => $repaymentPlan !== '' ? $repaymentPlan : null,
]);

flash_set('success', 'Ombi la M-FUND limetumwa kikamilifu.');
redirect(url('user/my_funding_applications.php'));
