<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/guards/user_guard.php';
require_once __DIR__ . '/includes/document_helpers.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

$user = current_user();
if (!$user) {
    redirect('login.php');
}

$status = (string) ($user['status'] ?? 'pending');
if ($status === 'active') {
    redirect('user/dashboard.php');
}

$fullName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['surname'] ?? '')));
if ($fullName === '') {
    $fullName = (string) ($user['full_name'] ?? 'Mwanachama');
}
$userMid = (string) ($user['m_id'] ?? '—');

$pdo = db();
$errors = [];
$success = null;

$docTypeId = document_type_id_by_code($pdo, 'nida');
if ($docTypeId === null) {
    $errors[] = 'Aina ya nyaraka ya NIDA haijapatikana.';
}

if (is_post() && $docTypeId !== null) {
    require_csrf();

    if (!isset($_FILES['nidaPhoto'])) {
        $errors[] = 'Tafadhali chagua picha ya NIDA.';
    } else {
        $file = $_FILES['nidaPhoto'];
        $maxMb = upload_max_size_mb($pdo);
        [$ok, $mimeOrMessage, $ext] = validate_nida_upload($file, $maxMb);

        if (!$ok) {
            $errors[] = (string) $mimeOrMessage;
        } else {
            try {
                $stored = store_nida_file($file, (int) $user['id'], (string) $ext);

                $pdo->beginTransaction();
                create_nida_document(
                    $pdo,
                    (int) $user['id'],
                    $docTypeId,
                    (string) $mimeOrMessage,
                    (int) $file['size'],
                    $stored['relative_path'],
                    (string) ($file['name'] ?? 'nida')
                );
                $pdo->commit();

                $success = 'Uwasilishaji umepokelewa. Inasubiri mapitio ya msimamizi.';
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Imeshindikana kuhifadhi nyaraka. Jaribu tena.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw" data-mgrid-default-lang="sw">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Uhakiki unaendelea - M-Grid</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap"
      rel="stylesheet"
    />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/mgrid-variables.css" rel="stylesheet" />
    <link href="assets/css/mgrid-overrides.css" rel="stylesheet" />
    <link href="assets/css/mgrid-components.css" rel="stylesheet" />
    <link href="assets/css/mgrid-animations.css" rel="stylesheet" />
    <link href="assets/css/mgrid-reference-theme.css" rel="stylesheet" />
  </head>
  <body class="bg-white">
    <div class="min-vh-100 d-lg-flex">
      <div class="d-none d-lg-flex flex-column justify-content-between col-lg-5 mgrid-register-left p-5">
        <div>
          <div class="mgrid-logo-text-light mb-4">M·GRID</div>
          <p class="lead text-white opacity-90 mgrid-register-lead">Akaunti yako ipo salama. Tunahitaji uhakiki wa NIDA kabla ya kuendelea.</p>
        </div>
        <div class="mgrid-mid-card text-dark mt-4">
          <div class="small text-muted text-uppercase">Hali ya akaunti</div>
          <div class="mgrid-mid-card-mono">INASUBIRI UHAKIKI</div>
          <div class="fw-semibold mt-2">Mwanachama mpya</div>
          <div class="small text-muted">Tutakutaarifu baada ya mapitio</div>
        </div>
        <p class="small mb-0 text-white-50">Hakiki kawaida huchukua saa chache hadi siku 1 ya kazi.</p>
      </div>

      <div class="col-lg-7 d-flex align-items-center justify-content-center p-4 p-lg-5">
        <div class="w-100 mgrid-max-w-560">
          <p class="mb-2"><a class="small text-decoration-none" href="index.php">&larr; Rudi ukurasa mkuu</a></p>
          <div class="d-flex justify-content-end mb-3">
            <div class="btn-group btn-group-sm mgrid-lang-toggle" role="group" aria-label="Lugha">
              <input type="radio" class="btn-check" name="mgridLang" id="mgridLangEn" value="en" autocomplete="off" />
              <label class="btn btn-sm btn-outline-secondary" for="mgridLangEn">EN</label>
              <input type="radio" class="btn-check" name="mgridLang" id="mgridLangSw" value="sw" autocomplete="off" />
              <label class="btn btn-sm btn-outline-secondary" for="mgridLangSw">SW</label>
            </div>
          </div>

          <div class="card border-0 shadow-lg mgrid-modal-rounded p-4 p-md-5">
            <p class="small text-uppercase text-muted mb-2">Uhakiki wa Kitambulisho</p>
            <h1 class="h2 mgrid-section-heading mb-2">Akaunti yako inasubiri uthibitisho</h1>
            <p class="text-muted mb-4">Tafadhali pakia picha inayoonekana ya Kitambulisho cha Taifa (NIDA). Mpaka ithibitishwe, utaendelea kubaki kwenye ukurasa huu.</p>

            <?php if ($success): ?>
              <div class="alert alert-success border-0 mb-3"><?= e($success) ?></div>
            <?php endif; ?>
            <?php foreach ($errors as $error): ?>
              <div class="alert alert-danger border-0 mb-2"><?= e($error) ?></div>
            <?php endforeach; ?>

            <div class="alert alert-warning border-0 mb-4" id="verifyStatusBox">
              Hali ya sasa: <strong id="verifyStatusText"><?= e($status === 'rejected' ? 'Ilikataliwa, pakia upya' : 'Inasubiri kupakiwa kwa NIDA') ?></strong>
            </div>

            <div class="card border-0 bg-light mb-4">
              <div class="card-body py-3">
                <div class="small text-uppercase text-muted mb-2">Maelezo ya akaunti</div>
                <div class="small"><strong>Jina kamili:</strong> <?= e($fullName) ?></div>
                <div class="small"><strong>M-ID:</strong> <?= e($userMid) ?></div>
              </div>
            </div>

            <form id="verifyIdForm" method="post" enctype="multipart/form-data" novalidate>
              <?= csrf_input() ?>
              <div class="mb-3">
                <label class="form-label" for="nidaPhoto">Picha ya NIDA (mbele au upande wenye taarifa)</label>
                <input class="form-control" type="file" id="nidaPhoto" name="nidaPhoto" accept=".jpg,.jpeg,.png,.webp" required />
                <div class="form-text">Tumia picha iliyo wazi, isiyokatika, na maandishi yasomeke.</div>
              </div>
              <button type="submit" class="btn btn-primary w-100 mb-2">Wasilisha kwa uhakiki</button>
              <a class="btn btn-outline-danger w-100 mb-2" href="logout.php">Toka kwenye akaunti</a>
              <a class="btn btn-outline-secondary w-100" href="login.php">Rudi ukurasa wa kuingia</a>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div id="mgridToastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/mgrid-i18n.js"></script>
    <script src="assets/js/mgrid-core.js"></script>
  </body>
</html>

