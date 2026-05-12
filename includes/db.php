<?php
/**
 * Database connection (PDO / MySQL)
 *
 * For XAMPP defaults the values below should just work.
 * On shared hosting / cPanel, change DB_NAME / DB_USER / DB_PASS to whatever the
 * host gave you.
 */

declare(strict_types=1);

// ─── CONFIG ──────────────────────────────────────────────────────────────
const DB_HOST    = 'localhost';
const DB_NAME    = 'if0_41900583_car_booking';
const DB_USER    = 'root';
const DB_PASS    = '';
const DB_CHARSET = 'utf8mb4';

// Public URL prefix where this project is served.
// XAMPP default → http://localhost/car-booking-system
// On a real host where the project sits at the document root → leave as '' (empty).
const BASE_URL = '/car-booking-system';

// ─── PDO BOOTSTRAP ────────────────────────────────────────────────────────
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Database error</title>'
       . '<pre style="font:14px/1.5 ui-monospace,monospace;padding:24px;color:#7f1d1d;background:#fef2f2;">'
       . 'Database connection failed:' . PHP_EOL
       . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . PHP_EOL . PHP_EOL
       . 'Check includes/db.php and make sure the database has been imported.'
       . '</pre>';
    exit;
}

/**
 * Build an absolute URL for the application.
 * Example: url('/admin/dashboard.php') → '/car-booking-system/admin/dashboard.php'
 */
function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return rtrim(BASE_URL, '/') . $path;
}

/**
 * HTML-escape helper.
 */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
