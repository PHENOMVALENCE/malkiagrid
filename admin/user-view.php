<?php
declare(strict_types=1);

require __DIR__ . '/includes/init_admin.php';
require_once __DIR__ . '/../includes/notification_helper.php';

$pdo = db();
$admin = auth_admin();
$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    http_response_code(404);
    echo 'User not found.';
    exit;
}

$actionErrors = [];

$userStmt = $pdo->prepare(
    'SELECT u.*, p.region, p.date_of_birth, p.has_registered_business, p.business_name, p.business_type, p.profile_completion
     FROM users u
     LEFT JOIN user_profiles p ON p.user_id = u.id
     WHERE u.id = :id
     LIMIT 1'
);
$userStmt->execute([':id' => $id]);
$row = $userStmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    http_response_code(404);
    echo 'Member not found.';
    exit;
}

$fullName = trim((string) (($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['surname'] ?? '')));
if ($fullName === '') {
    $fullName = 'Mwanachama';
}

$scoreStmt = $pdo->prepare('SELECT total_score, tier FROM mscore_current_scores WHERE user_id = :id LIMIT 1');
$scoreStmt->execute([':id' => $id]);
$scoreRow = $scoreStmt->fetch(PDO::FETCH_ASSOC) ?: ['total_score' => 0, 'tier' => 'Beginner'];

$docStmt = $pdo->prepare(
    "SELECT d.id, d.file_path, d.status, d.admin_comment, d.created_at, d.reviewed_at
     FROM user_documents d
     INNER JOIN document_types dt ON dt.id = d.document_type_id
     WHERE d.user_id = :id AND dt.code = 'nida'
     ORDER BY d.id DESC
     LIMIT 1"
);
$docStmt->execute([':id' => $id]);
$nidaDoc = $docStmt->fetch(PDO::FETCH_ASSOC) ?: null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf((string) ($_POST['_token'] ?? ''))) {
        $actionErrors[] = 'Invalid security token.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        $note = clean_string($_POST['review_note'] ?? '');

        try {
            $pdo->beginTransaction();

            if ($action === 'approve_id') {
                $up = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
                $up->execute([':status' => 'active', ':id' => $id]);

                if (is_array($nidaDoc)) {
                    $upDoc = $pdo->prepare(
                        'UPDATE user_documents
                         SET status = :status, admin_comment = :admin_comment, reviewed_by = :reviewed_by, reviewed_at = NOW()
                         WHERE id = :id'
                    );
                    $upDoc->execute([
                        ':status' => 'verified',
                        ':admin_comment' => $note !== '' ? $note : 'Approved by admin',
                        ':reviewed_by' => (int) ($admin['admin_id'] ?? 0),
                        ':id' => (int) $nidaDoc['id'],
                    ]);
                }

                write_admin_log(
                    $pdo,
                    (int) ($admin['admin_id'] ?? 0),
                    'approve_national_id',
                    'user',
                    $id,
                    'Approved NIDA for ' . (string) ($row['m_id'] ?? '')
                );
                $pdo->commit();
                flash_set('success', 'NIDA imeidhinishwa kikamilifu.');
                redirect(url('admin/user-view.php?id=' . $id));
            } elseif ($action === 'reject_id') {
                $up = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
                $up->execute([':status' => 'pending', ':id' => $id]);

                if (is_array($nidaDoc)) {
                    $upDoc = $pdo->prepare(
                        'UPDATE user_documents
                         SET status = :status, admin_comment = :admin_comment, reviewed_by = :reviewed_by, reviewed_at = NOW()
                         WHERE id = :id'
                    );
                    $upDoc->execute([
                        ':status' => 'rejected',
                        ':admin_comment' => $note !== '' ? $note : 'Please upload a clearer photo.',
                        ':reviewed_by' => (int) ($admin['admin_id'] ?? 0),
                        ':id' => (int) $nidaDoc['id'],
                    ]);
                }

                write_admin_log(
                    $pdo,
                    (int) ($admin['admin_id'] ?? 0),
                    'reject_national_id',
                    'user',
                    $id,
                    'Rejected NIDA for ' . (string) ($row['m_id'] ?? '')
                );
                $pdo->commit();
                flash_set('success', 'NIDA imekataliwa. Mwanaachama ataomba upya.');
                redirect(url('admin/user-view.php?id=' . $id));
            } else {
                $pdo->rollBack();
                $actionErrors[] = 'Hatua si sahihi.';
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $actionErrors[] = 'Imeshindikana kuhifadhi mapitio.';
        }
    }
}

$successMessages = flash_get('success');
$mgrid_page_title = 'Member Detail — ' . (string) ($row['m_id'] ?? '');
require __DIR__ . '/includes/shell_open.php';
?>

<div class="mb-3">
  <a class="btn btn-sm btn-light border" href="<?= e(url('admin/users.php')) ?>">&larr; Back to directory</a>
</div>

<?php foreach ($successMessages as $msg): ?>
  <div class="alert alert-success"><?= e((string) $msg) ?></div>
<?php endforeach; ?>
<?php foreach ($actionErrors as $err): ?>
  <div class="alert alert-danger"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body p-4">
    <h1 class="h4 mgrid-dash-page-title mb-4"><?= e($fullName) ?></h1>
    <div class="row g-4">
      <div class="col-md-4">
        <p class="text-muted small mb-1">M-ID</p>
        <p class="fw-bold fs-5"><?= e((string) ($row['m_id'] ?? '')) ?></p>
      </div>
      <div class="col-md-4">
        <p class="text-muted small mb-1">Status</p>
        <p class="mb-0"><span class="badge bg-light text-dark border"><?= e((string) ($row['status'] ?? 'pending')) ?></span></p>
      </div>
      <div class="col-md-4">
        <p class="text-muted small mb-1">Joined</p>
        <p class="mb-0"><?= e((string) ($row['created_at'] ?? '')) ?></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-1">Email</p>
        <p class="mb-0"><?= e((string) ($row['email'] ?? '')) ?></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-1">Phone</p>
        <p class="mb-0"><?= e((string) ($row['phone'] ?? '')) ?></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-1">Region</p>
        <p class="mb-0"><?= e((string) ($row['region'] ?? '')) ?></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-1">Business</p>
        <p class="mb-0"><?= e((string) (($row['has_registered_business'] ?? 'no') === 'yes' ? ($row['business_name'] ?? 'Registered') : 'Not registered')) ?></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-1">M-Score tier</p>
        <p class="mb-0"><?= e((string) ($scoreRow['tier'] ?? 'Beginner')) ?> · <?= (int) ($scoreRow['total_score'] ?? 0) ?></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-1">Profile completion</p>
        <p class="mb-0"><?= (int) ($row['profile_completion'] ?? 0) ?>%</p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-1">National ID status</p>
        <p class="mb-0"><span class="badge bg-light text-dark border"><?= e((string) ($nidaDoc['status'] ?? 'not_submitted')) ?></span></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-1">National ID submitted</p>
        <p class="mb-0"><?= e((string) ($nidaDoc['created_at'] ?? '—')) ?></p>
      </div>
      <div class="col-12">
        <p class="text-muted small mb-1">National ID photo</p>
        <?php if (is_array($nidaDoc) && !empty($nidaDoc['file_path'])): ?>
          <a class="btn btn-sm btn-outline-primary mb-2" target="_blank" href="<?= e(url('document_view.php?id=' . (int) $nidaDoc['id'])) ?>">Open uploaded photo</a>
        <?php else: ?>
          <p class="mb-0 text-muted">No file uploaded yet.</p>
        <?php endif; ?>
      </div>
      <div class="col-12">
        <form method="post" class="row g-2 align-items-end">
          <?= csrf_input() ?>
          <div class="col-md-8">
            <label class="form-label">Review note (optional)</label>
            <input type="text" class="form-control" name="review_note" maxlength="255" placeholder="Reason, instruction, or approval note">
          </div>
          <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-success w-100" type="submit" name="action" value="approve_id">Approve ID</button>
            <button class="btn btn-danger w-100" type="submit" name="action" value="reject_id">Reject ID</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/shell_close.php'; ?>
