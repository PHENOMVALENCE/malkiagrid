<?php

declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';

$pdo = db();
$uid = (int) auth_user()['user_id'];
$ready = mbenefits_module_ready($pdo);

$claims = [];
if ($ready) {
    $st = $pdo->prepare('
        SELECT c.*, o.title AS offer_title, p.name AS provider_name
        FROM benefit_claims c
        INNER JOIN benefit_offers o ON o.id = c.benefit_id
        INNER JOIN benefit_providers p ON p.id = o.provider_id
        WHERE c.user_id = :u
        ORDER BY c.created_at DESC
        LIMIT 100
    ');
    $st->execute(['u' => $uid]);
    $claims = $st->fetchAll() ?: [];
}

$mgrid_page_title = mgrid_title('title.my_benefits');
require __DIR__ . '/includes/shell_open.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="mgrid-alert mgrid-alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash_get('error')): ?><div class="mgrid-alert mgrid-alert-danger"><?= e($msg) ?></div><?php endif; ?>

<div class="mgrid-card mb-3">
  <div class="mgrid-card-body d-flex flex-wrap justify-content-between gap-2">
    <div>
      <div class="mgrid-topbar-label">M-BENEFITS</div>
      <h1 class="mgrid-display mb-0" style="font-size:1.75rem;" data-i18n="user.my_benefits_title">My claims &amp; history</h1>
    </div>
    <a class="btn-mgrid btn-mgrid-primary align-self-center" href="<?= e(url('user/benefits.php')) ?>" data-i18n="user.browse_offers">Browse offers</a>
  </div>
</div>

<?php if (!$ready): ?>
  <div class="mgrid-alert mgrid-alert-danger" data-i18n="user.mbenefits_missing">M-Benefits is not installed.</div>
<?php elseif ($claims === []): ?>
  <div class="mgrid-card"><div class="mgrid-card-body text-muted" data-i18n="user.no_benefit_claims_yet">You have not claimed any benefits yet.</div></div>
<?php else: ?>
  <div class="mgrid-card">
    <div class="mgrid-card-body p-0">
      <div class="table-responsive">
        <table class="mgrid-table mb-0">
          <thead>
            <tr>
              <th data-i18n="user.th_reference">Reference</th>
              <th data-i18n="user.th_benefit">Benefit</th>
              <th data-i18n="user.th_provider">Provider</th>
              <th data-i18n="user.th_claimed">Claimed</th>
              <th data-i18n="user.th_status">Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($claims as $c): ?>
              <tr>
                <td class="mgrid-table-mid-cell">CLM-<?= (int) $c['id'] ?></td>
                <td><?= e((string) $c['offer_title']) ?></td>
                <td><?= e((string) $c['provider_name']) ?></td>
                <td><?= e(substr((string) $c['created_at'], 0, 16)) ?></td>
                <td><span class="badge text-bg-<?= e(mbenefits_claim_status_badge((string) $c['status'])) ?>"><?= e(mbenefits_claim_status_label((string) $c['status'])) ?></span></td>
                <td>
                  <a class="btn btn-sm btn-outline-primary" href="<?= e(url('user/benefit_claim_detail.php?id=' . (int) $c['id'])) ?>" data-i18n="user.btn_details">Details</a>
                  <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('user/benefit_detail.php?id=' . (int) $c['benefit_id'])) ?>" data-i18n="user.offer_details">Offer</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/shell_close.php'; ?>
