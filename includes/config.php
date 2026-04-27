<?php
declare(strict_types=1);

/**
 * Malkia Grid - Core configuration.
 * Keep this file environment-driven for local/prod flexibility.
 */

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Malkia Grid');
}

if (!defined('APP_ENV')) {
    define('APP_ENV', (string) (getenv('APP_ENV') ?: 'production'));
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', APP_ENV !== 'production');
}

if (!defined('APP_URL')) {
    define('APP_URL', rtrim((string) (getenv('APP_URL') ?: ''), '/'));
}

if (!defined('DB_HOST')) {
    define('DB_HOST', (string) (getenv('DB_HOST') ?: '127.0.0.1'));
}
if (!defined('DB_PORT')) {
    define('DB_PORT', (string) (getenv('DB_PORT') ?: '3306'));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', (string) (getenv('DB_NAME') ?: 'malkia_grid'));
}
if (!defined('DB_USER')) {
    define('DB_USER', (string) (getenv('DB_USER') ?: 'root'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', (string) (getenv('DB_PASS') ?: ''));
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'mgrid_session');
}

if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 60 * 60 * 4); // 4 hours
}

if (!defined('CSRF_TOKEN_KEY')) {
    define('CSRF_TOKEN_KEY', '_csrf_token');
}

if (!defined('CSRF_TOKEN_TTL')) {
    define('CSRF_TOKEN_TTL', 60 * 60 * 2); // 2 hours
}

