<?php
declare(strict_types=1);

require __DIR__ . '/user/includes/init_member.php';

if (!is_post()) {
    redirect(url('user/my_documents.php'));
}

if (!csrf_verify((string) ($_POST['_csrf'] ?? $_POST['_token'] ?? ''))) {
    flash_set('error', 'Token si sahihi.');
    redirect(url('user/my_documents.php'));
}

$pdo = db();
$auth = auth_user();
$uid = (int) ($auth['user_id'] ?? 0);

$mode = clean_string($_POST['mode'] ?? 'upload');
$docTypeId = (int) ($_POST['document_type_id'] ?? 0);
$parentDocumentId = (int) ($_POST['parent_document_id'] ?? 0);
$title = clean_string($_POST['title'] ?? '');

if ($uid <= 0 || $docTypeId <= 0 || $title === '') {
    flash_set('error', 'Tafadhali jaza taarifa zote muhimu.');
    redirect(url('user/my_documents.php'));
}

if (!isset($_FILES['document_file']) || !is_array($_FILES['document_file'])) {
    flash_set('error', 'Chagua faili la kupakia.');
    redirect(url('user/my_documents.php'));
}

$file = $_FILES['document_file'];
$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
$size = (int) ($file['size'] ?? 0);
$tmp = (string) ($file['tmp_name'] ?? '');
$originalName = (string) ($file['name'] ?? '');
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
if ($errorCode !== UPLOAD_ERR_OK || $tmp === '') {
    flash_set('error', 'Kupakia faili kumeshindikana.');
    redirect(url('user/my_documents.php'));
}
if ($size <= 0 || $size > (8 * 1024 * 1024)) {
    flash_set('error', 'Ukubwa wa faili usizidi 8MB.');
    redirect(url('user/my_documents.php'));
}
if (!in_array($ext, $allowedExt, true)) {
    flash_set('error', 'Aina ya faili hairuhusiwi.');
    redirect(url('user/my_documents.php'));
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = (string) $finfo->file($tmp);

$uploadDir = MGRID_ROOT . '/uploads/documents/general';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}
$newName = sprintf('doc_%d_%s.%s', $uid, bin2hex(random_bytes(8)), $ext);
$absolutePath = $uploadDir . '/' . $newName;
$relativePath = 'uploads/documents/general/' . $newName;

if (!move_uploaded_file($tmp, $absolutePath)) {
    flash_set('error', 'Imeshindikana kuhifadhi faili.');
    redirect(url('user/my_documents.php'));
}

$columnsStmt = $pdo->query('SHOW COLUMNS FROM user_documents');
$availableColumns = [];
foreach (($columnsStmt ? $columnsStmt->fetchAll(PDO::FETCH_ASSOC) : []) as $col) {
    $field = (string) ($col['Field'] ?? '');
    if ($field !== '') {
        $availableColumns[$field] = true;
    }
}

$version = 1;
$prevId = null;
if ($mode === 'reupload' && $parentDocumentId > 0) {
    $prev = $pdo->prepare('SELECT id, version_number FROM user_documents WHERE id = :id AND user_id = :uid LIMIT 1');
    $prev->execute(['id' => $parentDocumentId, 'uid' => $uid]);
    $prevRow = $prev->fetch(PDO::FETCH_ASSOC);
    if (!$prevRow) {
        flash_set('error', 'Hati ya awali haijapatikana.');
        redirect(url('user/my_documents.php'));
    }
    $prevId = (int) $prevRow['id'];
    $version = (int) ($prevRow['version_number'] ?? 0) + 1;
} else {
    $last = $pdo->prepare('SELECT MAX(version_number) FROM user_documents WHERE user_id = :uid AND document_type_id = :doc_type');
    $last->execute(['uid' => $uid, 'doc_type' => $docTypeId]);
    $version = (int) ($last->fetchColumn() ?: 0) + 1;
}

$fields = ['user_id', 'document_type_id', 'file_path', 'mime_type', 'file_size', 'status', 'version_number'];
$params = [':user_id', ':document_type_id', ':file_path', ':mime_type', ':file_size', ':status', ':version_number'];
$bind = [
    ':user_id' => $uid,
    ':document_type_id' => $docTypeId,
    ':file_path' => $relativePath,
    ':mime_type' => $mime !== '' ? $mime : 'application/octet-stream',
    ':file_size' => $size,
    ':status' => 'pending',
    ':version_number' => $version,
];

if (isset($availableColumns['previous_document_id'])) {
    $fields[] = 'previous_document_id';
    $params[] = ':previous_document_id';
    $bind[':previous_document_id'] = $prevId;
} elseif (isset($availableColumns['parent_document_id'])) {
    $fields[] = 'parent_document_id';
    $params[] = ':parent_document_id';
    $bind[':parent_document_id'] = $prevId;
}
if (isset($availableColumns['original_name'])) {
    $fields[] = 'original_name';
    $params[] = ':original_name';
    $bind[':original_name'] = $title;
} elseif (isset($availableColumns['title'])) {
    $fields[] = 'title';
    $params[] = ':title';
    $bind[':title'] = $title;
}
if (isset($availableColumns['description'])) {
    $fields[] = 'description';
    $params[] = ':description';
    $bind[':description'] = clean_string($_POST['description'] ?? '');
}
if (isset($availableColumns['is_current'])) {
    $pdo->prepare('UPDATE user_documents SET is_current = 0 WHERE user_id = :uid AND document_type_id = :doc_type_id')
        ->execute(['uid' => $uid, 'doc_type_id' => $docTypeId]);
    $fields[] = 'is_current';
    $params[] = ':is_current';
    $bind[':is_current'] = 1;
}

$sql = sprintf('INSERT INTO user_documents (%s) VALUES (%s)', implode(', ', $fields), implode(', ', $params));
$insert = $pdo->prepare($sql);
$insert->execute($bind);

flash_set('success', $mode === 'reupload' ? 'Hati imetumwa upya kwa uhakiki.' : 'Hati imepakiwa kikamilifu.');
redirect(url('user/my_documents.php'));
