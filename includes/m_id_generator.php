<?php
declare(strict_types=1);

/**
 * Generate next M-ID in format: M-YYYY-000001
 * Uses m_id_counters table with transaction-safe row lock.
 */
function generate_next_m_id(PDO $pdo): string
{
    $year = (int) date('Y');

    $select = $pdo->prepare('SELECT id, last_number FROM m_id_counters WHERE year = :year LIMIT 1 FOR UPDATE');
    $select->execute([':year' => $year]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $insert = $pdo->prepare('INSERT INTO m_id_counters (year, last_number) VALUES (:year, 0)');
        $insert->execute([':year' => $year]);

        $select->execute([':year' => $year]);
        $row = $select->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new RuntimeException('Imeshindikana kuanzisha kaunta ya M-ID.');
        }
    }

    $counterId = (int) ($row['id'] ?? 0);
    $lastNumber = (int) ($row['last_number'] ?? 0);
    $nextNumber = $lastNumber + 1;

    $update = $pdo->prepare('UPDATE m_id_counters SET last_number = :last_number WHERE id = :id');
    $update->execute([
        ':last_number' => $nextNumber,
        ':id' => $counterId,
    ]);

    return sprintf('M-%d-%06d', $year, $nextNumber);
}

