<?php
/**
 * ============================================================
 * Shared Frontend Header (PHP)
 * ============================================================
 * Replacement for partials/head.html used by the dynamic pages.
 * Expects optional $pageTitle, $pageDescription and $activePage.
 *
 * The products dropdown is rendered dynamically from the active
 * categories stored in the database.
 * ============================================================
 */

$pageTitle       = $pageTitle ?? 'Finonest | India Fastest Growing Loan Provider';
$pageDescription = $pageDescription ?? 'Get home loans, car loans, personal loans &amp; business loans with quick approval and competitive interest rates.';
$activePage      = $activePage ?? basename($_SERVER['PHP_SELF']);

// Categories for the Loans dropdown (only needs a DB connection if one exists)
$navCategories = [];
if (isset($pdo)) {
    $stmt = $pdo->query('SELECT * FROM categories WHERE status = 1 ORDER BY category_name');
    $navCategories = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>" />
  <meta name="author" content="Finonest India Pvt. Ltd." />
  <meta name="theme-color" content="#0a3055" />
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
  <link rel="stylesheet" href="css/styles.css" />
</head>
<body>

<!-- ===== Topbar ===== -->
<div class="topbar">
  <div class="container">
    <div class="tb-left">
      <span class="tb-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        +91 94625 53887
      </span>
      <span class="tb-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        info@finonest.com
      </span>
    </div>
    <div class="tb-right">
      <span class="tb-item">Mon - Sat: 9:30 AM - 7:00 PM</span>
      <span class="tb-item">May I help you?</span>
    </div>
  </div>
</div>

<!-- ===== Navbar ===== -->
<nav class="navbar">
  <div class="container nav-inner">
    <a href="index.php" class="logo">
      <span class="logo-mark">F</span>
      <span class="logo-text">Finonest<span>.</span></span>
    </a>
    <ul class="nav-links">
      <li><a href="index.php" class="<?= $activePage === 'index.php' ? 'active' : '' ?>">Home</a></li>
      <li class="dropdown">
        <a href="products.php" class="nav-link">Loans <span class="dropdown-arrow">&#9662;</span></a>
        <div class="dropdown-menu">
          <?php if ($navCategories): ?>
            <?php foreach ($navCategories as $cat): ?>
              <a href="products.php?category=<?= (int)$cat['id'] ?>">
                <span class="dd-icon">🏦</span> <?= e($cat['category_name']) ?>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <a href="products.php"><span class="dd-icon bg-blue">🏠</span> All Products</a>
          <?php endif; ?>
        </div>
      </li>
      <li><a href="products.php" class="<?= $activePage === 'products.php' ? 'active' : '' ?>">All Products</a></li>
      <li><a href="emi-calculator.html" class="<?= $activePage === 'emi-calculator.html' ? 'active' : '' ?>">EMI Calculator</a></li>
      <li><a href="about.html" class="<?= $activePage === 'about.html' ? 'active' : '' ?>">About Us</a></li>
      <li><a href="dsa-partner.html">DSA Partner</a></li>
      <li><a href="contact.html">Contact</a></li>
    </ul>
    <div class="nav-cta">
      <a href="apply.php" class="btn btn-primary">Apply Now</a>
      <button class="menu-toggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</nav>