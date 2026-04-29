<?php

declare(strict_types=1);

/**
 * UI language (Swahili-first). Uses $_SESSION['preferred_language'] ('sw'|'en').
 * Guest default: Swahili. Logged-in users sync from DB on login.
 */

/** @var array<string, array<string, string>>|null */
$mgrid_i18n_php_cache = null;

function mgrid_normalize_ui_lang(?string $lang): string
{
    $lang = strtolower(trim((string) $lang));
    return in_array($lang, ['sw', 'en'], true) ? $lang : 'sw';
}

function mgrid_ui_lang(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return 'sw';
    }

    return mgrid_normalize_ui_lang((string) ($_SESSION['preferred_language'] ?? 'sw'));
}

/**
 * Ensure session has a valid UI language (default Swahili).
 */
function mgrid_i18n_bootstrap(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    $_SESSION['preferred_language'] = mgrid_normalize_ui_lang((string) ($_SESSION['preferred_language'] ?? 'sw'));
}

/**
 * @return array<string, string>
 */
function mgrid_i18n_php_strings(string $lang): array
{
    global $mgrid_i18n_php_cache;
    $lang = mgrid_normalize_ui_lang($lang);
    if ($mgrid_i18n_php_cache === null) {
        $en = require __DIR__ . '/../lang/php_en.php';
        $sw = require __DIR__ . '/../lang/php_sw.php';
        if (!is_array($en)) {
            $en = [];
        }
        if (!is_array($sw)) {
            $sw = [];
        }
        $mgrid_i18n_php_cache = ['en' => $en, 'sw' => $sw];
    }

    return $mgrid_i18n_php_cache[$lang] ?? $mgrid_i18n_php_cache['sw'];
}

/**
 * Translate PHP-visible strings (errors, flashes, labels not covered by data-i18n).
 *
 * @param array<string, string> $replace Placeholders :key in string
 */
function __(string $key, array $replace = []): string
{
    $lang = mgrid_ui_lang();
    $table = mgrid_i18n_php_strings($lang);
    $s = $table[$key] ?? '';
    if ($s === '') {
        $alt = mgrid_i18n_php_strings($lang === 'sw' ? 'en' : 'sw');
        $s = $alt[$key] ?? '';
    }
    if ($s === '') {
        $s = $key;
    }
    foreach ($replace as $k => $v) {
        $s = str_replace(':' . $k, (string) $v, $s);
    }

    return $s;
}

/** Browser title: translated page name + brand (e.g. "Nyumbani yangu — M GRID"). */
function mgrid_title(string $titleKey, array $replace = []): string
{
    return __($titleKey, $replace) . ' — ' . __('site.brand');
}

/**
 * Human label for `users.status` (DB stores English slugs; UI shows translated text when lang is Swahili).
 */
function mgrid_account_status_label(string $status): string
{
    $slug = strtolower(trim($status));
    if ($slug === '') {
        return '';
    }

    $key = 'display.account_status.' . $slug;
    $s = __($key);

    return $s !== $key ? $s : ucfirst($slug);
}

/**
 * Human label for M-SCORE tier stored in DB (e.g. Beginner, Gold, or admin tier labels).
 */
function mgrid_mscore_tier_display_label(string $tier): string
{
    $t = trim($tier);
    if ($t === '') {
        return '';
    }

    $slug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $t));
    $slug = trim((string) preg_replace('/_+/', '_', $slug), '_');
    $key = 'display.mscore_tier.' . $slug;
    $s = __($key);

    return $s !== $key ? $s : $t;
}

/**
 * Label for NIDA upload row status (`user_documents.status`) or synthetic `not_submitted` when none exists.
 */
function mgrid_nida_status_display_label(?string $status): string
{
    $raw = trim((string) ($status ?? ''));
    if ($raw === '') {
        $raw = 'not_submitted';
    }

    $slug = strtolower($raw);
    $key = 'display.nida_status.' . $slug;
    $s = __($key);

    return $s !== $key ? $s : str_replace('_', ' ', ucfirst($raw));
}
