<?php
/**
 * ============================================================
 * Add Product
 * ============================================================
 * Creates a new product with optional image upload.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Add Product';

// Load categories for the <select>
$categories = $pdo->query('SELECT * FROM categories ORDER BY category_name')->fetchAll();

if ($categories === []) {
    set_flash('warning', 'Please create at least one category before adding products.');
    redirect('categories.php');
}

$errors = [];
$old = [
    'product_name'   => '',
    'category_id'    => '',
    'description'    => '',
    'price'          => '',
    'discount_price' => '',
    'stock_quantity' => '',
    'status'         => '1',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify(post('csrf_token'))) {
        set_flash('danger', 'Invalid session token. Please try again.');
        redirect('add-product.php');
    }

    $old['product_name']   = post('product_name');
    $old['category_id']    = post('category_id');
    $old['description']    = post('description');
    $old['price']          = post('price');
    $old['discount_price'] = post('discount_price');
    $old['stock_quantity'] = post('stock_quantity');
    $old['status']         = isset($_POST['status']) && $_POST['status'] === '0' ? '0' : '1';

    // ---- Validation ----
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

    // ---- Image upload (validates type + size) ----
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $imagePath = upload_product_image($_FILES['image']);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }

    // ---- Insert ----
    if ($errors === []) {
        $stmt = $pdo->prepare(
            'INSERT INTO products
                (product_name, category_id, description, price, discount_price, image, stock_quantity, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $old['product_name'],
            (int) $old['category_id'],
            $old['description'],
            (float) $old['price'],
            $old['discount_price'] !== '' ? (float) $old['discount_price'] : null,
            $imagePath,
            (int) $old['stock_quantity'],
            (int) $old['status'],
        ]);

        set_flash('success', 'Product "' . $old['product_name'] . '" has been created and is now visible on the website.');
        redirect('products.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>Add New Product</h2>
      <p>Add a product and it automatically appears on the public website.</p>
    </div>
    <a href="products.php" class="btn btn-ghost btn-sm">Back to Products</a>
  </div>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="add-product.php" class="admin-form" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />

    <div class="form-grid">
      <div class="form-group full">
        <label for="product_name">Product Name <span class="req">*</span></label>
        <input class="form-control" type="text" id="product_name" name="product_name"
               value="<?= e($old['product_name']) ?>" placeholder="e.g. Home Loan" required />
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
        <span class="form-hint">Only Active products appear on the website.</span>
      </div>

      <div class="form-group">
        <label for="price">Price (₹) <span class="req">*</span></label>
        <input class="form-control" type="number" step="0.01" min="0" id="price" name="price"
               value="<?= e($old['price']) ?>" placeholder="e.g. 100000" required />
      </div>

      <div class="form-group">
        <label for="discount_price">Discount Price (₹)</label>
        <input class="form-control" type="number" step="0.01" min="0" id="discount_price" name="discount_price"
               value="<?= e($old['discount_price']) ?>" placeholder="Optional — must be lower than price" />
        <span class="form-hint">Leave empty for no discount.</span>
      </div>

      <div class="form-group">
        <label for="stock_quantity">Stock Quantity <span class="req">*</span></label>
        <input class="form-control" type="number" min="0" step="1" id="stock_quantity" name="stock_quantity"
               value="<?= e($old['stock_quantity']) ?>" placeholder="e.g. 50" required />
      </div>

      <div class="form-group full">
        <label for="image">Product Image</label>
        <div class="file-box">
          <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" />
          <span class="form-hint">JPG, PNG, GIF or WEBP. Max 2 MB.</span>
        </div>
      </div>

      <div class="form-group full">
        <label for="description">Product Description</label>
        <textarea class="form-control" id="description" name="description"
                  placeholder="Short description shown on the website"><?= e($old['description']) ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save Product</button>
      <a href="products.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>