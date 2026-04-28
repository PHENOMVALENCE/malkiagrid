<?php

declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';

$pdo = db();
$ready = opportunities_module_ready($pdo);

$type = clean_string($_GET['type'] ?? '');
$cat = (int) ($_GET['category'] ?? 0);
$df = clean_string($_GET['deadline_from'] ?? '');
$dt = clean_string($_GET['deadline_to'] ?? '');
$list = clean_string($_GET['listing'] ?? '');
$q = clean_string($_GET['q'] ?? '');

$types = ['grant', 'job', 'internship', 'fellowship', 'accelerator', 'tender', 'webinar', 'workshop', 'training_program', 'other'];
$list = in_array($list, ['active', 'expired', ''], true) ? $list : '';

$rows = $ready ? opportunities_list_for_public(
    $pdo,
    $type !== '' ? $type : null,
    $cat > 0 ? $cat : null,
    $df,
    $dt,
    $list !== '' ? $list : null,
    $q !== '' ? $q : null
) : [];

$cats = $ready
    ? ($pdo->query('SELECT id, COALESCE(name_sw, name_en) AS name FROM opportunity_categories WHERE is_active = 1 ORDER BY name_sw ASC, id ASC')->fetchAll() ?: [])
    : [];

$mgrid_page_title = mgrid_title('title.opportunities');
require __DIR__ . '/includes/shell_open.php';
?>

<?php if (!$ready): ?>
  <div class="mgrid-alert mgrid-alert-danger">
    <span data-i18n="user.module_missing_sql">Import the database SQL to enable this module.</span>
    <div class="small mt-1"><code>database/m_grid_opportunities_training.sql</code></div>
  </div>
<?php else: ?>
  <div class="mgrid-card mb-3">
    <div class="mgrid-card-body">
      <h1 class="mgrid-display mb-2" style="font-size:1.75rem;" data-i18n="user.opps_title">Growth opportunities</h1>
      <p class="text-muted mb-0" data-i18n="user.opps_sub">Grants, roles, tenders, and programmes — filter by type, category, and deadline.</p>
    </div>
  </div>

  <form method="get" class="mgrid-card mb-3 p-3">
    <div class="row g-2">
      <div class="col-md-3">
        <label class="form-label small text-muted" data-i18n="user.filter_type">Type</label>
        <select name="type" class="mgrid-form-control">
          <option value="" data-i18n="user.opps_opt_all_types">All types</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $t))) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small text-muted" data-i18n="user.filter_category">Category</label>
        <select name="category" class="mgrid-form-control">
          <option value="0" data-i18n="user.opps_opt_all_cats">All</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= $cat === (int) $c['id'] ? 'selected' : '' ?>><?= e((string) $c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small text-muted" data-i18n="user.filter_deadline_from">Deadline from</label>
        <input type="date" name="deadline_from" class="mgrid-form-control" value="<?= e($df) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small text-muted" data-i18n="user.filter_deadline_to">Deadline to</label>
        <input type="date" name="deadline_to" class="mgrid-form-control" value="<?= e($dt) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small text-muted" data-i18n="user.filter_status">Status</label>
        <select name="listing" class="mgrid-form-control">
          <option value="" <?= $list === '' ? 'selected' : '' ?> data-i18n="user.opps_status_all">All (open listing)</option>
          <option value="active" <?= $list === 'active' ? 'selected' : '' ?> data-i18n="user.opps_status_active">Active only</option>
          <option value="expired" <?= $list === 'expired' ? 'selected' : '' ?> data-i18n="user.opps_status_expired">Expired</option>
        </select>
      </div>
      <div class="col-md-8">
        <label class="form-label small text-muted" data-i18n="user.filter_keyword">Keyword</label>
        <input type="search" name="q" class="mgrid-form-control" data-i18n-placeholder="user.opps_keyword_ph" placeholder="Search title, provider, description" value="<?= e($q) ?>">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn-mgrid btn-mgrid-primary w-100" data-i18n="user.opps_apply_filters">Apply filters</button>
      </div>
    </div>
  </form>

  <div class="d-flex justify-content-between mb-2">
    <a class="btn-mgrid btn-mgrid-outline btn-sm" href="<?= e(url('user/my_opportunities.php')) ?>" data-i18n="user.my_applications">My applications</a>
    <a class="btn-mgrid btn-mgrid-outline btn-sm" href="<?= e(url('user/trainings.php')) ?>" data-i18n="user.nav_trainings">Trainings</a>
  </div>

  <div class="row g-3">
    <?php if ($rows === []): ?>
      <div class="col-12 text-muted" data-i18n="user.empty_no_listings">No listings match your filters.</div>
    <?php endif; ?>
    <?php foreach ($rows as $o): ?>
      <?php $state = ot_opportunity_listing_state($o); ?>
      <div class="col-md-6 col-lg-4">
        <div class="mgrid-card h-100">
          <div class="mgrid-card-body d-flex flex-column">
            <div class="d-flex justify-content-between mb-2">
              <span class="badge text-bg-primary"><?= e(ucwords(str_replace('_', ' ', (string) $o['opportunity_type']))) ?></span>
              <span class="badge text-bg-<?= $state === 'active' ? 'success' : ($state === 'expired' ? 'secondary' : 'dark') ?>"><?= e(ucfirst($state)) ?></span>
            </div>
            <div class="small text-muted"><?= e((string) $o['category_name']) ?> · <?= e((string) $o['provider_name']) ?></div>
            <h2 class="h5 mt-1"><?= e((string) $o['title']) ?></h2>
            <?php $prev = strip_tags((string) $o['description']); if (strlen($prev) > 160) { $prev = substr($prev, 0, 157) . '…'; } ?>
            <p class="small flex-grow-1" style="color:var(--mgrid-ink-500);"><?= e($prev) ?></p>
            <div class="small text-muted mb-2">
              <?php if (!empty($o['deadline'])): ?><div><strong data-i18n="user.label_deadline">Deadline</strong>: <?= e((string) $o['deadline']) ?></div><?php endif; ?>
              <div><strong data-i18n="user.label_format">Format</strong>: <?= e(ucfirst((string) $o['format'])) ?></div>
            </div>
            <a class="btn-mgrid btn-mgrid-primary mt-auto" href="<?= e(url('user/opportunity_detail.php?id=' . (int) $o['id'])) ?>" data-i18n="user.btn_view">View</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/shell_close.php'; ?>
