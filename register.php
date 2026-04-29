<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/m_id_generator.php';
require_once __DIR__ . '/includes/registration_helper.php';

$form = [
    'first_name' => '',
    'middle_name' => '',
    'surname' => '',
    'nida_number' => '',
    'phone' => '',
    'email' => '',
    'has_registered_business' => 'no',
    'business_name' => '',
    'business_type' => '',
    'has_bank_account' => 'no',
    'heard_about' => '',
];

$errors = [];

if (is_post()) {
    require_csrf();

    [$validated, $errors] = validate_registration_payload($_POST);
    $form = array_merge($form, $validated);

    if ($errors === []) {
        $pdo = db();
        $errors = registration_unique_checks($pdo, $validated);
    }

    if ($errors === []) {
        $pdo = db();

        try {
            $pdo->beginTransaction();

            $mId = generate_next_m_id($pdo);
            $passwordHash = password_hash($validated['password'], PASSWORD_DEFAULT);

            $insertUser = $pdo->prepare(
                'INSERT INTO users
                (m_id, first_name, middle_name, surname, nida_number, phone, email, password_hash, status, preferred_language)
                VALUES
                (:m_id, :first_name, :middle_name, :surname, :nida_number, :phone, :email, :password_hash, :status, :preferred_language)'
            );
            $insertUser->execute([
                ':m_id' => $mId,
                ':first_name' => $validated['first_name'],
                ':middle_name' => $validated['middle_name'],
                ':surname' => $validated['surname'],
                ':nida_number' => $validated['nida_number'],
                ':phone' => $validated['phone'],
                ':email' => $validated['email'] !== '' ? strtolower($validated['email']) : null,
                ':password_hash' => $passwordHash,
                ':status' => 'pending',
                ':preferred_language' => 'sw',
            ]);

            $userId = (int) $pdo->lastInsertId();

            $insertProfile = $pdo->prepare(
                'INSERT INTO user_profiles
                (user_id, has_registered_business, business_name, business_type, has_bank_account, heard_about)
                VALUES
                (:user_id, :has_registered_business, :business_name, :business_type, :has_bank_account, :heard_about)'
            );
            $insertProfile->execute([
                ':user_id' => $userId,
                ':has_registered_business' => $validated['has_registered_business'],
                ':business_name' => $validated['business_name'] !== '' ? $validated['business_name'] : null,
                ':business_type' => $validated['business_type'] !== '' ? $validated['business_type'] : null,
                ':has_bank_account' => $validated['has_bank_account'],
                ':heard_about' => $validated['heard_about'],
            ]);

            initialize_mscore_current_scores($pdo, $userId);

            $pdo->commit();

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_m_id'] = $mId;
            $_SESSION['role'] = 'user';

            flash_success('Usajili umefanikiwa.');
            redirect('pending-verification.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Hitilafu imetokea wakati wa usajili. Tafadhali jaribu tena.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw" data-mgrid-default-lang="sw">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Fungua M-ID yako — M-Grid</title>
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
  <body class="bg-white mgrid-auth-page">
    <div class="row g-0 min-vh-100 mgrid-auth-layout">
      <div class="d-none d-lg-flex flex-column justify-content-between col-lg-5 mgrid-register-left p-5">
        <div>
          <div class="mgrid-logo-text-light mb-4">M·GRID</div>
          <p class="lead text-white opacity-90 mgrid-register-lead">Njia yako ya fursa huanza na M-ID moja.</p>
        </div>
        <div class="mgrid-mid-card text-dark mt-4">
          <div class="small text-muted text-uppercase">Kadi ya mfano</div>
          <div class="mgrid-mid-card-mono">M-2026-000104</div>
          <div class="fw-semibold mt-2">Neema Joseph</div>
          <div class="small text-muted">Mbeya · Gold</div>
        </div>
        <p class="small mb-0">
          <a class="text-white text-decoration-underline" href="login.php">Tayari una M-ID? Ingia</a>
        </p>
      </div>

      <div class="col-12 col-lg-7 d-flex flex-column justify-content-center px-3 py-4 p-lg-5">
        <div class="d-lg-none mgrid-auth-mobile-hero mb-3">
          <div class="mgrid-auth-mobile-hero__brand">M·GRID</div>
          <p class="mgrid-auth-mobile-hero__lead mb-0">Njia yako ya fursa huanza na M-ID moja.</p>
        </div>
        <div class="w-100 mgrid-max-w-560 mx-lg-0 mx-auto">
          <p class="mb-2">
            <a class="small text-decoration-none" href="index.php">&larr; Rudi ukurasa mkuu</a>
          </p>

          <h1 class="h2 mgrid-section-heading mb-1">Fungua M-ID yako</h1>
          <p class="text-muted small mb-4">Inachukua chini ya dakika 3</p>

          <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger border-0 mb-2"><?= e($error) ?></div>
          <?php endforeach; ?>

          <form id="registerForm" method="post" novalidate>
            <?= csrf_input() ?>

            <div id="regStep1">
              <div class="row g-2 mb-3">
                <div class="col-12 col-md-4">
                  <label class="form-label" for="firstName">Jina la kwanza</label>
                  <input type="text" class="form-control" id="firstName" name="first_name" required value="<?= e($form['first_name']) ?>" />
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="middleName">Jina la kati</label>
                  <input type="text" class="form-control" id="middleName" name="middle_name" value="<?= e($form['middle_name']) ?>" />
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label" for="surname">Ukoo</label>
                  <input type="text" class="form-control" id="surname" name="surname" required value="<?= e($form['surname']) ?>" />
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" for="nidaNumber">Namba ya NIDA (hakikisha namba ni sahihi)</label>
                <input type="text" class="form-control" id="nidaNumber" name="nida_number" value="<?= e($form['nida_number']) ?>" />
              </div>
              <div class="mb-3">
                <label class="form-label" for="phone">Namba ya simu</label>
                <input type="text" class="form-control" id="phone" name="phone" required value="<?= e($form['phone']) ?>" />
              </div>
              <div class="mb-3">
                <label class="form-label" for="email">Barua pepe (si lazima)</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= e($form['email']) ?>" />
              </div>
              <div class="mb-2">
                <label class="form-label" for="regPassword">Nenosiri</label>
                <input type="password" class="form-control" id="regPassword" name="password" required minlength="8" />
              </div>
              <div class="mb-3">
                <label class="form-label" for="regPassword2">Thibitisha nenosiri</label>
                <input type="password" class="form-control" id="regPassword2" name="confirm_password" required />
              </div>
              <button type="button" class="btn btn-primary w-100 py-2" id="regBtnNext">Endelea Hatua ya 2</button>
            </div>

            <div id="regStep2" class="d-none">
              <div class="mb-3">
                <span class="form-label d-block">Una biashara iliyosajiliwa?</span>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="form-check m-0 border rounded px-2 py-2 h-100">
                      <input class="form-check-input" type="radio" name="has_registered_business" id="bizY" value="yes" <?= $form['has_registered_business'] === 'yes' ? 'checked' : '' ?> />
                      <label class="form-check-label" for="bizY">Ndiyo</label>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="form-check m-0 border rounded px-2 py-2 h-100">
                      <input class="form-check-input" type="radio" name="has_registered_business" id="bizN" value="no" <?= $form['has_registered_business'] !== 'yes' ? 'checked' : '' ?> />
                      <label class="form-check-label" for="bizN">Hapana</label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" for="bizName">Jina la biashara</label>
                <input type="text" class="form-control" id="bizName" name="business_name" value="<?= e($form['business_name']) ?>" />
              </div>
              <div class="mb-3">
                <label class="form-label" for="bizType">Aina ya biashara</label>
                <select class="form-select" id="bizType" name="business_type">
                  <option value="">Chagua...</option>
                  <option value="sole_prop" <?= $form['business_type'] === 'sole_prop' ? 'selected' : '' ?>>Mmiliki mmoja</option>
                  <option value="company" <?= $form['business_type'] === 'company' ? 'selected' : '' ?>>Kampuni</option>
                  <option value="cooperative" <?= $form['business_type'] === 'cooperative' ? 'selected' : '' ?>>Ushirika</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="bank">Una akaunti ya benki?</label>
                <select class="form-select" id="bank" name="has_bank_account">
                  <option value="yes" <?= $form['has_bank_account'] === 'yes' ? 'selected' : '' ?>>Ndiyo</option>
                  <option value="no" <?= $form['has_bank_account'] !== 'yes' ? 'selected' : '' ?>>Hapana</option>
                </select>
              </div>
              <div class="mb-4">
                <label class="form-label" for="hear">Umesikiaje kuhusu M-Grid?</label>
                <select class="form-select" id="hear" name="heard_about">
                  <option value="">Chagua...</option>
                  <option value="clouds_media" <?= $form['heard_about'] === 'clouds_media' ? 'selected' : '' ?>>Clouds Media</option>
                  <option value="partner_referral" <?= $form['heard_about'] === 'partner_referral' ? 'selected' : '' ?>>Kupitia mshirika</option>
                  <option value="community_event" <?= $form['heard_about'] === 'community_event' ? 'selected' : '' ?>>Tukio la jamii</option>
                  <option value="other" <?= $form['heard_about'] === 'other' ? 'selected' : '' ?>>Nyingine</option>
                </select>
              </div>
              <div class="d-grid d-sm-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-sm-grow-1 py-2" id="regBtnBack">Rudi</button>
                <button type="submit" class="btn btn-primary flex-sm-grow-1 py-2">Tengeneza M-ID yangu</button>
              </div>
            </div>
          </form>
          <p class="text-center small text-muted mt-3 mb-0">
            Tayari una M-ID? <a href="login.php">Ingia</a>
          </p>
        </div>
      </div>
    </div>

    <script>
      (function () {
        const step1 = document.getElementById("regStep1");
        const step2 = document.getElementById("regStep2");
        const nextBtn = document.getElementById("regBtnNext");
        const backBtn = document.getElementById("regBtnBack");
        const regPassword = document.getElementById("regPassword");
        const regPassword2 = document.getElementById("regPassword2");

        if (!step1 || !step2 || !nextBtn || !backBtn) return;

        function applySwValidation(input) {
          if (!input) return;
          input.addEventListener("invalid", function () {
            if (input.validity.valueMissing) {
              input.setCustomValidity("Tafadhali jaza sehemu hii.");
            } else if (input.validity.tooShort) {
              input.setCustomValidity("Nenosiri liwe angalau herufi 8.");
            } else if (input.validity.typeMismatch && input.type === "email") {
              input.setCustomValidity("Weka barua pepe sahihi.");
            } else {
              input.setCustomValidity("Tafadhali hakikisha taarifa ni sahihi.");
            }
          });
          input.addEventListener("input", function () {
            input.setCustomValidity("");
          });
        }

        applySwValidation(regPassword);
        applySwValidation(regPassword2);

        nextBtn.addEventListener("click", function () {
          const requiredStep1 = step1.querySelectorAll("input[required]");
          for (const input of requiredStep1) {
            if (!input.checkValidity()) {
              input.reportValidity();
              return;
            }
          }
          step1.classList.add("d-none");
          step2.classList.remove("d-none");
        });

        backBtn.addEventListener("click", function () {
          step2.classList.add("d-none");
          step1.classList.remove("d-none");
        });
      })();
    </script>
  </body>
</html>
