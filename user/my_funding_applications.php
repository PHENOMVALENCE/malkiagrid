<?php

declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';
$auth = auth_user();
$uid = (int) $auth['user_id'];
$pdo = db();

$stmt = $pdo->prepare('
  SELECT id, reference_number, "funding" AS application_type, amount_requested AS requested_amount, status, created_at AS submitted_at
  FROM funding_applications
  WHERE user_id = :uid
  ORDER BY created_at DESC
');
$stmt->execute(['uid' => $uid]);
$rows = $stmt->fetchAll() ?: [];

$mgrid_page_title = mgrid_title('title.my_funding');
require __DIR__ . '/includes/shell_open.php';
?>

<div class="mgrid-card">
  <div class="mgrid-card-header">
    <h1 class="mgrid-card-title"><i class="ti ti-list-details"></i> <span data-i18n="user.my_funding_apps_title">My Funding Applications</span></h1>
    <div class="d-flex gap-2">
      <a class="btn-mgrid btn-mgrid-outline" href="<?= e(url('user/funding_overview.php')) ?>" data-i18n="user.overview">Overview</a>
      <a class="btn-mgrid btn-mgrid-primary" href="<?= e(url('user/apply_funding.php')) ?>" data-i18n="user.apply">Apply</a>
    </div>
  </div>
  <div class="mgrid-card-body p-0">
    <div class="table-responsive">
      <table class="mgrid-table">
        <thead><tr><th data-i18n="user.th_reference">Reference</th><th data-i18n="user.type">Type</th><th data-i18n="user.th_requested">Requested</th><th data-i18n="user.th_submitted">Submitted</th><th data-i18n="user.th_status">Status</th><th></th></tr></thead>
        <tbody>
          <?php if ($rows === []): ?>
            <tr><td colspan="6" class="text-center" style="padding:24px;" data-i18n="user.no_apps_yet">No applications submitted yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td class="mgrid-table-mid-cell"><?= e((string) $r['reference_number']) ?></td>
              <td><?= e(strtoupper((string) $r['application_type'])) ?></td>
              <td>TZS <?= number_format((float) $r['requested_amount'], 2) ?></td>
              <td><?= e(substr((string) $r['submitted_at'], 0, 16)) ?></td>
              <td><span class="badge text-bg-<?= e(mfund_status_badge((string) $r['status'])) ?>"><?= e(mfund_status_label((string) $r['status'])) ?></span></td>
              <td><a class="btn btn-sm btn-outline-primary" href="<?= e(url('user/funding_application_detail.php?id=' . (int) $r['id'])) ?>" data-i18n="user.btn_view">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/shell_close.php'; ?>
