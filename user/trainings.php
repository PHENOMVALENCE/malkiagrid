<?php

declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';

$pdo = db();
$ready = trainings_module_ready($pdo);

$type = clean_string($_GET['type'] ?? '');
$format = clean_string($_GET['format'] ?? '');
$list = clean_string($_GET['listing'] ?? '');
$q = clean_string($_GET['q'] ?? '');

$types = ['course', 'workshop', 'webinar', 'cohort', 'mentorship', 'certification', 'other'];
$formats = ['physical', 'online', 'hybrid', 'unspecified'];
$list = in_array($list, ['active', 'past', ''], true) ? $list : '';

$rows = $ready ? trainings_list_for_public(
    $pdo,
    $type !== '' ? $type : null,
    $format !== '' ? $format : null,
    $list !== '' ? $list : null,
    $q !== '' ? $q : null
) : [];

$mgrid_page_title = mgrid_title('title.trainings');
require __DIR__ . '/includes/shell_open.php';
?>

<?php if (!$ready): ?>
  <div class="mgrid-alert mgrid-alert-danger">
    <span data-i18n="user.module_missing_sql">Import the database SQL to enable this module.</span>
    <div class="small mt-1"><code>database/m_grid_opportunities_training.sql</code></div>
  </div>
<?php else: ?>
  <div class="mgrid-card mb-3">
    <div class="mgrid-card-body d-flex flex-wrap justify-content-between gap-2">
      <div>
        <h1 class="mgrid-display mb-1" style="font-size:1.75rem;" data-i18n="user.trainings_title">Trainings &amp; workshops</h1>
        <p class="text-muted mb-0" data-i18n="user.trainings_sub">Capacity-building sessions — online or in person.</p>
      </div>
      <a class="btn-mgrid btn-mgrid-outline align-self-center" href="<?= e(url('user/my_trainings.php')) ?>" data-i18n="user.my_registrations">My registrations</a>
    </div>
  </div>

  <form method="get" class="mgrid-card mb-3 p-3">
    <div class="row g-2">
      <div class="col-md-3">
        <label class="form-label small text-muted" data-i18n="user.filter_type">Type</label>
        <select name="type" class="mgrid-form-control">
          <option value="" data-i18n="user.opt_all">All</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e(ucfirst($t)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small text-muted" data-i18n="user.filter_format">Format</label>
        <select name="format" class="mgrid-form-control">
          <option value="" data-i18n="user.opt_any">Any</option>
          <?php foreach ($formats as $f): ?>
            <option value="<?= e($f) ?>" <?= $format === $f ? 'selected' : '' ?>><?= e(ucfirst($f)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small text-muted" data-i18n="user.filter_schedule">Schedule</label>
        <select name="listing" class="mgrid-form-control">
          <option value="" <?= $list === '' ? 'selected' : '' ?> data-i18n="user.trainings_schedule_all">All upcoming/past</option>
          <option value="active" <?= $list === 'active' ? 'selected' : '' ?> data-i18n="user.trainings_schedule_active">Upcoming / ongoing</option>
          <option value="past" <?= $list === 'past' ? 'selected' : '' ?> data-i18n="user.trainings_schedule_past">Past</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small text-muted" data-i18n="user.filter_search">Search</label>
        <input type="search" name="q" class="mgrid-form-control" value="<?= e($q) ?>">
      </div>
      <div class="col-12"><button type="submit" class="btn-mgrid btn-mgrid-primary" data-i18n="user.filter_btn">Filter</button></div>
    </div>
  </form>

  <div class="mb-2"><a href="<?= e(url('user/opportunities.php')) ?>" class="small" data-i18n="user.back_opportunities">← Browse opportunities</a></div>

  <div class="row g-3">
    <?php if ($rows === []): ?>
      <div class="col-12 text-muted" data-i18n="user.empty_no_programmes">No programmes match your filters.</div>
    <?php endif; ?>
    <?php foreach ($rows as $p): ?>
      <?php $st = ot_training_listing_state($p); ?>
      <div class="col-md-6 col-lg-4">
        <div class="mgrid-card h-100">
          <div class="mgrid-card-body d-flex flex-column">
            <span class="badge text-bg-info w-fit mb-2"><?= e(ucfirst((string) $p['training_type'])) ?></span>
            <h2 class="h5"><?= e((string) $p['title']) ?></h2>
            <div class="small text-muted mb-2"><?= e((string) $p['provider_name']) ?></div>
            <?php $prev = strip_tags((string) $p['description']); if (strlen($prev) > 140) { $prev = substr($prev, 0, 137) . '…'; } ?>
            <p class="small flex-grow-1"><?= e($prev) ?></p>
            <div class="small mb-2">
              <div><strong data-i18n="user.label_when">When</strong>: <?= $p['schedule_start'] ? e(substr((string) $p['schedule_start'], 0, 16)) : 'TBD' ?></div>
              <div><strong data-i18n="user.label_format">Format</strong>: <?= e(ucfirst((string) $p['format'])) ?></div>
              <span class="badge text-bg-<?= $st === 'active' ? 'success' : 'secondary' ?>"><?= e(ucfirst($st)) ?></span>
            </div>
            <a class="btn-mgrid btn-mgrid-primary mt-auto" href="<?= e(url('user/training_detail.php?id=' . (int) $p['id'])) ?>" data-i18n="user.btn_details">Details</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/shell_close.php'; ?>
