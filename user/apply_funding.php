<?php

declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';
$auth = auth_user();
$uid = (int) $auth['user_id'];
$elig = checkFundingEligibility($uid);

$pdo = db();
$minAmount = (float) mfund_setting($pdo, 'min_funding_amount', '50000');
$maxAmount = (float) mfund_setting($pdo, 'max_funding_amount', '20000000');

$mgrid_page_title = mgrid_title('title.apply_funding');
require __DIR__ . '/includes/shell_open.php';
?>

<?php if (!$elig['eligible']): ?>
  <div class="mgrid-alert mgrid-alert-warning" data-i18n="user.apply_funding_not_eligible">You are currently not eligible to apply. Please complete the missing requirements in M-FUND Overview.</div>
<?php endif; ?>

<div class="mgrid-card">
  <div class="mgrid-card-header"><h1 class="mgrid-card-title"><i class="ti ti-cash-banknote"></i> <span data-i18n="user.apply_funding_title">Apply for Funding</span></h1></div>
  <div class="mgrid-card-body">
    <form method="post" action="<?= e(url('save_funding_application.php')) ?>" enctype="multipart/form-data" class="row g-3">
      <?= csrf_field() ?>
      <div class="col-md-4">
        <label class="mgrid-form-label" data-i18n="user.application_type">Application Type</label>
        <select name="application_type" class="mgrid-form-control" required>
          <option value="loan" data-i18n="user.app_type_loan">Loan</option>
          <option value="grant" data-i18n="user.app_type_grant">Grant</option>
          <option value="support" data-i18n="user.app_type_support">Support</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="mgrid-form-label" data-i18n="user.requested_amount">Requested Amount (TZS)</label>
        <input type="number" step="0.01" min="<?= e((string) $minAmount) ?>" max="<?= e((string) $maxAmount) ?>" name="requested_amount" class="mgrid-form-control" required>
      </div>
      <div class="col-md-4">
        <label class="mgrid-form-label" data-i18n="user.business_sector">Business Sector</label>
        <input type="text" name="business_sector" class="mgrid-form-control" required>
      </div>
      <div class="col-md-6">
        <label class="mgrid-form-label" data-i18n="user.business_name">Business Name</label>
        <input type="text" name="business_name" class="mgrid-form-control" required>
      </div>
      <div class="col-md-6">
        <label class="mgrid-form-label" data-i18n="user.purpose_funding">Purpose of Funding</label>
        <input type="text" name="purpose" class="mgrid-form-control" required>
      </div>
      <div class="col-md-4">
        <label class="mgrid-form-label" data-i18n="user.monthly_revenue">Monthly Revenue Estimate (TZS)</label>
        <input type="number" step="0.01" min="0" name="monthly_revenue_estimate" class="mgrid-form-control">
      </div>
      <div class="col-md-4">
        <label class="mgrid-form-label" data-i18n="user.repayment_capacity">Repayment Capacity (TZS / month)</label>
        <input type="number" step="0.01" min="0" name="repayment_capacity" class="mgrid-form-control">
      </div>
      <div class="col-md-4">
        <label class="mgrid-form-label" data-i18n="user.repayment_period">Proposed Repayment Period (months)</label>
        <input type="number" min="1" max="120" name="proposed_repayment_period" class="mgrid-form-control">
      </div>
      <div class="col-12">
        <label class="mgrid-form-label" data-i18n="user.business_description">Business Description</label>
        <textarea name="business_description" rows="3" class="mgrid-form-control"></textarea>
      </div>
      <div class="col-12">
        <label class="mgrid-form-label" data-i18n="user.reason_request">Reason for Request</label>
        <textarea name="request_reason" rows="3" class="mgrid-form-control" required></textarea>
      </div>
      <div class="col-12">
        <label class="mgrid-form-label" data-i18n="user.supporting_notes">Supporting Notes</label>
        <textarea name="supporting_notes" rows="2" class="mgrid-form-control"></textarea>
      </div>
      <div class="col-12">
        <label class="mgrid-form-label" data-i18n="user.optional_support_doc">Optional Supporting Document (PDF/JPG/JPEG/PNG, max 8MB)</label>
        <input type="file" name="supporting_document" class="mgrid-form-control" accept=".pdf,.jpg,.jpeg,.png">
      </div>
      <div class="col-12 form-check">
        <input class="form-check-input" type="checkbox" id="declaration" name="declaration" value="1" required>
        <label class="form-check-label" for="declaration" data-i18n="user.declaration">I confirm the submitted information is true and accurate.</label>
      </div>
      <div class="col-12 d-flex justify-content-end">
        <button class="btn-mgrid btn-mgrid-primary" type="submit" <?= $elig['eligible'] ? '' : 'disabled' ?> data-i18n="user.submit_application">Submit Application</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/shell_close.php'; ?>
