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
$fundingStatus = $latestFunding ? (string) $latestFunding['status'] : 'Bado hujatuma ombi';
$mgrid_page_title = 'Dashibodi ya Mwanachama — Malkia Grid';
require __DIR__ . '/includes/shell_open.php';
?>

<section class="mgrid-page-section">
  <div class="mgrid-card">
    <div class="mgrid-card-body d-flex flex-wrap justify-content-between gap-3 align-items-center">
      <div>
        <p class="text-muted mb-1">Karibu</p>
        <h1 class="mgrid-dash-page-title mb-1"><?= e($fullName) ?></h1>
        <p class="small text-muted mb-0">M-ID: <span class="mgrid-table-mid-cell"><?= e($mId) ?></span></p>
      </div>
      <a class="btn-mgrid btn-mgrid-outline" href="<?= e(url('logout.php')) ?>">Toka</a>
    </div>
  </div>
</section>

<section class="mgrid-page-section">
  <div class="mgrid-grid-4">
    <article class="mgrid-stat-card">
      <div class="mgrid-stat-label">M-ID yako</div>
      <div class="mgrid-stat-value"><?= e($mId) ?></div>
    </article>
    <article class="mgrid-stat-card">
      <div class="mgrid-stat-label">Hali ya akaunti</div>
      <div class="mgrid-stat-value"><?= e($accountStatus) ?></div>
    </article>
    <article class="mgrid-stat-card">
      <div class="mgrid-stat-label">Ukamilifu wa wasifu</div>
      <div class="mgrid-stat-value"><?= (int) $profileCompletion ?>%</div>
    </article>
    <article class="mgrid-stat-card">
      <div class="mgrid-stat-label">M-SCORE</div>
      <div class="mgrid-stat-value"><?= (int) $mScore ?></div>
      <div class="mgrid-stat-sub"><?= e($mTier) ?></div>
    </article>
  </div>
</section>

<section class="mgrid-page-section">
  <div class="mgrid-grid-4">
    <article class="mgrid-card">
      <div class="mgrid-card-body">
        <div class="mgrid-stat-label">Nyaraka</div>
        <div class="mgrid-stat-value"><?= (int) $docVerified ?>/<?= (int) $docTotal ?></div>
        <div class="small text-muted">Inasubiri: <?= (int) $docPending ?></div>
      </div>
    </article>
    <article class="mgrid-card">
      <div class="mgrid-card-body">
        <div class="mgrid-stat-label">Fursa zilizochapishwa</div>
        <div class="mgrid-stat-value"><?= (int) $opportunitiesCount ?></div>
      </div>
    </article>
    <article class="mgrid-card">
      <div class="mgrid-card-body">
        <div class="mgrid-stat-label">Mafunzo yaliyo wazi</div>
        <div class="mgrid-stat-value"><?= (int) $trainingsCount ?></div>
      </div>
    </article>
    <article class="mgrid-card">
      <div class="mgrid-card-body">
        <div class="mgrid-stat-label">M-Fund</div>
        <div class="fw-semibold"><?= e($fundingStatus) ?></div>
      </div>
    </article>
  </div>
</section>

<section class="mgrid-page-section">
  <div class="mgrid-card">
    <div class="mgrid-card-header">
      <h2 class="mgrid-card-title mb-0">Vitendo vya haraka</h2>
    </div>
    <div class="mgrid-card-body d-flex flex-wrap gap-2">
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/profile.php')) ?>">Kamilisha wasifu</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/my_documents.php')) ?>">Pakia nyaraka</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/my_mscore.php')) ?>">Tazama M-SCORE</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/opportunities.php')) ?>">Tazama fursa</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/trainings.php')) ?>">Jisajili mafunzo</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/apply_funding.php')) ?>">Omba M-Fund</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('user/benefits.php')) ?>">Dai manufaa</a>
    </div>
  </div>
</section>

<section class="mgrid-page-section">
  <div class="mgrid-card">
    <div class="mgrid-card-header">
      <h2 class="mgrid-card-title mb-0">Arifa za karibuni</h2>
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

