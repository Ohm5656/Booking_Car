<?php
/**
 * Authentication helpers
 *
 * Every page that requires login should `require` this file (which also pulls in
 * db.php). It boots the session and exposes:
 *   - current_user(): ?array
 *   - require_login(): redirect to /auth/login.php if not signed in
 *   - require_admin(): redirect to user dashboard if not an admin
 *   - require_user():  same as require_login() but blocks admins from user pages
 *   - flash() / get_flash(): one-shot status messages (used for toast on next page)
 *   - csrf_token() / csrf_check(): CSRF protection for forms
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    // Harden the session cookie a bit
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Returns the signed-in user (id, name, email, role, phone) or null. */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) return null;

    static $cache = null;
    if ($cache !== null) return $cache;

    global $pdo;
    $stmt = $pdo->prepare('SELECT id, name, email, role, phone FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if (!$row) {
        // Stale session — purge
        $_SESSION = [];
        session_destroy();
        return null;
    }
    return $cache = $row;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $u = current_user();
    return $u !== null && $u['role'] === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . url('/auth/login.php'));
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        header('Location: ' . url('/user/dashboard.php'));
        exit;
    }
}

function require_user(): void
{
    require_login();
    // Admins are redirected to the admin console instead of being shown user pages.
    if (is_admin()) {
        header('Location: ' . url('/admin/dashboard.php'));
        exit;
    }
}

// ── Flash messages ────────────────────────────────────────────────────────

/**
 * Stores a one-shot message that will be displayed (and removed) on the next page.
 * $type ∈ 'success' | 'error'
 */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (empty($_SESSION['_flash'])) return null;
    $f = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
    return $f;
}

// ── CSRF ──────────────────────────────────────────────────────────────────

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $supplied = $_POST['_csrf'] ?? '';
    if (!hash_equals(csrf_token(), (string) $supplied)) {
        http_response_code(419);
        exit('CSRF token mismatch. Please reload the page and try again.');
    }
}
