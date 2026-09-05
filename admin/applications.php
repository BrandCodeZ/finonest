<?php
/**
 * ============================================================
 * Loan Applications - List / Manage
 * ============================================================
 * Lists all loan applications with search (name / mobile / id),
 * filters (status, date) and pagination. View / Delete actions.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Loan Applications';

// ---- Filters ----
$search  = get('search');
$status  = get('status');
$dateFilter = get('date');

$perPage = 12;
$page    = max(1, (int) get('page', '1'));

// ---- Build WHERE clause dynamically (all values bound) ----
$where  = ['1=1'];
$params = [];

if ($search !== '') {
    $where[]  = '(a.full_name LIKE ? OR a.mobile_number LIKE ? OR CAST(a.id AS CHAR) LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$validStatuses = array_keys(loan_statuses());
if ($status !== '' && in_array($status, $validStatuses, true)) {
    $where[]  = 'a.application_status = ?';
    $params[] = $status;
}

if ($dateFilter !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
    $where[]  = 'DATE(a.created_at) = ?';
    $params[] = $dateFilter;
}

$whereSql = implode(' AND ', $where);

// ---- Count total ----
$stmt = $pdo->prepare('SELECT COUNT(*) FROM loan_applications a WHERE ' . $whereSql);
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

// ---- Fetch page ----
$stmt = $pdo->prepare(
    'SELECT a.*
     FROM loan_applications a
     WHERE ' . $whereSql . '
     ORDER BY a.created_at DESC
     LIMIT ' . $perPage . ' OFFSET ' . $offset
);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Helper to keep current filters while paging.
function filter_qs(): string
{
    $parts = [];
    if (get('search') !== '')  $parts[] = 'search=' . urlencode(get('search'));
    if (get('status') !== '')  $parts[] = 'status=' . urlencode(get('status'));
    if (get('date') !== '')    $parts[] = 'date=' . urlencode(get('date'));
    return $parts ? '&' . implode('&', $parts) : '';
}

include __DIR__ . '/includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>All Loan Applications</h2>
      <p><?= $total ?> application<?= $total === 1 ? '' : 's' ?> found.</p>
    </div>
  </div>

  <!-- Filters -->
  <form method="get" action="applications.php" class="filters" style="margin-bottom:20px">
    <div class="form-group">
      <label for="search">Search (name, mobile, ID)</label>
      <input class="form-control" type="text" id="search" name="search"
             value="<?= e($search) ?>" placeholder="e.g. Ramesh or 94625" />
    </div>
    <div class="form-group">
      <label for="status">Status</label>
      <select class="form-control" id="status" name="status">
        <option value="">All Statuses</option>
        <?php foreach (loan_statuses() as $st => $info): ?>
          <option value="<?= e($st) ?>" <?= $status === $st ? 'selected' : '' ?>><?= e($info['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label for="date">Submitted on</label>
      <input class="form-control" type="date" id="date" name="date"
             value="<?= e($dateFilter) ?>" />
    </div>
    <div class="form-group" style="justify-content:flex-end;flex-direction:row;align-self:end;gap:8px">
      <button type="submit" class="btn btn-ghost">Filter</button>
      <a href="applications.php" class="btn btn-ghost">Reset</a>
    </div>
  </form>

  <?php if ($applications): ?>
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
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $ap): ?>
            <tr>
              <td class="t-muted">#<?= (int)$ap['id'] ?></td>
              <td><span class="t-name"><?= e($ap['full_name']) ?></span>
                <?php if ($ap['email']): ?><div class="t-muted"><?= e($ap['email']) ?></div><?php endif; ?>
              </td>
              <td class="t-muted"><?= e($ap['mobile_number']) ?></td>
              <td><?= e($ap['loan_type']) ?></td>
              <td class="t-price"><?= inr($ap['loan_amount']) ?></td>
              <td><?= loan_status_badge($ap['application_status']) ?></td>
              <td class="t-muted"><?= e(date('d M Y', strtotime($ap['created_at']))) ?><br>
                <small><?= e(date('h:i A', strtotime($ap['created_at']))) ?></small></td>
              <td>
                <div class="actions">
                  <a class="action-btn edit" href="view-application.php?id=<?= (int)$ap['id'] ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    View
                  </a>
                  <form method="post" action="delete-application.php" style="display:inline"
                        onsubmit="return confirm('Delete this loan application permanently?');">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
                    <input type="hidden" name="id" value="<?= (int)$ap['id'] ?>" />
                    <button type="submit" class="action-btn del">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="applications.php?page=<?= $page - 1 . filter_qs() ?>">‹ Prev</a>
        <?php else: ?>
          <span class="disabled">‹ Prev</span>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i == $page): ?>
            <span class="active"><?= $i ?></span>
          <?php else: ?>
            <a href="applications.php?page=<?= $i . filter_qs() ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="applications.php?page=<?= $page + 1 . filter_qs() ?>">Next ›</a>
        <?php else: ?>
          <span class="disabled">Next ›</span>
        <?php endif; ?>
      </div>
      <p class="page-info">Showing page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> applications).</p>
    <?php endif; ?>

  <?php else: ?>
    <div class="empty-state">
      <div class="es-icon">📋</div>
      <h3>No applications found</h3>
      <p>Try adjusting your search / filters, or wait for new submissions from the Apply for Loan form.</p>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
