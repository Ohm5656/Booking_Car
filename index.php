<?php
require_once __DIR__ . '/includes/auth.php';

if (is_admin()) {
    header('Location: ' . url('/admin/dashboard.php'));
    exit;
}
if (is_logged_in()) {
    header('Location: ' . url('/user/dashboard.php'));
    exit;
}
header('Location: ' . url('/auth/login.php'));
exit;
