<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/guards/user_guard.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
$user = current_user();

if (!$user) {
    redirect(url('login.php'));
}

$userId = (int) $user['id'];
$accountStatus = (string) ($user['status'] ?? 'pending');
if ($accountStatus !== 'active') {
    redirect(url('pending-verification.php'));
}

$fullName = trim(
    (string) (($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['surname'] ?? ''))
);
if ($fullName === '') {
    $fullName = (string) ($user['full_name'] ?? 'Mwanachama');
}

$mId = (string) ($user['m_id'] ?? '—');

$profileStmt = $pdo->prepare('SELECT profile_completion FROM user_profiles WHERE user_id = :user_id LIMIT 1');
$profileStmt->execute([':user_id' => $userId]);
$profileRow = $profileStmt->fetch(PDO::FETCH_ASSOC) ?: [];
$profileCompletion = (int) ($profileRow['profile_completion'] ?? 0);
$profileCompletion = max(0, min(100, $profileCompletion));

$scoreStmt = $pdo->prepare('SELECT total_score, tier FROM mscore_current_scores WHERE user_id = :user_id LIMIT 1');
$scoreStmt->execute([':user_id' => $userId]);
$scoreRow = $scoreStmt->fetch(PDO::FETCH_ASSOC) ?: [];
$mScore = (int) ($scoreRow['total_score'] ?? 0);
$mTier = (string) ($scoreRow['tier'] ?? 'Beginner');

$docSummaryStmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS total_docs,
        SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) AS verified_docs,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_docs
     FROM user_documents
     WHERE user_id = :user_id"
);
$docSummaryStmt->execute([':user_id' => $userId]);
$docSummary = $docSummaryStmt->fetch(PDO::FETCH_ASSOC) ?: [];
$docTotal = (int) ($docSummary['total_docs'] ?? 0);
$docVerified = (int) ($docSummary['verified_docs'] ?? 0);
$docPending = (int) ($docSummary['pending_docs'] ?? 0);

$opStmt = $pdo->prepare("SELECT COUNT(*) FROM opportunities WHERE status = 'published'");
$opStmt->execute();
$opportunitiesCount = (int) $opStmt->fetchColumn();

$trainStmt = $pdo->prepare("SELECT COUNT(*) FROM training_programs WHERE status = 'published'");
$trainStmt->execute();
$trainingsCount = (int) $trainStmt->fetchColumn();

$benefitStmt = $pdo->prepare("SELECT COUNT(*) FROM benefit_offers WHERE status = 'published'");
$benefitStmt->execute();
$benefitsCount = (int) $benefitStmt->fetchColumn();

$fundingStmt = $pdo->prepare(
    'SELECT status, created_at
     FROM funding_applications
     WHERE user_id = :user_id
     ORDER BY created_at DESC
     LIMIT 1'
);
$fundingStmt->execute([':user_id' => $userId]);
$latestFunding = $fundingStmt->fetch(PDO::FETCH_ASSOC) ?: null;

$notifStmt = $pdo->prepare(
    'SELECT title, message, created_at, is_read
     FROM notifications
     WHERE user_id = :user_id
     ORDER BY created_at DESC
     LIMIT 5'
);
$notifStmt->execute([':user_id' => $userId]);
$notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
$unreadCount = 0;
foreach ($notifications as $n) {
    if ((int) ($n['is_read'] ?? 0) === 0) {
        $unreadCount++;
    }
}
$fundingStatus = $latestFunding ? mfund_status_label((string) $latestFunding['status']) : __('display.dashboard_no_funding_app');
$profileCompletionPct = max(0, min(100, (int) $profileCompletion));
$mgrid_page_title = 'Dashibodi ya Mwanachama — Malkia Grid';
require __DIR__ . '/includes/shell_open.php';
?>

<section class="mgrid-page-section mgrid-dashboard-shell">
  <div class="mgrid-card mgrid-dash-hero">
    <div class="mgrid-card-body">
      <div class="mgrid-dash-hero-grid">
        <div>
          <p class="text-muted mb-1">Karibu</p>
          <h1 class="mgrid-dash-page-title mb-1"><?= e($fullName) ?></h1>
          <p class="small text-muted mb-2">M-ID: <span class="mgrid-table-mid-cell"><?= e($mId) ?></span></p>
          <div class="mgrid-progress-wrap mb-0">
            <div class="mgrid-progress-track">
              <div
                class="mgrid-progress-fill mgrid-progress-fill--hero"
                style="width: <?= $profileCompletionPct ?>%; min-width: <?= $profileCompletionPct > 0 ? '10px' : '0' ?>;"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="<?= $profileCompletionPct ?>"
              ></div>
            </div>
            <div class="mgrid-progress-meta">
              <span>Ukamilifu wa M PROFILE</span>
              <span><?= $profileCompletionPct ?>%</span>
            </div>
          </div>
        </div>
        <div class="mgrid-dash-hero-actions">
          <a class="btn-mgrid btn-mgrid-primary" href="<?= e(url('user/profile.php')) ?>">Kamilisha M PROFILE</a>
          <a class="btn-mgrid btn-mgrid-outline" href="<?= e(url('user/my_documents.php')) ?>">Pakia Nyaraka</a>
          <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('logout.php')) ?>">Toka</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="mgrid-page-section mgrid-dashboard-shell">
  <div class="mgrid-grid-4">
    <article class="mgrid-stat-card mgrid-dash-stat">
      <div class="mgrid-stat-label">M-ID yako</div>
      <div class="mgrid-stat-value"><?= e($mId) ?></div>
      <div class="mgrid-stat-sub">Kitambulisho chako cha kudumu</div>
    </article>
    <article class="mgrid-stat-card mgrid-dash-stat">
      <div class="mgrid-stat-label">Hali ya akaunti</div>
      <div class="mgrid-stat-value"><?= e(mgrid_account_status_label($accountStatus)) ?></div>
      <div class="mgrid-stat-sub">Ili kufungua huduma zote</div>
    </article>
    <article class="mgrid-stat-card mgrid-dash-stat">
      <div class="mgrid-stat-label">Ukamilifu wa wasifu</div>
      <div class="mgrid-stat-value"><?= (int) $profileCompletion ?>%</div>
      <div class="mgrid-stat-sub">Lengo: 100%</div>
    </article>
    <article class="mgrid-stat-card mgrid-dash-stat">
      <div class="mgrid-stat-label">M-SCORE</div>
      <div class="mgrid-stat-value"><?= (int) $mScore ?></div>
      <div class="mgrid-stat-sub"><?= e(mgrid_mscore_tier_display_label($mTier)) ?></div>
    </article>
  </div>
</section>

<section class="mgrid-page-section mgrid-dashboard-shell">
  <div class="row g-3">
    <div class="col-lg-7">
      <div class="mgrid-card h-100">
        <div class="mgrid-card-header">
          <h2 class="mgrid-card-title mb-0">Hatua za haraka</h2>
        </div>
        <div class="mgrid-card-body mgrid-dash-actions-grid">
          <a class="mgrid-quick-link" href="<?= e(url('user/profile.php')) ?>"><i class="ti ti-user-circle"></i> M PROFILE</a>
          <a class="mgrid-quick-link is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa"><i class="ti ti-lock"></i> M‑Documents</a>
          <a class="mgrid-quick-link is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa"><i class="ti ti-lock"></i> M SCORE</a>
          <a class="mgrid-quick-link is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa"><i class="ti ti-lock"></i> Fursa</a>
          <a class="mgrid-quick-link is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa"><i class="ti ti-lock"></i> Mafunzo</a>
          <a class="mgrid-quick-link is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa"><i class="ti ti-lock"></i> M‑Benefits</a>
          <a class="mgrid-quick-link is-locked" href="javascript:void(0)" onclick="return false;" aria-disabled="true" title="Imefungwa kwa sasa"><i class="ti ti-lock"></i> M‑Fund</a>
          <a class="mgrid-quick-link" href="<?= e(url('user/notifications.php')) ?>"><i class="ti ti-bell"></i> Arifa (<?= (int) $unreadCount ?> mpya)</a>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="mgrid-card h-100">
        <div class="mgrid-card-header">
          <h2 class="mgrid-card-title mb-0">Muhtasari wa leo</h2>
        </div>
        <div class="mgrid-card-body">
          <div class="mgrid-dash-mini-stat">
            <span>Nyaraka</span>
            <strong><?= (int) $docVerified ?>/<?= (int) $docTotal ?> <?= e(__('display.docs_verified_suffix')) ?></strong>
          </div>
          <div class="mgrid-dash-mini-stat">
            <span>Inasubiri hakiki</span>
            <strong><?= (int) $docPending ?></strong>
          </div>
          <div class="mgrid-dash-mini-stat">
            <span>Fursa zilizopo</span>
            <strong><?= (int) $opportunitiesCount ?></strong>
          </div>
          <div class="mgrid-dash-mini-stat">
            <span>Mafunzo yaliyofunguliwa</span>
            <strong><?= (int) $trainingsCount ?></strong>
          </div>
          <div class="mgrid-dash-mini-stat">
            <span>Benefits zilizopo</span>
            <strong><?= (int) $benefitsCount ?></strong>
          </div>
          <div class="mgrid-dash-mini-stat">
            <span>Hali ya M‑Fund</span>
            <strong><?= e($fundingStatus) ?></strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="mgrid-page-section mgrid-dashboard-shell">
  <div class="mgrid-card">
    <div class="mgrid-card-header">
      <h2 class="mgrid-card-title mb-0">Vitendo vya haraka</h2>
    </div>
    <div class="mgrid-card-body d-flex flex-wrap gap-2">
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/profile.php')) ?>">Hariri M PROFILE</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/settings.php')) ?>">Mipangilio</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/notifications.php')) ?>">Arifa</a>
    </div>
  </div>
</section>

<section class="mgrid-page-section mgrid-dashboard-shell">
  <div class="mgrid-card">
    <div class="mgrid-card-header">
      <h2 class="mgrid-card-title mb-0">Arifa za karibuni (<?= (int) $unreadCount ?> mpya)</h2>
    </div>
    <div class="mgrid-card-body">
      <?php if ($notifications === []): ?>
        <p class="text-muted mb-0">Hakuna arifa mpya.</p>
      <?php else: ?>
        <ul class="list-unstyled mb-0">
          <?php foreach ($notifications as $n): ?>
            <li class="mb-3 pb-3 border-bottom">
              <div class="d-flex justify-content-between">
                <strong><?= e((string) $n['title']) ?></strong>
                <small class="text-muted"><?= e(substr((string) $n['created_at'], 0, 16)) ?></small>
              </div>
              <div class="small text-muted"><?= e((string) $n['message']) ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/shell_close.php'; ?>

