<?php
declare(strict_types=1);

require __DIR__ . '/includes/init_member.php';
require_once __DIR__ . '/../includes/document_helpers.php';

$pdo = db();
$auth = auth_user();
$uid = (int) ($auth['user_id'] ?? 0);
$errors = [];
$maxBytes = 5 * 1024 * 1024;
$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

$docTypeStmt = $pdo->query("SELECT id FROM document_types WHERE code = 'nida' LIMIT 1");
$docTypeId = (int) ($docTypeStmt->fetchColumn() ?: 0);
if ($docTypeId <= 0) {
    http_response_code(500);
    exit('Document type national_id haijasanidiwa.');
}

if (is_post()) {
    if (!csrf_verify((string) ($_POST['_csrf'] ?? $_POST['_token'] ?? ''))) {
        $errors[] = 'Token si sahihi. Jaribu tena.';
    } elseif (!isset($_FILES['national_id_photo']) || !is_array($_FILES['national_id_photo'])) {
        $errors[] = 'Chagua picha ya NIDA kwanza.';
    } else {
        $file = $_FILES['national_id_photo'];
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $tmp = (string) ($file['tmp_name'] ?? '');
        $name = (string) ($file['name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = 'Kupakia picha kumeshindikana.';
        } elseif ($size <= 0 || $size > $maxBytes) {
            $errors[] = 'Ukubwa wa picha uzidi 0 na usizidi 5MB.';
        } elseif (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Tumia JPG, PNG au WEBP.';
        } else {
            $uploadDir = MGRID_ROOT . '/uploads/documents/national_ids';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $newName = sprintf('nid_%d_%s.%s', $uid, bin2hex(random_bytes(8)), $ext);
            $absolutePath = $uploadDir . '/' . $newName;
            $relativePath = 'uploads/documents/national_ids/' . $newName;

            if (!move_uploaded_file($tmp, $absolutePath)) {
                $errors[] = 'Imeshindikana kuhifadhi faili.';
            } else {
                try {
                    $pdo->beginTransaction();

                    $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';
                    create_nida_document($pdo, $uid, $docTypeId, $mime, (int) filesize($absolutePath), $relativePath, $name);

                    $pdo->prepare('UPDATE users SET status = "pending" WHERE id = :uid')->execute(['uid' => $uid]);
                    $pdo->commit();

                    flash_set('success', 'Picha ya NIDA imepakiwa. Inasubiri uhakiki wa admin.');
                    redirect(url('user/verify-id.php'));
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $errors[] = 'Hitilafu imetokea wakati wa kuhifadhi NIDA.';
                }
            }
        }
    }
}

$profileStmt = $pdo->prepare('
    SELECT u.status, d.status AS nida_status, d.admin_comment AS admin_notes, d.created_at AS submitted_at
    FROM users u
    LEFT JOIN user_documents d ON d.id = (
        SELECT ud.id
        FROM user_documents ud
        WHERE ud.user_id = u.id AND ud.document_type_id = :doc_type_id
        ORDER BY ud.created_at DESC
        LIMIT 1
    )
    WHERE u.id = :uid
    LIMIT 1
');
$profileStmt->execute(['uid' => $uid, 'doc_type_id' => $docTypeId]);
$profile = $profileStmt->fetch() ?: [];

$mgrid_page_title = mgrid_title('title.verify_id');
require __DIR__ . '/includes/shell_open.php';
?>

<?php if ($msg = flash_get('success')): ?>
  <div class="alert alert-success"><?= e((string) $msg) ?></div>
<?php endif; ?>
<?php foreach ($errors as $err): ?>
  <div class="alert alert-danger"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-4">
    <h1 class="h4 mgrid-dash-page-title mb-2">Uthibitishaji wa NIDA</h1>
    <p class="text-muted small mb-0">Pakia picha ya NIDA ili akaunti yako ithibitishwe kikamilifu.</p>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-4">
        <h2 class="h5 mgrid-dash-section-title mb-3">Pakia picha ya NIDA</h2>
        <form method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label" for="national_id_photo">Faili (JPG/PNG/WEBP, max 5MB)</label>
            <input class="form-control" type="file" id="national_id_photo" name="national_id_photo" accept=".jpg,.jpeg,.png,.webp" required>
          </div>
          <button class="btn btn-primary" type="submit">Tuma kwa uhakiki</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-4">
        <h2 class="h5 mgrid-dash-section-title mb-3">Hali ya uhakiki</h2>
        <p class="mb-2"><strong>Hali ya akaunti:</strong> <?= e(mgrid_account_status_label((string) ($profile['status'] ?? 'pending'))) ?></p>
        <p class="mb-2"><strong>Hali ya NIDA:</strong> <?= e(mgrid_nida_status_display_label($profile['nida_status'] ?? null)) ?></p>
        <p class="small text-muted mb-2">Imetumwa: <?= e((string) ($profile['submitted_at'] ?? '—')) ?></p>
        <?php if (!empty($profile['admin_notes'])): ?>
          <div class="alert alert-warning small mb-0"><strong>Maoni ya admin:</strong> <?= e((string) $profile['admin_notes']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/shell_close.php'; ?>
