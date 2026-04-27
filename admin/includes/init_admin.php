<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../includes/guards/admin_guard.php';

if (!function_exists('clean_string')) {
    /**
     * Normalize user input for admin forms/search.
     */
    function clean_string($value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        // Collapse repeated whitespace while preserving readable words.
        $collapsed = preg_replace('/\s+/u', ' ', $text);
        return is_string($collapsed) ? trim($collapsed) : $text;
    }
}
