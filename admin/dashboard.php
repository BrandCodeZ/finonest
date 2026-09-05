<?php
/**
 * ============================================================
 * Admin Dashboard
 * ============================================================
 * Shows key statistics and the 5 most recent products.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';

// ---- Statistics ----
$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$activeProducts = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE status = 1')->fetchColumn();
$outOfStock = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE stock_quantity <= 0')->fetchColumn();
$totalCategories = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$lowStock = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE stock_quantity > 0 AND stock_quantity <= 5')->fetchColumn();

// ---- Recent products ----
$stmt = $pdo->query(
    'SELECT p.id, p.product_name, p.image, p.price, p.discount_price, p.stock_quantity, p.status, c.category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC
     LIMIT 5'
);
$recent = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="sc-icon bg-blue">📦</div>
    <div class="sc-label">Total Products</div>
    <div class="sc-value"><?= $totalProducts ?></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-green">✅</div>
    <div class="sc-label">Active Products</div>
    <div class="sc-value"><?= $activeProducts ?></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-red">⚠️</div>
    <div class="sc-label">Out of Stock</div>
    <div class="sc-value"><?= $outOfStock ?></div>
  </div>
  <div class="stat-card">
    <div class="sc-icon bg-orange">🗂️</div>
    <div class="sc-label">Total Categories</div>
    <div class="sc-value"><?= $totalCategories ?></div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>Recent Products</h2>
      <p>The 5 most recently added products.</p>
    </div>
    <a href="products.php" class="btn btn-ghost btn-sm">View All Products</a>
  </div>

  <?php if ($recent): ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $row): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:12px">
                  <img class="t-img" src="../<?= e(product_image_url($row['image'])) ?>" alt="<?= e($row['product_name']) ?>" />
                  <span class="t-name"><?= e($row['product_name']) ?></span>
                </div>
              </td>
              <td class="t-muted"><?= e($row['category_name'] ?? '—') ?></td>
              <td class="t-price">
                <?= money($row['discount_price'] ?? $row['price']) ?>
                <?php if ($row['discount_price']): ?>
                  <div class="t-disc"><?= money($row['price']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($row['stock_quantity'] <= 0): ?>
                  <span class="badge badge-stock-out">Out of Stock</span>
                <?php elseif ($row['stock_quantity'] <= 5): ?>
                  <span class="badge badge-stock-low">Low (<?= (int)$row['stock_quantity'] ?>)</span>
                <?php else: ?>
                  <span class="badge badge-stock-ok"><?= (int)$row['stock_quantity'] ?> in stock</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($row['status'] == 1): ?>
                  <span class="badge badge-active">Active</span>
                <?php else: ?>
                  <span class="badge badge-inactive">Inactive</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <div class="es-icon">📦</div>
      <h3>No products yet</h3>
      <p>Add your first product to get started.</p>
      <a href="add-product.php" class="btn btn-primary" style="margin-top:16px">+ Add Product</a>
    </div>
  <?php endif; ?>
</div>

<?php if ($lowStock > 0): ?>
  <div class="alert alert-warning">
    <strong><?= $lowStock ?> product(s)</strong> are running low on stock (5 or fewer units). Consider restocking.
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>