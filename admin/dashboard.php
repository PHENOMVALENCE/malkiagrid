<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/guards/admin_guard.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();

$usersStatsStmt = $pdo->query(
    "SELECT
        COUNT(*) AS total_users,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_users,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_users,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_users
     FROM users"
);
$usersStats = $usersStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$docStatsStmt = $pdo->query(
    "SELECT
        COUNT(*) AS total_documents,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_documents
     FROM user_documents"
);
$docStats = $docStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$fundingStatsStmt = $pdo->query(
    "SELECT
        COUNT(*) AS total_funding,
        SUM(CASE WHEN status IN ('submitted','under_review','more_info_requested') THEN 1 ELSE 0 END) AS pending_funding
     FROM funding_applications"
);
$fundingStats = $fundingStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

$pendingNidaStmt = $pdo->prepare(
    "SELECT d.id, d.created_at AS uploaded_at, u.m_id, u.first_name, u.middle_name, u.surname
     FROM user_documents d
     INNER JOIN users u ON u.id = d.user_id
     INNER JOIN document_types dt ON dt.id = d.document_type_id
     WHERE dt.code = 'nida' AND d.status = 'pending'
     ORDER BY d.created_at ASC
     LIMIT 10"
);
$pendingNidaStmt->execute();
$pendingNida = $pendingNidaStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$recentLogsStmt = $pdo->prepare(
    "SELECT action, description, created_at
     FROM admin_logs
     ORDER BY created_at DESC
     LIMIT 10"
);
$recentLogsStmt->execute();
$recentLogs = $recentLogsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$mgrid_page_title = 'Admin Dashboard — M GRID';
require __DIR__ . '/includes/shell_open.php';
?>

<section class="mgrid-page-section">
  <div class="mgrid-card">
    <div class="mgrid-card-body">
      <p class="text-uppercase small mb-2" data-i18n="admin.dashboard.kicker">Kitovu cha usimamizi</p>
      <h1 class="mgrid-dash-page-title mb-2" data-i18n="admin.dashboard.title">Dashibodi ya msimamizi</h1>
      <p class="text-muted mb-3" data-i18n="admin.dashboard.subtitle">Fuatilia uhakiki, ukuaji wa wanachama, na shughuli za wasimamizi kwa pamoja.</p>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn-mgrid btn-mgrid-primary" href="<?= e(url('admin/admin_documents.php')) ?>" data-i18n="admin.dashboard.queue">Foleni ya NIDA</a>
        <a class="btn-mgrid btn-mgrid-outline" href="<?= e(url('logout.php')) ?>" data-i18n="sidebar.logout">Toka</a>
      </div>
    </div>
  </div>
</section>

<section class="mgrid-page-section">
  <div class="mgrid-grid-4">
    <article class="mgrid-stat-card">
      <div class="mgrid-stat-label" data-i18n="admin.dashboard.total_members">Wanachama wote</div>
      <div class="mgrid-stat-value"><?= (int) ($usersStats['total_users'] ?? 0) ?></div>
    </article>
    <article class="mgrid-stat-card">
      <div class="mgrid-stat-label" data-i18n="admin.dashboard.active_members">Wanachama active</div>
      <div class="mgrid-stat-value"><?= (int) ($usersStats['active_users'] ?? 0) ?></div>
    </article>
    <article class="mgrid-stat-card">
      <div class="mgrid-stat-label" data-i18n="admin.dashboard.pending_members">Wanachama pending</div>
      <div class="mgrid-stat-value"><?= (int) ($usersStats['pending_users'] ?? 0) ?></div>
    </article>
    <article class="mgrid-stat-card">
      <div class="mgrid-stat-label" data-i18n="admin.dashboard.rejected_members">Wanachama rejected</div>
      <div class="mgrid-stat-value"><?= (int) ($usersStats['rejected_users'] ?? 0) ?></div>
    </article>
  </div>
</section>

<section class="mgrid-page-section">
  <div class="mgrid-grid-3">
    <article class="mgrid-card">
      <div class="mgrid-card-body">
        <div class="mgrid-stat-label" data-i18n="admin.dashboard.docs_total">Nyaraka zote</div>
        <div class="mgrid-stat-value"><?= (int) ($docStats['total_documents'] ?? 0) ?></div>
      </div>
    </article>
    <article class="mgrid-card">
      <div class="mgrid-card-body">
        <div class="mgrid-stat-label" data-i18n="admin.dashboard.docs_pending">Nyaraka pending</div>
        <div class="mgrid-stat-value"><?= (int) ($docStats['pending_documents'] ?? 0) ?></div>
      </div>
    </article>
    <article class="mgrid-card">
      <div class="mgrid-card-body">
        <div class="mgrid-stat-label" data-i18n="admin.dashboard.funding_pending">Maombi ya M-Fund pending</div>
        <div class="mgrid-stat-value"><?= (int) ($fundingStats['pending_funding'] ?? 0) ?></div>
      </div>
    </article>
  </div>
</section>

<section class="mgrid-page-section">
  <div class="mgrid-card">
    <div class="mgrid-card-header">
      <h2 class="mgrid-card-title mb-0" data-i18n="admin.dashboard.quick_actions">Vitendo vya haraka</h2>
    </div>
    <div class="mgrid-card-body d-flex flex-wrap gap-2">
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('admin/users.php')) ?>" data-i18n="admin.dashboard.qa_members">Fungua wanachama</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('admin/admin_documents.php')) ?>" data-i18n="admin.dashboard.qa_documents">Kagua nyaraka</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('admin/admin_funding_applications.php')) ?>" data-i18n="admin.dashboard.qa_funding">Kagua M-Fund</a>
      <a class="btn-mgrid btn-mgrid-ghost" href="<?= e(url('admin/admin_reports.php')) ?>" data-i18n="admin.dashboard.qa_reports">Angalia ripoti</a>
    </div>
  </div>
</section>

<section class="mgrid-page-section">
  <div class="row g-3">
    <div class="col-xl-7">
      <div class="mgrid-card h-100">
        <div class="mgrid-card-header">
          <h2 class="mgrid-card-title mb-0" data-i18n="admin.dashboard.nida_review">NIDA zinazosubiri mapitio</h2>
        </div>
        <div class="mgrid-card-body p-0">
          <div class="table-responsive">
            <table class="mgrid-table mb-0">
              <thead>
                <tr>
                  <th data-i18n="admin.dashboard.table_mid">M-ID</th>
                  <th data-i18n="admin.dashboard.table_name">Jina</th>
                  <th data-i18n="admin.dashboard.table_date">Tarehe ya kupakia</th>
                  <th data-i18n="admin.dashboard.table_action">Hatua</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($pendingNida === []): ?>
                  <tr>
                    <td colspan="4" class="text-muted" data-i18n="admin.dashboard.no_pending_nida">Hakuna NIDA zinazongoja mapitio kwa sasa.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($pendingNida as $doc): ?>
                    <?php $name = trim((string) (($doc['first_name'] ?? '') . ' ' . ($doc['middle_name'] ?? '') . ' ' . ($doc['surname'] ?? ''))); ?>
                    <tr>
                      <td class="mgrid-table-mid-cell"><?= e((string) ($doc['m_id'] ?? '')) ?></td>
                      <td><?= e($name !== '' ? $name : 'Mwanachama') ?></td>
                      <td class="small text-muted"><?= e(substr((string) ($doc['uploaded_at'] ?? ''), 0, 16)) ?></td>
                      <td>
                        <a class="btn-mgrid btn-mgrid-outline btn-sm" href="<?= e(url('admin/review_document.php?id=' . (int) ($doc['id'] ?? 0))) ?>" data-i18n="admin.dashboard.review">Kagua</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-5">
      <div class="mgrid-card h-100">
        <div class="mgrid-card-header">
          <h2 class="mgrid-card-title mb-0" data-i18n="admin.dashboard.recent_activity">Shughuli za karibuni za admin</h2>
        </div>
        <div class="mgrid-card-body">
          <?php if ($recentLogs === []): ?>
            <p class="text-muted mb-0" data-i18n="admin.dashboard.no_logs">Bado hakuna shughuli za admin zilizorekodiwa.</p>
          <?php else: ?>
            <ul class="list-unstyled mb-0">
              <?php foreach ($recentLogs as $log): ?>
                <li class="mb-3 pb-3 border-bottom">
                  <div class="fw-semibold"><?= e((string) ($log['action'] ?? 'action')) ?></div>
                  <div class="small text-muted"><?= e((string) ($log['description'] ?? '')) ?></div>
                  <div class="small text-muted"><?= e(substr((string) ($log['created_at'] ?? ''), 0, 16)) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/shell_close.php'; ?>

