<?php
/**
 * ============================================================
 * Products Catalog (dynamic)
 * ============================================================
 * Lists all Active products. Supports filtering by category
 * and searching by name. Same design language as the homepage.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$search     = get('search');
$categoryId = get('category');

// Categories for the filter dropdown
$categories = $pdo->query('SELECT * FROM categories WHERE status = 1 ORDER BY category_name')->fetchAll();

// Build product query (only active products)
$where  = ['p.status = 1'];
$params = [];

if ($search !== '') {
    $where[]  = 'p.product_name LIKE ?';
    $params[] = '%' . $search . '%';
}
if ($categoryId !== '' && ctype_digit($categoryId)) {
    $where[]  = 'p.category_id = ?';
    $params[] = (int) $categoryId;
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare(
    'SELECT p.*, c.category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE ' . $whereSql . '
     ORDER BY p.created_at DESC'
);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Find the selected category name for the heading (any status)
$selectedCategoryName = null;
if ($categoryId !== '' && ctype_digit($categoryId)) {
    $stmt = $pdo->prepare('SELECT category_name FROM categories WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $categoryId]);
    $catRow = $stmt->fetch();
    if ($catRow) {
        $selectedCategoryName = $catRow['category_name'];
    }
}

$pageTitle = $selectedCategoryName ? $selectedCategoryName . ' | Finonest' : 'All Loan Products | Finonest';
$pageDescription = 'Explore our full range of loan products — home, car, personal, business and more with the best interest rates.';

$activePage = 'products.php';
require __DIR__ . '/includes/site_header.php';
?>

<!-- ============ PAGE HERO ============ -->
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> &nbsp;/&nbsp; Products</div>
    <h1><?= $selectedCategoryName ? e($selectedCategoryName) : 'All Loan Products' ?></h1>
    <p>Browse our complete range of financial solutions. Compare offers from 50+ banks and get the lowest rate.</p>
  </div>
</section>

<!-- ============ FILTER BAR ============ -->
<section class="section" style="padding:30px 0 0">
  <div class="container">
    <form method="get" action="products.php" class="catalog-bar">
      <div class="catalog-filters">
        <select class="catalog-select" name="category" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input class="catalog-input" type="text" name="search" value="<?= e($search) ?>" placeholder="Search products…" />
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search !== '' || $categoryId !== ''): ?>
          <a href="products.php" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
      </div>
      <span class="catalog-count"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?></span>
    </form>
  </div>
</section>

<!-- ============ PRODUCTS GRID ============ -->
<section class="section" style="padding-top:30px">
  <div class="container">
    <?php if ($products): ?>
      <div class="products-grid">
        <?php foreach ($products as $product): ?>
          <div class="product-card">
            <div class="pc-head">
              <img class="pc-img" src="<?= e(product_image_url($product['image'])) ?>" alt="<?= e($product['product_name']) ?>" />
              <span class="rate">
                <?= inr($product['discount_price'] ?: $product['price']) ?>
                <span><?= e($product['category_name'] ?? 'loan') ?></span>
              </span>
            </div>
            <h3><?= e($product['product_name']) ?></h3>
            <p><?= e(mb_strimwidth((string)$product['description'], 0, 120, '…')) ?></p>
            <div class="meta-line">
              <?= e($product['category_name'] ?? 'Loan') ?>
              <?php if ($product['discount_price']): ?>
                &nbsp;·&nbsp; Was <strong style="text-decoration:line-through"><?= inr($product['price']) ?></strong>
              <?php endif; ?>
              &nbsp;·&nbsp; <?= (int)$product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock' ?>
            </div>
            <a href="product-details.php?id=<?= (int)$product['id'] ?>" class="pc-link">View Details <span>→</span></a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align:center;padding:60px 0">
        <div style="font-size:46px;margin-bottom:12px">🔍</div>
        <h2 style="color:var(--ink)">No products found</h2>
        <p style="color:var(--muted);margin-top:6px">Try a different keyword or category.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/site_footer.php'; ?>