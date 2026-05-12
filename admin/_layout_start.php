<?php
/**
 * Wrapper that opens the admin shell.
 * Pages set $pageTitle and $currentPage before requiring this file,
 * then output content, then require _layout_end.php.
 */
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$user = current_user();

require __DIR__ . '/../includes/header.php';
?>
<div class="flex h-screen bg-stone-50">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <div class="flex flex-1 flex-col overflow-hidden">
    <main class="flex-1 overflow-x-hidden overflow-y-auto">
      <div class="mx-auto max-w-7xl px-6 py-10 lg:px-10">
