<?php
/**
 * ============================================================
 * Product Details (dynamic)
 * ============================================================
 * Shows a single Active product. Inactive or missing products
 * show a proper "not found" message.
 * Access via: product-details.php?id=PRODUCT_ID
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int) get('id');
$product = null;

if ($id > 0) {
    $stmt = $pdo->prepare(
        'SELECT p.*, c.category_name
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id = ? AND p.status = 1
         LIMIT 1'
    );
    $stmt->execute([$id]);
    $product = $stmt->fetch();
}

if (!$product) {
    $pageTitle = 'Product Not Found | Finonest';
    $activePage = 'products.php';
    require __DIR__ . '/includes/site_header.php';
    ?>
    <!-- ============ PAGE HERO ============ -->
    <section class="page-hero">
      <div class="container">
        <div class="crumbs"><a href="index.php">Home</a> &nbsp;/&nbsp; Products</div>
        <h1>Product Not Found</h1>
        <p>The product you are looking for does not exist or is currently unavailable.</p>
      </div>
    </section>

    <section class="section">
      <div class="container" style="text-align:center">
        <div style="font-size:56px;margin-bottom:14px">😕</div>
        <h2 style="color:var(--ink);margin-bottom:8px">Sorry, no product here</h2>
        <p style="color:var(--muted);margin-bottom:24px">It may have been removed or made inactive by the admin.</p>
        <a href="products.php" class="btn btn-primary btn-lg">Browse All Products</a>
        <a href="contact.html" class="btn btn-ghost btn-lg" style="margin-left:8px">Contact Us</a>
      </div>
    </section>
    <?php
    require __DIR__ . '/includes/site_footer.php';
    exit;
}

$isDiscounted = !empty($product['discount_price']);
$showPrice    = $isDiscounted ? $product['discount_price'] : $product['price'];
$inStock      = (int) $product['stock_quantity'] > 0;

$pageTitle = $product['product_name'] . ' | Finonest';
$pageDescription = mb_strimwidth((string) $product['description'], 0, 155, '…');
$activePage = 'product-details.php';
require __DIR__ . '/includes/site_header.php';
?>

<!-- ============ PAGE HERO ============ -->
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> &nbsp;/&nbsp; <a href="products.php">Products</a> &nbsp;/&nbsp; <?= e($product['product_name']) ?></div>
    <h1><?= e($product['product_name']) ?></h1>
    <p>Get the best offer on <?= e($product['product_name']) ?> with fast approval, minimal paperwork and zero hidden charges.</p>
  </div>
</section>

<!-- ============ PRODUCT DETAILS ============ -->
<section class="section">
  <div class="container two-col">
    <div class="col-text">
      <div class="ct-eyebrow"><?= e($product['category_name'] ?? 'Product') ?></div>
      <h2><?= e($product['product_name']) ?></h2>
      <p><?= nl2br(e($product['description'])) ?></p>

      <ul class="check-list green" style="margin-top:22px">
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          <span><strong>Availability:</strong> <?= $inStock ? 'In stock — ready to disburse.' : 'Currently out of stock.' ?></span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          <span><strong>Status:</strong> <span class="badge <?= $inStock ? 'badge-green' : 'badge-grey' ?>"><?= $inStock ? 'Active' : 'Out of Stock' ?></span></span>
        </li>
      </ul>

      <div class="hero-cta" style="margin-top:28px">
        <a href="apply.php" class="btn btn-orange btn-lg">Apply Now</a>
        <a href="products.php" class="btn btn-ghost btn-lg">← All Products</a>
      </div>
    </div>

    <div class="col-visual">
      <img src="<?= e(product_image_url($product['image'])) ?>" alt="<?= e($product['product_name']) ?>"
           style="width:100%;border-radius:var(--radius-lg);border:1px solid var(--border)" />
      <div class="info-card" style="margin-top:18px">
        <h3>Pricing Summary</h3>
        <div class="ic-sub"><?= e($product['product_name']) ?></div>
        <div class="ic-rows">
          <div class="ic-row"><span class="l">Category</span><span class="r"><?= e($product['category_name'] ?? '—') ?></span></div>
          <div class="ic-row"><span class="l">Original Price</span><span class="r"><?= inr($product['price']) ?></span></div>
          <?php if ($isDiscounted): ?>
            <div class="ic-row"><span class="l">Offer Price</span><span class="r" style="color:var(--orange)"><?= inr($product['discount_price']) ?></span></div>
          <?php endif; ?>
          <div class="ic-row">
            <span class="l">Stock</span>
            <span class="r"><?= $inStock ? (int)$product['stock_quantity'] . ' available' : 'Out of Stock' ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ PRICING ============ -->
<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="section-eyebrow">Pricing</span>
      <h2>Clear &amp; Transparent Pricing</h2>
      <p>No hidden charges — what you see is exactly what you pay.</p>
    </div>
    <div class="why-grid">
      <div class="why-item" style="grid-column:span 1">
        <div class="wi-icon bg-orange">💵</div>
        <h3>Original Price</h3>
        <p style="font-size:22px;font-weight:800;color:var(--ink)">
          <?= inr($product['price']) ?>
        </p>
      </div>
      <?php if ($isDiscounted): ?>
        <div class="why-item">
          <div class="wi-icon bg-green">🏷️</div>
          <h3>Offer Price</h3>
          <p style="font-size:22px;font-weight:800;color:var(--ink)">
            <?= inr($product['discount_price']) ?>
            <span style="font-size:13px;font-weight:600;color:var(--green)">(You save <?= inr((float)$product['price'] - (float)$product['discount_price']) ?>)</span>
          </p>
        </div>
      <?php endif; ?>
      <div class="why-item">
        <div class="wi-icon bg-blue">📦</div>
        <h3><?= $inStock ? 'In Stock' : 'Out of Stock' ?></h3>
        <p><?= $inStock ? (int)$product['stock_quantity'] . ' units available.' : 'Check back soon.' ?></p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/site_footer.php'; ?>