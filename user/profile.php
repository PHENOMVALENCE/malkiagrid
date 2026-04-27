<?php
declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';

$pdo = db();
$auth = auth_user();
$uid = (int) ($auth['user_id'] ?? 0);
$errors = [];

$regions = [
    'Arusha', 'Dar es Salaam', 'Dodoma', 'Geita', 'Iringa', 'Kagera', 'Katavi', 'Kigoma', 'Kilimanjaro',
    'Lindi', 'Manyara', 'Mara', 'Mbeya', 'Mjini Magharibi', 'Morogoro', 'Mtwara', 'Mwanza', 'Njombe',
    'Pemba North', 'Pemba South', 'Pwani', 'Rukwa', 'Ruvuma', 'Shinyanga', 'Simiyu', 'Singida', 'Songwe',
    'Tabora', 'Tanga', 'Other / Diaspora',
];

$businessStatuses = ['employed', 'self_employed', 'student', 'homemaker', 'seeking', 'other'];

if (is_post()) {
    if (!csrf_verify((string) ($_POST['_csrf'] ?? $_POST['_token'] ?? ''))) {
        $errors[] = 'Token si sahihi. Jaribu tena.';
    } else {
        $region = clean_string($_POST['region'] ?? '');
        $businessStatus = clean_string($_POST['business_status'] ?? '');
        $preferredLanguage = clean_string($_POST['preferred_language'] ?? 'sw');
        $bio = clean_string($_POST['bio'] ?? '');

        if ($region === '') {
            $errors[] = 'Tafadhali chagua mkoa.';
        }
        if (!in_array($businessStatus, $businessStatuses, true)) {
            $errors[] = 'Hali ya biashara si sahihi.';
        }
        if (!in_array($preferredLanguage, ['sw', 'en'], true)) {
            $errors[] = 'Lugha uliyochagua si sahihi.';
        }
        if (mb_strlen($bio) > 500) {
            $errors[] = 'Maelezo ya wasifu yamezidi herufi 500.';
        }

        if ($errors === []) {
            $completion = 35;
            if ($region !== '') {
                $completion += 20;
            }
            if ($businessStatus !== '') {
                $completion += 20;
            }
            if ($bio !== '') {
                $completion += 25;
            }

            $upUser = $pdo->prepare('UPDATE users SET preferred_language = :lang WHERE id = :id LIMIT 1');
            $upUser->execute(['lang' => $preferredLanguage, 'id' => $uid]);

            $upProfile = $pdo->prepare('
                INSERT INTO user_profiles (user_id, region, business_status, bio, profile_completion)
                VALUES (:uid, :region, :business_status, :bio, :profile_completion)
                ON DUPLICATE KEY UPDATE
                    region = VALUES(region),
                    business_status = VALUES(business_status),
                    bio = VALUES(bio),
                    profile_completion = VALUES(profile_completion)
            ');
            $upProfile->execute([
                'uid' => $uid,
                'region' => $region,
                'business_status' => $businessStatus,
                'bio' => $bio !== '' ? $bio : null,
                'profile_completion' => $completion,
            ]);

            $_SESSION['preferred_language'] = $preferredLanguage;
            flash_set('success', 'Wasifu umehifadhiwa kikamilifu.');
            redirect(url('user/profile.php'));
        }
    }
}

$stmt = $pdo->prepare('
    SELECT u.id, u.m_id, u.first_name, u.middle_name, u.surname, u.email, u.phone, u.status, u.preferred_language, u.created_at,
           p.region, p.business_status, p.bio, p.profile_completion,
           s.score AS m_score, s.tier AS m_tier,
           (
             SELECT ud.status
             FROM user_documents ud
             INNER JOIN document_types dt ON dt.id = ud.document_type_id
             WHERE ud.user_id = u.id AND dt.slug = "national_id"
             ORDER BY ud.created_at DESC
             LIMIT 1
           ) AS nida_status,
           (
             SELECT ud.admin_notes
             FROM user_documents ud
             INNER JOIN document_types dt ON dt.id = ud.document_type_id
             WHERE ud.user_id = u.id AND dt.slug = "national_id"
             ORDER BY ud.created_at DESC
             LIMIT 1
           ) AS nida_notes
    FROM users u
    LEFT JOIN user_profiles p ON p.user_id = u.id
    LEFT JOIN mscore_current_scores s ON s.user_id = u.id
    WHERE u.id = :id
    LIMIT 1
');
$stmt->execute(['id' => $uid]);
$row = $stmt->fetch() ?: [];

$fullName = trim(implode(' ', array_filter([
    (string) ($row['first_name'] ?? ''),
    (string) ($row['middle_name'] ?? ''),
    (string) ($row['surname'] ?? ''),
])));

$mgrid_page_title = mgrid_title('title.profile');
require __DIR__ . '/includes/shell_open.php';
?>

<div class="mgrid-card mb-3">
  <div class="mgrid-card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
      <div class="mgrid-topbar-label">M-Profile</div>
      <h1 class="mgrid-display mb-1" style="font-size:2rem;"><?= e($fullName !== '' ? $fullName : 'Mwanachama') ?></h1>
      <p class="mb-1">M-ID: <strong><?= e((string) ($row['m_id'] ?? '—')) ?></strong></p>
      <p class="mb-0" style="color:var(--mgrid-ink-500);">Hali ya akaunti: <?= e((string) ($row['status'] ?? 'pending')) ?></p>
    </div>
    <a href="<?= e(url('user/my_mscore.php')) ?>" class="btn-mgrid btn-mgrid-outline">M-SCORE: <?= e((string) ($row['m_score'] ?? '—')) ?></a>
  </div>
</div>

<?php if ($msg = flash_get('success')): ?>
  <div class="alert alert-success"><?= e((string) $msg) ?></div>
<?php endif; ?>
<?php foreach ($errors as $err): ?>
  <div class="alert alert-danger"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="mgrid-stat-card">
      <div class="mgrid-stat-label">Barua pepe</div>
      <div class="mgrid-stat-value" style="font-size:1.05rem;"><?= e((string) ($row['email'] ?? '—')) ?></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="mgrid-stat-card">
      <div class="mgrid-stat-label">Simu</div>
      <div class="mgrid-stat-value" style="font-size:1.05rem;"><?= e((string) ($row['phone'] ?? '—')) ?></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="mgrid-stat-card">
      <div class="mgrid-stat-label">Hali ya NIDA</div>
      <div class="mgrid-stat-value" style="font-size:1.05rem;"><?= e((string) ($row['nida_status'] ?? 'not_submitted')) ?></div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="mgrid-stat-card">
      <div class="mgrid-stat-label">Profile completion</div>
      <div class="mgrid-stat-value"><?= (int) ($row['profile_completion'] ?? 0) ?>%</div>
    </div>
  </div>
</div>

<div class="mgrid-card">
  <div class="mgrid-card-header">
    <h2 class="mgrid-card-title"><i class="ti ti-edit"></i> Hariri maelezo ya wasifu</h2>
  </div>
  <div class="mgrid-card-body">
    <form method="post" class="row g-3" novalidate>
      <?= csrf_field() ?>
      <div class="col-md-6">
        <label class="form-label" for="region">Mkoa</label>
        <select class="form-select" id="region" name="region" required>
          <option value="">Chagua...</option>
          <?php foreach ($regions as $regionName): ?>
            <option value="<?= e($regionName) ?>" <?= (string) ($row['region'] ?? '') === $regionName ? 'selected' : '' ?>><?= e($regionName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="business_status">Hali ya biashara</label>
        <select class="form-select" id="business_status" name="business_status" required>
          <option value="">Chagua...</option>
          <?php foreach ($businessStatuses as $status): ?>
            <option value="<?= e($status) ?>" <?= (string) ($row['business_status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="preferred_language">Lugha ya mfumo</label>
        <select class="form-select" id="preferred_language" name="preferred_language">
          <option value="sw" <?= (string) ($row['preferred_language'] ?? 'sw') === 'sw' ? 'selected' : '' ?>>Kiswahili</option>
          <option value="en" <?= (string) ($row['preferred_language'] ?? 'sw') === 'en' ? 'selected' : '' ?>>English</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label" for="bio">Wasifu mfupi</label>
        <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="500"><?= e((string) ($row['bio'] ?? '')) ?></textarea>
      </div>
      <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn-mgrid btn-mgrid-primary px-4">Hifadhi mabadiliko</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/shell_close.php'; ?>
