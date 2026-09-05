<?php
/**
 * ============================================================
 * Admin Panel - Shared Layout Header (sidebar + topbar)
 * ============================================================
 * Expects: $pageTitle (string) set before including this file.
 * Requires auth.php to have been included first (session + $pdo).
 * ============================================================
 */

$currentPage = basename($_SERVER['PHP_SELF']);

function admin_link(string $file, string $current, string $label): string
{
    $active = $file === $current ? ' class="sb-link active"' : ' class="sb-link"';
    return '<a href="' . $file . '"' . $active . '>' . $label . '</a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle ?? 'Admin') ?> | Finonest Admin</title>
  <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg" />
  <link rel="stylesheet" href="css/admin.css" />
</head>
<body class="admin-body">
<div class="admin-shell">

  <!-- ===== Sidebar ===== -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sb-brand">
      <span class="sb-logo">F</span>
      <span class="sb-brand-name">Finonest<em>.</em> Admin</span>
    </div>

    <nav class="sb-nav">
      <?= admin_link('dashboard.php',   $currentPage, 'Dashboard') ?>
      <?= admin_link('products.php',    $currentPage, 'Products') ?>
      <?= admin_link('add-product.php', $currentPage, 'Add Product') ?>
      <?= admin_link('categories.php',  $currentPage, 'Categories') ?>
    </nav>

    <div class="sb-foot">
      <a href="logout.php" class="sb-logout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout
      </a>
    </div>
  </aside>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ===== Main ===== -->
  <main class="admin-main">
    <div class="admin-topbar">
      <button class="menu-btn" id="menuBtn" aria-label="Toggle sidebar">
        <span></span><span></span><span></span>
      </button>
      <h1 class="admin-title"><?= e($pageTitle ?? 'Admin') ?></h1>
      <div class="admin-user">
        <span class="au-avatar"><?= e(strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1))) ?></span>
        <span class="au-info">
          <strong><?= e($_SESSION['admin_username'] ?? 'Admin') ?></strong>
          <small><?= e($_SESSION['admin_email'] ?? '') ?></small>
        </span>
      </div>
    </div>

    <div class="admin-content">
      <?php $flash = get_flash(); if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>