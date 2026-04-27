<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/guards/admin_guard.php';
require_once __DIR__ . '/../includes/document_helpers.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
$docTypeId = document_type_id_by_code($pdo, 'nida');
$rows = [];

if ($docTypeId !== null) {
    $stmt = $pdo->prepare(
        "SELECT d.id, d.user_id, d.document_type, d.original_name, d.status, d.created_at AS uploaded_at, d.version_number,
                u.m_id, u.first_name, u.middle_name, u.surname
         FROM user_documents d
         INNER JOIN users u ON u.id = d.user_id
         WHERE d.document_type_id = :doc_type_id
           AND d.status = 'pending'
         ORDER BY d.created_at ASC"
    );
    $stmt->execute([':doc_type_id' => $docTypeId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
?>
<!DOCTYPE html>
<html lang="sw">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nyaraka za NIDA — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/mgrid-reference-theme.css" rel="stylesheet" />
  </head>
  <body class="bg-light">
    <main class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 m-0">NIDA zinazosubiri mapitio</h1>
        <a href="../admin/dashboard.php" class="btn btn-outline-secondary btn-sm">Rudi dashibodi</a>
      </div>

      <?php if ($docTypeId === null): ?>
        <div class="alert alert-danger border-0">Aina ya document `nida` haipo kwenye `document_types`.</div>
      <?php elseif ($rows === []): ?>
        <div class="alert alert-info border-0">Hakuna NIDA inayosubiri kwa sasa.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle bg-white">
            <thead>
              <tr>
                <th>M-ID</th>
                <th>Jina</th>
                <th>Kichwa</th>
                <th>Toleo</th>
                <th>Tarehe</th>
                <th>Hatua</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><?= e((string) $row['m_id']) ?></td>
                  <td><?= e(trim((string) ($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['surname']))) ?></td>
                  <td><?= e((string) ($row['original_name'] ?? $row['document_type'] ?? 'NIDA')) ?></td>
                  <td>v<?= e((string) $row['version_number']) ?></td>
                  <td><?= e((string) $row['uploaded_at']) ?></td>
                  <td class="d-flex gap-2">
                    <a class="btn btn-sm btn-outline-primary" href="../document_view.php?id=<?= (int) $row['id'] ?>" target="_blank">Fungua</a>
                    <a class="btn btn-sm btn-primary" href="review_document.php?id=<?= (int) $row['id'] ?>">Kagua</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </main>
  </body>
</html>

