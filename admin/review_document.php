<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/guards/admin_guard.php';
require_once __DIR__ . '/../includes/document_helpers.php';
require_once __DIR__ . '/../includes/notification_helper.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
$docId = (int) ($_GET['id'] ?? $_POST['document_id'] ?? 0);

if ($docId <= 0) {
    redirect('admin_documents.php');
}

$stmt = $pdo->prepare(
    "SELECT d.*, u.status AS user_status, u.id AS user_id, u.m_id
     FROM user_documents d
     INNER JOIN users u ON u.id = d.user_id
     WHERE d.id = :id
     LIMIT 1"
);
$stmt->execute([':id' => $docId]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    redirect('admin_documents.php');
}

$errors = [];

if (is_post()) {
    require_csrf();

    $action = (string) ($_POST['action'] ?? '');
    $comment = trim((string) ($_POST['admin_comment'] ?? ''));
    $admin = current_admin();
    $adminId = (int) ($admin['id'] ?? 0);

    if (!in_array($action, ['approve', 'reject', 'resubmission'], true)) {
        $errors[] = 'Hatua si sahihi.';
    }

    if (in_array($action, ['reject', 'resubmission'], true) && $comment === '') {
        $errors[] = 'Weka maoni kabla ya hatua hii.';
    }

    if ($errors === []) {
        try {
            $pdo->beginTransaction();

            if ($action === 'approve') {
                $newDocStatus = 'verified';
                $newUserStatus = 'active';
                $notifyTitle = 'NIDA imethibitishwa';
                $notifyMsg = 'Hongera! NIDA yako imethibitishwa. Akaunti yako sasa iko active.';
                $logAction = 'approve_nida';
            } elseif ($action === 'reject') {
                $newDocStatus = 'rejected';
                $newUserStatus = 'rejected';
                $notifyTitle = 'NIDA imekataliwa';
                $notifyMsg = 'NIDA yako imekataliwa. Sababu: ' . $comment;
                $logAction = 'reject_nida';
            } else {
                $newDocStatus = 'resubmission_requested';
                $newUserStatus = 'pending';
                $notifyTitle = 'Tuma NIDA upya';
                $notifyMsg = 'Tafadhali tuma NIDA upya. Maoni ya msimamizi: ' . $comment;
                $logAction = 'resubmission_nida';
            }

            $upDoc = $pdo->prepare('UPDATE user_documents SET status = :status, admin_comment = :admin_comment, reviewed_at = NOW(), reviewed_by = :reviewed_by WHERE id = :id');
            $upDoc->execute([
                ':status' => $newDocStatus,
                ':admin_comment' => $comment !== '' ? $comment : null,
                ':reviewed_by' => $adminId > 0 ? $adminId : null,
                ':id' => $docId,
            ]);

            $upUser = $pdo->prepare('UPDATE users SET status = :status WHERE id = :user_id');
            $upUser->execute([
                ':status' => $newUserStatus,
                ':user_id' => (int) $doc['user_id'],
            ]);

            $insVLog = $pdo->prepare(
                'INSERT INTO document_verification_logs (document_id, user_id, admin_id, old_status, new_status, comment, created_at)
                 VALUES (:document_id, :user_id, :admin_id, :old_status, :new_status, :comment, NOW())'
            );
            $insVLog->execute([
                ':document_id' => $docId,
                ':user_id' => (int) $doc['user_id'],
                ':admin_id' => $adminId > 0 ? $adminId : null,
                ':old_status' => (string) $doc['status'],
                ':new_status' => $newDocStatus,
                ':comment' => $comment !== '' ? $comment : null,
            ]);

            push_notification($pdo, (int) $doc['user_id'], $notifyTitle, $notifyMsg, 'system');
            write_admin_log($pdo, $adminId, $logAction, 'user_document', $docId, $comment !== '' ? $comment : null);

            if ($action === 'approve') {
                recalculate_mscore($pdo, (int) $doc['user_id']);
            }

            $pdo->commit();
            redirect('admin_documents.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Imeshindikana kuhifadhi mapitio.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kagua NIDA — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/mgrid-reference-theme.css" rel="stylesheet" />
  </head>
  <body class="bg-light">
    <main class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 m-0">Kagua NIDA: <?= e((string) $doc['m_id']) ?></h1>
        <a href="admin_documents.php" class="btn btn-outline-secondary btn-sm">Rudi</a>
      </div>

      <?php foreach ($errors as $error): ?>
        <div class="alert alert-danger border-0 mb-2"><?= e($error) ?></div>
      <?php endforeach; ?>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <p class="mb-2"><strong>Status ya sasa:</strong> <?= e((string) $doc['status']) ?></p>
          <p class="mb-2"><strong>Faili:</strong> <a href="../document_view.php?id=<?= (int) $doc['id'] ?>" target="_blank">Fungua NIDA</a></p>
          <p class="mb-0"><strong>Tarehe ya upakiaji:</strong> <?= e((string) ($doc['created_at'] ?? '')) ?></p>
        </div>
      </div>

      <form method="post" class="card border-0 shadow-sm">
        <div class="card-body">
          <?= csrf_input() ?>
          <input type="hidden" name="document_id" value="<?= (int) $doc['id'] ?>" />
          <div class="mb-3">
            <label class="form-label" for="admin_comment">Maoni ya msimamizi</label>
            <textarea class="form-control" id="admin_comment" name="admin_comment" rows="4"></textarea>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-success" type="submit" name="action" value="approve">Approve</button>
            <button class="btn btn-danger" type="submit" name="action" value="reject">Reject</button>
            <button class="btn btn-warning" type="submit" name="action" value="resubmission">Request Resubmission</button>
          </div>
        </div>
      </form>
    </main>
  </body>
</html>

