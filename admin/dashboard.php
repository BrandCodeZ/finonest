<?php
/**
 * ============================================================
 * Admin Dashboard
 * ============================================================
 * Shows loan application statistics and the most recent
 * applications, plus a compact product summary below.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';

// ---- Loan application statistics ----
function appcount(string $filter): int
{
    global $pdo;
    static $map = null;
    if ($map === null) {
        $rows = $pdo->query(
            'SELECT application_status AS s, COUNT(*) AS c
             FROM loan_applications GROUP BY application_status'
        )->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['s']] = (int) $r['c'];
        }
    }
    return (int) ($map[$filter] ?? 0);
}

$totalApplications     = (int) $pdo->query('SELECT COUNT(*) FROM loan_applications')->fetchColumn();
$newApplications       = appcount('New');
$underReview           = appcount('Under Review');
$approvedApplications  = appcount('Approved');
$rejectedApplications  = appcount('Rejected');
$submittedToday        = (int) $pdo->query("SELECT COUNT(*) FROM loan_applications WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// ---- Recent applications ----
$recentApps = $pdo->query(
    'SELECT * FROM loan_applications ORDER BY created_at DESC LIMIT 6'
)->fetchAll();

// ---- Product summary (secondary) ----
$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$activeProducts = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE status = 1')->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="sc-icon bg-blue">📋</div>
    <div class="sc-label">Total Applications</div>
    <div class="sc-value"><?= $totalApplications ?></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-orange">🆕</div>
    <div class="sc-label">New</div>
    <div class="sc-value"><?= $newApplications ?></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-green">✅</div>
    <div class="sc-label">Approved</div>
    <div class="sc-value"><?= $approvedApplications ?></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-red">❌</div>
    <div class="sc-label">Rejected</div>
    <div class="sc-value"><?= $rejectedApplications ?></div>
  </div>
</div>

<div class="stats-grid stats-grid-3">
  <div class="stat-card">
    <div class="sc-icon bg-teal">🔎</div>
    <div class="sc-label">Under Review</div>
    <div class="sc-value"><?= $underReview ?></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-purple">📅</div>
    <div class="sc-label">Submitted Today</div>
    <div class="sc-value"><?= $submittedToday ?></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-slate">🗂️</div>
    <div class="sc-label">Active Products</div>
    <div class="sc-value"><?= $activeProducts ?></div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>Recent Loan Applications</h2>
      <p>The 6 most recent applications submitted from the website.</p>
    </div>
    <a href="applications.php" class="btn btn-ghost btn-sm">View All Applications</a>
  </div>

  <?php if ($recentApps): ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Applicant</th>
            <th>Mobile</th>
            <th>Loan Type</th>
            <th>Loan Amount</th>
            <th>Status</th>
            <th>Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentApps as $ap): ?>
            <tr>
              <td class="t-muted">#<?= (int)$ap['id'] ?></td>
              <td><span class="t-name"><?= e($ap['full_name']) ?></span></td>
              <td class="t-muted"><?= e($ap['mobile_number']) ?></td>
              <td><?= e($ap['loan_type']) ?></td>
              <td class="t-price"><?= inr($ap['loan_amount']) ?></td>
              <td><?= loan_status_badge($ap['application_status']) ?></td>
              <td class="t-muted"><?= e(date('d M Y', strtotime($ap['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <div class="es-icon">📋</div>
      <h3>No applications yet</h3>
      <p>When visitors submit the Apply for Loan form, their applications will appear here.</p>
      <a href="../apply.php" target="_blank" class="btn btn-primary" style="margin-top:16px">Open Apply Page</a>
    </div>
  <?php endif; ?>
</div>

<?php if ($totalProducts > 0): ?>
  <div class="panel">
    <div class="panel-head">
      <div>
        <h2>Products</h2>
        <p><?= $totalProducts ?> products, <?= $activeProducts ?> active.</p>
      </div>
      <a href="products.php" class="btn btn-ghost btn-sm">Manage Products</a>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
