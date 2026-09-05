<?php
/**
 * ============================================================
 * View Products
 * ============================================================
 * Lists all products with search (by name), filters (category,
 * status) and pagination. Edit / Delete actions included.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Products';

// ---- Filters ----
$search     = get('search');
$categoryId = get('category');
$status     = get('status');

$perPage  = 10;
$page     = max(1, (int) get('page', '1'));

// ---- Build the WHERE clause dynamically ----
$where  = ['1=1'];
$params = [];

if ($search !== '') {
    $where[]   = 'p.product_name LIKE ?';
    $params[]  = '%' . $search . '%';
}
if ($categoryId !== '' && ctype_digit($categoryId)) {
    $where[]  = 'p.category_id = ?';
    $params[] = (int) $categoryId;
}
if ($status === '1' || $status === '0') {
    $where[]  = 'p.status = ?';
    $params[] = (int) $status;
}

$whereSql = implode(' AND ', $where);

// ---- Count total (for pagination) ----
$stmt = $pdo->prepare('SELECT COUNT(*) FROM products p WHERE ' . $whereSql);
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$totalPages = max(1, (int) ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

// ---- Fetch page ----
$stmt = $pdo->prepare(
    'SELECT p.*, c.category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE ' . $whereSql . '
     ORDER BY p.created_at DESC
     LIMIT ' . $perPage . ' OFFSET ' . $offset
);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ---- Categories for the filter dropdown ----
$categories = $pdo->query('SELECT id, category_name FROM categories ORDER BY category_name')->fetchAll();

// Helper to keep current filters while paging
function filter_qs(): string
{
    $parts = [];
    if (get('search') !== '')      $parts[] = 'search=' . urlencode(get('search'));
    if (get('category') !== '')    $parts[] = 'category=' . urlencode(get('category'));
    if (get('status') !== '')      $parts[] = 'status=' . urlencode(get('status'));
    return $parts ? '&' . implode('&', $parts) : '';
}

include __DIR__ . '/includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>All Products</h2>
      <p><?= $total ?> product<?= $total === 1 ? '' : 's' ?> found.</p>
    </div>
    <a href="add-product.php" class="btn btn-primary btn-sm">+ Add Product</a>
  </div>

  <!-- Filters -->
  <form method="get" action="products.php" class="filters" style="margin-bottom:20px">
    <div class="form-group">
      <label for="search">Search by name</label>
      <input class="form-control" type="text" id="search" name="search"
             value="<?= e($search) ?>" placeholder="e.g. Home Loan" />
    </div>
    <div class="form-group">
      <label for="category">Category</label>
      <select class="form-control" id="category" name="category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int)$cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
            <?= e($cat['category_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label for="status">Status</label>
      <select class="form-control" id="status" name="status">
        <option value="">All Statuses</option>
        <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
        <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive</option>
      </select>
    </div>
    <div class="form-group" style="justify-content:flex-end;flex-direction:row;align-self:end;gap:8px">
      <button type="submit" class="btn btn-ghost">Filter</button>
      <a href="products.php" class="btn btn-ghost">Reset</a>
    </div>
  </form>

  <?php if ($products): ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Discount</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><img class="t-img" src="../<?= e(product_image_url($p['image'])) ?>" alt="<?= e($p['product_name']) ?>" /></td>
              <td class="t-name"><?= e($p['product_name']) ?></td>
              <td class="t-muted"><?= e($p['category_name'] ?? '—') ?></td>
              <td class="t-price"><?= money($p['price']) ?></td>
              <td>
                <?php if ($p['discount_price'] !== null && (float)$p['discount_price'] > 0): ?>
                  <span class="t-discount"><?= money($p['discount_price']) ?></span>
                <?php else: ?>
                  <span class="t-muted">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($p['stock_quantity'] <= 0): ?>
                  <span class="badge badge-stock-out">Out of Stock</span>
                <?php elseif ($p['stock_quantity'] <= 5): ?>
                  <span class="badge badge-stock-low"><?= (int)$p['stock_quantity'] ?> left</span>
                <?php else: ?>
                  <span class="badge badge-stock-ok"><?= (int)$p['stock_quantity'] ?></span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($p['status'] == 1): ?>
                  <span class="badge badge-active">Active</span>
                <?php else: ?>
                  <span class="badge badge-inactive">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="t-muted"><?= e(date('d M Y', strtotime($p['created_at']))) ?></td>
              <td>
                <div class="actions">
                  <a class="action-btn edit" href="edit-product.php?id=<?= (int)$p['id'] ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    Edit
                  </a>
                  <form method="post" action="delete-product.php" style="display:inline"
                        onsubmit="return confirm('Delete this product permanently? Its image will also be removed.');">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>" />
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
          <a href="products.php?page=<?= $page - 1 . filter_qs() ?>">‹ Prev</a>
        <?php else: ?>
          <span class="disabled">‹ Prev</span>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i == $page): ?>
            <span class="active"><?= $i ?></span>
          <?php else: ?>
            <a href="products.php?page=<?= $i . filter_qs() ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="products.php?page=<?= $page + 1 . filter_qs() ?>">Next ›</a>
        <?php else: ?>
          <span class="disabled">Next ›</span>
        <?php endif; ?>
      </div>
      <p class="page-info">Showing page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> products).</p>
    <?php endif; ?>

  <?php else: ?>
    <div class="empty-state">
      <div class="es-icon">🔍</div>
      <h3>No products found</h3>
      <p>Try adjusting your search / filters, or add a new product.</p>
      <a href="add-product.php" class="btn btn-primary" style="margin-top:16px">+ Add Product</a>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>