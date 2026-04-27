<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function document_type_id_by_code(PDO $pdo, string $code): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM document_types WHERE code = :code LIMIT 1');
    $stmt->execute([':code' => $code]);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int) $id : null;
}

function platform_setting_value(PDO $pdo, string $key, ?string $default = null): ?string
{
    $queries = [
        'SELECT setting_value FROM platform_settings WHERE setting_key = :k LIMIT 1',
        'SELECT value FROM platform_settings WHERE `key` = :k LIMIT 1',
        'SELECT value FROM platform_settings WHERE setting_key = :k LIMIT 1',
    ];

    foreach ($queries as $sql) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':k' => $key]);
            $val = $stmt->fetchColumn();
            if ($val !== false && $val !== null && $val !== '') {
                return (string) $val;
            }
        } catch (Throwable $e) {
            continue;
        }
    }

    return $default;
}

function upload_max_size_mb(PDO $pdo): int
{
    $raw = platform_setting_value($pdo, 'upload_max_file_size_mb', '5');
    $size = (int) $raw;
    return $size > 0 ? $size : 5;
}

function validate_nida_upload(array $file, int $maxMb): array
{
    if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Imeshindikana kupakia faili.'];
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > ($maxMb * 1024 * 1024)) {
        return [false, 'Faili imezidi ukubwa unaoruhusiwa.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return [false, 'Faili si sahihi kwa upakiaji.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string) $finfo->file($tmp);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        return [false, 'Aina ya faili hairuhusiwi. Tumia jpg, png au webp.'];
    }

    return [true, $mime, $allowed[$mime]];
}

function store_nida_file(array $file, int $userId, string $ext): array
{
    $baseDir = __DIR__ . '/../uploads/national_ids';
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
    }

    $name = sprintf('nida_u%d_%s.%s', $userId, bin2hex(random_bytes(12)), $ext);
    $target = $baseDir . DIRECTORY_SEPARATOR . $name;

    if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
        throw new RuntimeException('Imeshindikana kuhifadhi faili.');
    }

    return [
        'disk_path' => realpath($target) ?: $target,
        'relative_path' => 'uploads/national_ids/' . $name,
        'filename' => $name,
    ];
}

function latest_nida_document(PDO $pdo, int $userId, int $docTypeId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM user_documents
         WHERE user_id = :user_id AND document_type_id = :doc_type_id
         ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':doc_type_id' => $docTypeId,
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function create_nida_document(
    PDO $pdo,
    int $userId,
    int $docTypeId,
    string $mime,
    int $size,
    string $relativePath,
    ?string $originalName = null
): int
{
    $prev = latest_nida_document($pdo, $userId, $docTypeId);
    $previousId = null;
    $version = 1;

    if ($prev) {
        $version = (int) ($prev['version_number'] ?? 1) + 1;
        $prevStatus = (string) ($prev['status'] ?? '');
        if (in_array($prevStatus, ['rejected', 'resubmission_requested'], true)) {
            $previousId = (int) $prev['id'];
        }
    }

    static $docColumns = null;
    if (!is_array($docColumns)) {
        $docColumns = [];
        $cols = $pdo->query('SHOW COLUMNS FROM user_documents')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($cols as $col) {
            $field = (string) ($col['Field'] ?? '');
            if ($field !== '') {
                $docColumns[$field] = true;
            }
        }
    }

    $fields = ['user_id', 'document_type_id', 'file_path', 'mime_type', 'file_size', 'status', 'version_number', 'previous_document_id'];
    $params = [':user_id', ':document_type_id', ':file_path', ':mime_type', ':file_size', ':status', ':version_number', ':previous_document_id'];
    $bind = [
        ':user_id' => $userId,
        ':document_type_id' => $docTypeId,
        ':file_path' => $relativePath,
        ':mime_type' => $mime,
        ':file_size' => $size,
        ':status' => 'pending',
        ':version_number' => $version,
        ':previous_document_id' => $previousId,
    ];

    if (isset($docColumns['document_type'])) {
        $fields[] = 'document_type';
        $params[] = ':document_type';
        $bind[':document_type'] = 'nida';
    }
    if (isset($docColumns['original_name'])) {
        $fields[] = 'original_name';
        $params[] = ':original_name';
        $bind[':original_name'] = $originalName !== null && trim($originalName) !== '' ? trim($originalName) : 'nida';
    } elseif (isset($docColumns['title'])) {
        $fields[] = 'title';
        $params[] = ':title';
        $bind[':title'] = 'NIDA';
    }
    if (isset($docColumns['uploaded_at'])) {
        $fields[] = 'uploaded_at';
        $params[] = 'NOW()';
    }

    $sql = sprintf(
        'INSERT INTO user_documents (%s) VALUES (%s)',
        implode(', ', $fields),
        implode(', ', $params)
    );
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bind);

    return (int) $pdo->lastInsertId();
}

function recalculate_mscore(PDO $pdo, int $userId): void
{
    // Basic recalculation: if NIDA verified set tier at least Beginner with base score 10.
    $docTypeId = document_type_id_by_code($pdo, 'nida');
    $verified = false;
    if ($docTypeId !== null) {
        $stmt = $pdo->prepare(
            "SELECT id FROM user_documents WHERE user_id = :user_id AND document_type_id = :doc_type_id AND status = 'verified' LIMIT 1"
        );
        $stmt->execute([':user_id' => $userId, ':doc_type_id' => $docTypeId]);
        $verified = (bool) $stmt->fetchColumn();
    }

    $score = $verified ? 10 : 0;
    $tier = 'Beginner';

    $check = $pdo->prepare('SELECT id FROM mscore_current_scores WHERE user_id = :user_id LIMIT 1');
    $check->execute([':user_id' => $userId]);
    $id = $check->fetchColumn();

    if ($id) {
        $update = $pdo->prepare(
            'UPDATE mscore_current_scores
             SET total_score = :total_score, tier = :tier, updated_at = NOW()
             WHERE user_id = :user_id'
        );
        try {
            $update->execute([
                ':total_score' => $score,
                ':tier' => $tier,
                ':user_id' => $userId,
            ]);
        } catch (Throwable $e) {
            $fallback = $pdo->prepare('UPDATE mscore_current_scores SET tier = :tier WHERE user_id = :user_id');
            $fallback->execute([':tier' => $tier, ':user_id' => $userId]);
        }
        return;
    }

    $insert = $pdo->prepare(
        'INSERT INTO mscore_current_scores (user_id, total_score, tier, created_at, updated_at)
         VALUES (:user_id, :total_score, :tier, NOW(), NOW())'
    );
    try {
        $insert->execute([
            ':user_id' => $userId,
            ':total_score' => $score,
            ':tier' => $tier,
        ]);
    } catch (Throwable $e) {
        $fallback = $pdo->prepare('INSERT INTO mscore_current_scores (user_id, tier) VALUES (:user_id, :tier)');
        $fallback->execute([':user_id' => $userId, ':tier' => $tier]);
    }
}

