<?php

declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';

$pdo = db();
$uid = (int) auth_user()['user_id'];
$id = (int) ($_GET['id'] ?? 0);

if (!mbenefits_module_ready($pdo) || $id <= 0) {
    flash_set('error', __('benefit.not_found'));
    redirect(url('user/benefits.php'));
}

$offer = mbenefits_get_offer($pdo, $id);
if ($offer === null || (string) ($offer['status'] ?? '') !== 'published') {
    flash_set('error', __('benefit.not_found'));
    redirect(url('user/benefits.php'));
}

$ev = mbenefits_evaluate_eligibility($pdo, $uid, $offer);
$eligible = $ev['ok'];
$msg = mbenefits_get_eligibility_message($pdo, $uid, $id);

$mgrid_page_title = (string) $offer['title'] . ' — ' . __('site.brand');
require __DIR__ . '/includes/shell_open.php';
?>

<?php if ($m = flash_get('success')): ?><div class="mgrid-alert mgrid-alert-success"><?= e($m) ?></div><?php endif; ?>
<?php if ($m = flash_get('error')): ?><div class="mgrid-alert mgrid-alert-danger"><?= e($m) ?></div><?php endif; ?>

<div class="mgrid-card mb-3">
  <div class="mgrid-card-body">
    <div class="d-flex flex-wrap gap-2 mb-2">
      <span class="badge text-bg-<?= e(mbenefits_benefit_type_badge((string) $offer['benefit_type'])) ?>"><?= e(mbenefits_benefit_type_label((string) $offer['benefit_type'])) ?></span>
      <span class="badge text-bg-<?= $eligible ? 'success' : 'secondary' ?>">
        <span data-i18n="<?= $eligible ? 'user.benefit_you_eligible' : 'user.benefit_not_eligible_yet' ?>"><?= $eligible ? 'You are eligible' : 'Not eligible yet' ?></span>
      </span>
    </div>
    <div class="small text-muted mb-1"><?= e((string) $offer['category_name']) ?> · <?= e((string) $offer['provider_name']) ?></div>
    <h1 class="mgrid-display mb-2" style="font-size:1.75rem;"><?= e((string) $offer['title']) ?></h1>
    <p class="lead mb-3" style="font-size:1rem;"><?= e((string) $offer['short_description']) ?></p>
    <div class="row g-3">
      <div class="col-md-6">
        <div class="p-3 rounded" style="background:var(--mgrid-surface-2);">
          <div class="small text-muted" data-i18n="user.value">Value</div>
          <div class="mgrid-mono-id" style="font-size:1.25rem;"><?= e((string) $offer['value_label']) ?></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="p-3 rounded" style="background:var(--mgrid-surface-2);">
          <div class="small text-muted" data-i18n="user.validity_label">Validity</div>
          <div><?= e((string) $offer['valid_from']) ?> → <?= e((string) $offer['valid_to']) ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="mgrid-card mb-3">
      <div class="mgrid-card-header"><h2 class="mgrid-card-title"><i class="ti ti-file-text"></i> <span data-i18n="user.full_description">Full description</span></h2></div>
      <div class="mgrid-card-body">
        <p style="white-space:pre-wrap;"><?= e(trim((string) ($offer['full_description'] ?? $offer['short_description']))) ?></p>
      </div>
    </div>
    <div class="mgrid-card mb-3">
      <div class="mgrid-card-header"><h2 class="mgrid-card-title"><i class="ti ti-list-check"></i> <span data-i18n="user.terms_conditions">Terms &amp; conditions</span></h2></div>
      <div class="mgrid-card-body">
        <p style="white-space:pre-wrap;" class="mb-0"><?= e(trim((string) ($offer['terms_and_conditions'] ?? 'See partner terms at redemption.'))) ?></p>
      </div>
    </div>
    <div class="mgrid-card">
      <div class="mgrid-card-header"><h2 class="mgrid-card-title"><i class="ti ti-route"></i> <span data-i18n="user.redemption_method">Redemption method</span></h2></div>
      <div class="mgrid-card-body">
        <p style="white-space:pre-wrap;" class="mb-0"><?= e(trim((string) ($offer['redemption_method'] ?? 'Instructions will appear on your claim after approval.'))) ?></p>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="mgrid-card mb-3">
      <div class="mgrid-card-header"><h2 class="mgrid-card-title"><i class="ti ti-building"></i> <span data-i18n="user.provider">Provider</span></h2></div>
      <div class="mgrid-card-body">
        <p class="fw-semibold mb-1"><?= e((string) $offer['provider_name']) ?></p>
        <?php if (trim((string) ($offer['provider_description'] ?? '')) !== ''): ?>
          <p class="small text-muted"><?= e((string) $offer['provider_description']) ?></p>
        <?php endif; ?>
        <?php if (trim((string) ($offer['provider_website'] ?? '')) !== ''): ?>
          <a href="<?= e((string) $offer['provider_website']) ?>" class="small" target="_blank" rel="noopener" data-i18n="user.website">Website</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="mgrid-card mb-3">
      <div class="mgrid-card-header"><h2 class="mgrid-card-title"><i class="ti ti-shield-check"></i> <span data-i18n="user.eligibility">Eligibility</span></h2></div>
      <div class="mgrid-card-body">
        <p class="small mb-2"><?= e(mbenefits_eligibility_rule_summary($offer)) ?></p>
        <p class="small mb-0 <?= $eligible ? 'text-success' : 'text-muted' ?>"><?= e($msg) ?></p>
      </div>
    </div>
    <div class="mgrid-card">
      <div class="mgrid-card-body">
        <?php if ($eligible): ?>
          <form method="post" action="<?= e(url('user/claim_benefit.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="benefit_offer_id" value="<?= (int) $offer['id'] ?>">
            <div class="mb-2">
              <label class="form-label small" data-i18n="user.optional_note">Optional note</label>
              <input type="text" name="user_notes" class="mgrid-form-control" maxlength="500" data-i18n-placeholder="user.optional_note_ph" placeholder="e.g. preferred clinic branch">
            </div>
            <button type="submit" class="btn-mgrid btn-mgrid-primary w-100"><i class="ti ti-gift"></i> <span data-i18n="user.request_redeem">Request / redeem</span></button>
          </form>
        <?php else: ?>
          <button class="btn btn-secondary w-100" disabled data-i18n="user.cannot_claim_yet">Cannot claim yet</button>
          <p class="small text-muted mt-2 mb-0" data-i18n="user.complete_requirements_then_return">Complete the requirements above, then return to claim.</p>
        <?php endif; ?>
        <a class="btn-mgrid btn-mgrid-outline w-100 mt-2" href="<?= e(url('user/benefits.php')) ?>" data-i18n="user.back_to_offers">Back to offers</a>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/shell_close.php'; ?>
