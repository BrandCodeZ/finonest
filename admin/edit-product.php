<?php
/**
 * ============================================================
 * Edit Product
 * ============================================================
 * Updates a product. If a new image is uploaded it replaces the
 * old one (and the old file is deleted safely). Otherwise the
 * existing image is kept.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Edit Product';

$id = (int) get('id');
if ($id <= 0) {
    set_flash('danger', 'Invalid product.');
    redirect('products.php');
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('danger', 'Product not found.');
    redirect('products.php');
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();

$errors = [];
$old = [
    'product_name'   => $product['product_name'],
    'category_id'    => (string) $product['category_id'],
    'description'    => $product['description'],
    'price'          => rtrim(rtrim(number_format((float)$product['price'], 2, '.', ''), '0'), '.'),
    'discount_price' => $product['discount_price'] !== null
        ? rtrim(rtrim(number_format((float)$product['discount_price'], 2, '.', ''), '0'), '.')
        : '',
    'stock_quantity' => (string) $product['stock_quantity'],
    'status'         => (string) $product['status'],
];

// Trim trailing .00 -> keep simple decimal strings for the inputs.
if ($old['price'] === '') $old['price'] = (string)$product['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify(post('csrf_token'))) {
        set_flash('danger', 'Invalid session token. Please try again.');
        redirect('edit-product.php?id=' . $id);
    }

    $old['product_name']   = post('product_name');
    $old['category_id']    = post('category_id');
    $old['description']    = post('description');
    $old['price']          = post('price');
    $old['discount_price'] = post('discount_price');
    $old['stock_quantity'] = post('stock_quantity');
    $old['status']         = isset($_POST['status']) && $_POST['status'] === '0' ? '0' : '1';

    // ---- Validation (same rules as add-product.php) ----
    if ($old['product_name'] === '') {
        $errors[] = 'Product name is required.';
    } elseif (strlen($old['product_name']) > 200) {
        $errors[] = 'Product name must be 200 characters or fewer.';
    }

    if ($old['category_id'] === '' || !ctype_digit($old['category_id'])) {
        $errors[] = 'Please choose a valid category.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $old['category_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'The selected category does not exist.';
        }
    }

    if ($old['price'] === '' || !is_numeric($old['price']) || (float) $old['price'] < 0) {
        $errors[] = 'Price must be a valid non-negative number.';
    }

    if ($old['discount_price'] !== '') {
        if (!is_numeric($old['discount_price']) || (float) $old['discount_price'] < 0) {
            $errors[] = 'Discount price must be a valid non-negative number.';
        } elseif (is_numeric($old['price']) && (float) $old['discount_price'] >= (float) $old['price']) {
            $errors[] = 'Discount price must be lower than the original price.';
        }
    }

    if ($old['stock_quantity'] === '' || !ctype_digit($old['stock_quantity'])) {
        $errors[] = 'Stock quantity must be a whole number.';
    }

    // ---- Optional new image ----
    $newImage = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $newImage = upload_product_image($_FILES['image'], $product['image']);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }

    // ---- Update ----
    if ($errors === []) {
        $stmt = $pdo->prepare(
            'UPDATE products SET
                product_name   = ?,
                category_id    = ?,
                description    = ?,
                price          = ?,
                discount_price = ?,
                image          = ?,
                stock_quantity = ?,
                status         = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $old['product_name'],
            (int) $old['category_id'],
            $old['description'],
            (float) $old['price'],
            $old['discount_price'] !== '' ? (float) $old['discount_price'] : null,
            $newImage,
            (int) $old['stock_quantity'],
            (int) $old['status'],
            $id,
        ]);

        set_flash('success', 'Product "' . $old['product_name'] . '" has been updated.');
        redirect('products.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>Edit Product</h2>
      <p>Update product details. Leave the image field empty to keep the current image.</p>
    </div>
    <a href="products.php" class="btn btn-ghost btn-sm">Back to Products</a>
  </div>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="edit-product.php?id=<?= $id ?>" class="admin-form" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />

    <div class="form-grid">
      <div class="form-group full">
        <label for="product_name">Product Name <span class="req">*</span></label>
        <input class="form-control" type="text" id="product_name" name="product_name"
               value="<?= e($old['product_name']) ?>" required />
      </div>

      <div class="form-group">
        <label for="category_id">Category <span class="req">*</span></label>
        <select class="form-control" id="category_id" name="category_id" required>
          <option value="">— Select Category —</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= $old['category_id'] == $cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="status">Status <span class="req">*</span></label>
        <select class="form-control" id="status" name="status">
          <option value="1" <?= $old['status'] === '1' ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= $old['status'] === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>

      <div class="form-group">
        <label for="price">Price (₹) <span class="req">*</span></label>
        <input class="form-control" type="number" step="0.01" min="0" id="price" name="price"
               value="<?= e($old['price']) ?>" required />
      </div>

      <div class="form-group">
        <label for="discount_price">Discount Price (₹)</label>
        <input class="form-control" type="number" step="0.01" min="0" id="discount_price" name="discount_price"
               value="<?= e($old['discount_price']) ?>" />
      </div>

      <div class="form-group">
        <label for="stock_quantity">Stock Quantity <span class="req">*</span></label>
        <input class="form-control" type="number" min="0" step="1" id="stock_quantity" name="stock_quantity"
               value="<?= e($old['stock_quantity']) ?>" required />
      </div>

      <div class="form-group full">
        <label for="image">Product Image</label>
        <div class="file-box">
          <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" />
          <span class="form-hint">JPG, PNG, GIF or WEBP. Max 2 MB. Leave empty to keep the current image.</span>
        </div>
        <?php if (product_image_url($product['image']) !== default_product_image()): ?>
          <div class="img-preview">
            <img src="../<?= e(product_image_url($product['image'])) ?>" alt="Current image" />
          </div>
        <?php endif; ?>
      </div>

      <div class="form-group full">
        <label for="description">Product Description</label>
        <textarea class="form-control" id="description" name="description"><?= e($old['description']) ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Update Product</button>
      <a href="products.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>