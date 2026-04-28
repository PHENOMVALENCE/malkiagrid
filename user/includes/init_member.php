<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/guards/user_guard.php';

if (!function_exists('clean_string')) {
    function clean_string($value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }
        $collapsed = preg_replace('/\s+/u', ' ', $text);
        return is_string($collapsed) ? trim($collapsed) : $text;
    }
}

if (!function_exists('normalise_phone')) {
    function normalise_phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0')) {
            $digits = '255' . substr($digits, 1);
        }
        if (!str_starts_with($digits, '255')) {
            $digits = '255' . $digits;
        }
        return '+' . $digits;
    }
}

if (!function_exists('mgrid_document_status_label')) {
    function mgrid_document_status_label(string $status): string
    {
        return match ($status) {
            'verified' => 'Imethibitishwa',
            'pending' => 'Inasubiri',
            'rejected' => 'Imekataliwa',
            'resubmission_requested' => 'Tuma upya',
            default => $status,
        };
    }
}

if (!function_exists('mgrid_document_status_badge')) {
    function mgrid_document_status_badge(string $status): string
    {
        return match ($status) {
            'verified' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            'resubmission_requested' => 'info',
            default => 'secondary',
        };
    }
}

if (!function_exists('mgrid_document_can_reupload')) {
    function mgrid_document_can_reupload(string $status): bool
    {
        return in_array($status, ['rejected', 'resubmission_requested'], true);
    }
}

if (!function_exists('mgrid_document_types')) {
    function mgrid_document_types(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT id, COALESCE(name_sw, name_en) AS name, code AS slug FROM document_types WHERE is_active = 1 ORDER BY id ASC');
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return is_array($rows) ? $rows : [];
    }
}

if (!function_exists('mgrid_document_find_for_user')) {
    function mgrid_document_find_for_user(PDO $pdo, int $documentId, int $userId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM user_documents WHERE id = :id AND user_id = :uid LIMIT 1');
        $stmt->execute(['id' => $documentId, 'uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }
}
