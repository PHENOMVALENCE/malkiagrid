<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/functions.php';

function user_login_redirect_path(string $status): string
{
    if ($status === 'active') {
        return url('user/dashboard.php');
    }

    return url('pending-verification.php');
}

function admin_login_redirect_path(): string
{
    return url('admin/dashboard.php');
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_SESSION['admin_id'])) {
    $admin = current_admin();
    if (is_array($admin) && (string) ($admin['status'] ?? '') === 'active') {
        redirect(admin_login_redirect_path());
    }

    logout_all();
    flash_error('Kikao cha msimamizi si halali. Tafadhali ingia tena.');
    redirect(url('login.php'));
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'user' && isset($_SESSION['user_id'])) {
    $user = current_user();
    if (is_array($user)) {
        $status = (string) ($user['status'] ?? 'pending');
        if ($status === 'suspended') {
            logout_all();
            flash_error('Akaunti yako imesimamishwa. Wasiliana na msimamizi.');
            redirect(url('login.php'));
        }

        redirect(user_login_redirect_path($status));
    }

    logout_all();
    flash_error('Kikao cha mwanachama si halali. Tafadhali ingia tena.');
    redirect(url('login.php'));
}

$errors = [];
$activeRole = 'user';
$userLogin = '';
$adminEmail = '';
$flashErrors = flash_get('error');

if (is_post()) {
    require_csrf();

    $activeRole = (string) ($_POST['login_role'] ?? 'user');

    if ($activeRole === 'admin') {
        $adminEmail = trim((string) ($_POST['admin_email'] ?? ''));
        $adminPassword = (string) ($_POST['admin_password'] ?? '');

        if ($adminEmail === '' || $adminPassword === '') {
            $errors[] = 'Weka barua pepe na nenosiri la msimamizi.';
        } else {
            $result = authenticate_admin($adminEmail, $adminPassword);
            if (($result['ok'] ?? false) === true) {
                redirect(admin_login_redirect_path());
            }

            $reason = (string) ($result['reason'] ?? 'invalid_credentials');
            if ($reason === 'throttled') {
                $errors[] = 'Jaribio nyingi sana. Subiri kidogo ujaribu tena.';
            } elseif ($reason === 'disabled') {
                $errors[] = 'Ufikivu umekataliwa. Akaunti ya msimamizi imezuiwa.';
            } else {
                $errors[] = 'Taarifa za kuingia si sahihi.';
            }
        }
    } else {
        $userLogin = trim((string) ($_POST['user_login'] ?? ''));
        $userPassword = (string) ($_POST['user_password'] ?? '');

        if ($userLogin === '' || $userPassword === '') {
            $errors[] = 'Weka M-ID, simu au barua pepe pamoja na nenosiri.';
        } else {
            $result = authenticate_user($userLogin, $userPassword);
            if (($result['ok'] ?? false) === true) {
                $status = (string) ($result['status'] ?? 'pending');
                redirect(user_login_redirect_path($status));
            }

            $reason = (string) ($result['reason'] ?? 'invalid_credentials');
            if ($reason === 'throttled') {
                $errors[] = 'Jaribio nyingi sana. Subiri kidogo ujaribu tena.';
            } elseif ($reason === 'suspended') {
                $errors[] = 'Akaunti yako imesimamishwa. Wasiliana na msimamizi.';
            } else {
                $errors[] = 'Taarifa za kuingia si sahihi.';
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
    <title>Ingia — M-Grid</title>
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
          <p class="lead text-white opacity-90 mgrid-register-lead">Njia yako ya fursa huanza na M-ID moja.</p>
        </div>
        <div class="mgrid-mid-card text-dark mt-4">
          <div class="small text-muted text-uppercase">Kadi ya mfano</div>
          <div class="mgrid-mid-card-mono">M-2026-004821</div>
          <div class="fw-semibold mt-2">Amina Hassan</div>
          <div class="small text-muted">Dar es Salaam · Gold</div>
        </div>
        <p class="small mb-0">
          <a class="text-white text-decoration-underline" href="register.php">Pata M-ID yako</a>
        </p>
      </div>

      <div class="col-lg-7 d-flex align-items-center justify-content-center p-4 p-lg-5">
        <div class="w-100 mgrid-max-w-460">
          <p class="mb-2">
            <a class="small text-decoration-none" href="index.php">&larr; Rudi ukurasa mkuu</a>
          </p>
          <h1 class="h2 mgrid-section-heading mb-1">Karibu tena, Malkia</h1>
          <p class="text-muted small mb-4">Ingia ili kuendelea kwenye wasifu wako uliothibitishwa.</p>

          <?php foreach ($flashErrors as $error): ?>
            <div class="alert alert-danger border-0 mb-2"><?= e((string) $error) ?></div>
          <?php endforeach; ?>
          <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger border-0 mb-2"><?= e($error) ?></div>
          <?php endforeach; ?>

          <form method="post" novalidate>
            <?= csrf_input() ?>
            <div class="btn-group w-100 mb-4" role="group" aria-label="Login role">
              <input type="radio" class="btn-check" name="login_role" id="roleUser" value="user" autocomplete="off" <?= $activeRole !== 'admin' ? 'checked' : '' ?> />
              <label class="btn btn-outline-primary" for="roleUser">Mwanachama</label>
              <input type="radio" class="btn-check" name="login_role" id="roleAdmin" value="admin" autocomplete="off" <?= $activeRole === 'admin' ? 'checked' : '' ?> />
              <label class="btn btn-outline-primary" for="roleAdmin">Msimamizi</label>
            </div>

            <div id="userFields" class="<?= $activeRole === 'admin' ? 'd-none' : '' ?>">
              <div class="mb-3">
                <label class="form-label" for="userLogin">M-ID, namba ya simu au barua pepe</label>
                <input type="text" class="form-control" id="userLogin" name="user_login" autocomplete="username" value="<?= e($userLogin) ?>" />
              </div>
              <div class="mb-3">
                <label class="form-label" for="userPassword">Nenosiri</label>
                <input type="password" class="form-control" id="userPassword" name="user_password" autocomplete="current-password" />
              </div>
            </div>

            <div id="adminFields" class="<?= $activeRole === 'admin' ? '' : 'd-none' ?>">
              <div class="mb-3">
                <label class="form-label" for="adminEmail">Barua pepe ya msimamizi</label>
                <input type="email" class="form-control" id="adminEmail" name="admin_email" autocomplete="username" value="<?= e($adminEmail) ?>" />
              </div>
              <div class="mb-3">
                <label class="form-label" for="adminPassword">Nenosiri la msimamizi</label>
                <input type="password" class="form-control" id="adminPassword" name="admin_password" autocomplete="current-password" />
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">Ingia</button>
            <p class="text-center small text-muted mb-0">
              Mpya hapa? <a href="register.php">Pata M-ID yako</a>
            </p>
          </form>
        </div>
      </div>
    </div>

    <script>
      (function () {
        const roleUser = document.getElementById('roleUser');
        const roleAdmin = document.getElementById('roleAdmin');
        const userFields = document.getElementById('userFields');
        const adminFields = document.getElementById('adminFields');

        function toggle() {
          const isAdmin = roleAdmin.checked;
          userFields.classList.toggle('d-none', isAdmin);
          adminFields.classList.toggle('d-none', !isAdmin);
        }

        roleUser.addEventListener('change', toggle);
        roleAdmin.addEventListener('change', toggle);
      })();
    </script>
  </body>
</html>

