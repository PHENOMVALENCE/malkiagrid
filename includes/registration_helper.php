<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Validate registration payload.
 *
 * @return array{0: array<string,string>, 1: array<int,string>}
 */
function validate_registration_payload(array $input): array
{
    $data = [
        'first_name' => trim((string) ($input['first_name'] ?? '')),
        'middle_name' => trim((string) ($input['middle_name'] ?? '')),
        'surname' => trim((string) ($input['surname'] ?? '')),
        'nida_number' => trim((string) ($input['nida_number'] ?? '')),
        'phone' => trim((string) ($input['phone'] ?? '')),
        'email' => trim((string) ($input['email'] ?? '')),
        'password' => (string) ($input['password'] ?? ''),
        'confirm_password' => (string) ($input['confirm_password'] ?? ''),
        'has_registered_business' => trim((string) ($input['has_registered_business'] ?? '')),
        'business_name' => trim((string) ($input['business_name'] ?? '')),
        'business_type' => trim((string) ($input['business_type'] ?? '')),
        'has_bank_account' => trim((string) ($input['has_bank_account'] ?? '')),
        'heard_about' => trim((string) ($input['heard_about'] ?? '')),
    ];

    $errors = [];

    foreach (['first_name', 'surname', 'phone', 'password', 'confirm_password'] as $field) {
        if ($data[$field] === '') {
            $errors[] = 'Tafadhali jaza taarifa zote muhimu za Hatua ya 1.';
            break;
        }
    }

    // Step 2 fields are optional.
    if ($data['has_registered_business'] === '') {
        $data['has_registered_business'] = 'no';
    }
    if ($data['has_bank_account'] === '') {
        $data['has_bank_account'] = 'no';
    }

    if ($data['has_registered_business'] === 'yes') {
        if ($data['business_name'] === '' || $data['business_type'] === '') {
            $errors[] = 'Jaza jina na aina ya biashara iliyosajiliwa.';
        }
    }

    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Barua pepe si sahihi.';
    }

    if (mb_strlen($data['password']) < 8) {
        $errors[] = 'Nenosiri lazima liwe angalau herufi 8.';
    }

    if (!hash_equals($data['password'], $data['confirm_password'])) {
        $errors[] = 'Nenosiri halilingani.';
    }

    return [$data, $errors];
}

function registration_unique_checks(PDO $pdo, array $data): array
{
    $errors = [];

    if ($data['nida_number'] !== '') {
        $nidaCheck = $pdo->prepare('SELECT id FROM users WHERE nida_number = :nida LIMIT 1');
        $nidaCheck->execute([':nida' => $data['nida_number']]);
        if ($nidaCheck->fetch()) {
            $errors[] = 'Namba ya NIDA tayari imetumika.';
        }
    }

    $phoneCheck = $pdo->prepare('SELECT id FROM users WHERE phone = :phone LIMIT 1');
    $phoneCheck->execute([':phone' => $data['phone']]);
    if ($phoneCheck->fetch()) {
        $errors[] = 'Namba ya simu tayari imetumika.';
    }

    if ($data['email'] !== '') {
        $emailCheck = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $emailCheck->execute([':email' => strtolower($data['email'])]);
        if ($emailCheck->fetch()) {
            $errors[] = 'Barua pepe tayari imetumika.';
        }
    }

    return $errors;
}

/**
 * Insert initial record in mscore_current_scores while respecting existing columns.
 */
function initialize_mscore_current_scores(PDO $pdo, int $userId): void
{
    $colStmt = $pdo->query('SHOW COLUMNS FROM mscore_current_scores');
    $columnsRaw = $colStmt->fetchAll(PDO::FETCH_ASSOC);

    $columns = [];
    foreach ($columnsRaw as $row) {
        $field = (string) ($row['Field'] ?? '');
        if ($field !== '') {
            $columns[] = $field;
        }
    }

    if ($columns === []) {
        throw new RuntimeException('Jedwali mscore_current_scores halina columns.');
    }

    $payload = [];

    if (in_array('user_id', $columns, true)) {
        $payload['user_id'] = $userId;
    }

    if (in_array('tier', $columns, true)) {
        $payload['tier'] = 'Beginner';
    }

    foreach ($columns as $column) {
        if ($column === 'id' || $column === 'user_id' || $column === 'tier') {
            continue;
        }
        if (stripos($column, 'score') !== false) {
            $payload[$column] = 0;
        }
    }

    if ($payload === []) {
        return;
    }

    $fieldNames = array_keys($payload);
    $placeholders = array_map(static fn(string $field): string => ':' . $field, $fieldNames);

    $sql = sprintf(
        'INSERT INTO mscore_current_scores (%s) VALUES (%s)',
        implode(', ', $fieldNames),
        implode(', ', $placeholders)
    );

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_combine($placeholders, array_values($payload)));
}

