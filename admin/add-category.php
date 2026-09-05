<?php
/**
 * ============================================================
 * Add Category
 * ============================================================
 * Creates a new product category. Prevents duplicate names.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Add Category';

$errors    = [];
$old       = ['category_name' => '', 'description' => '', 'status' => '1'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify(post('csrf_token'))) {
        set_flash('danger', 'Invalid session token. Please try again.');
        redirect('add-category.php');
    }

    $old['category_name'] = post('category_name');
    $old['description']   = post('description');
    $old['status']        = isset($_POST['status']) && $_POST['status'] === '0' ? '0' : '1';

    // Validate
    if ($old['category_name'] === '') {
        $errors[] = 'Category name is required.';
    } elseif (strlen($old['category_name']) > 150) {
        $errors[] = 'Category name must be 150 characters or fewer.';
    }

    // Prevent duplicate names
    if ($errors === []) {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE category_name = ? LIMIT 1');
        $stmt->execute([$old['category_name']]);
        if ($stmt->fetch()) {
            $errors[] = 'A category with this name already exists.';
        }
    }

    if ($errors === []) {
        $stmt = $pdo->prepare(
            'INSERT INTO categories (category_name, description, status) VALUES (?, ?, ?)'
        );
        $stmt->execute([
            $old['category_name'],
            $old['description'],
            (int) $old['status'],
        ]);

        set_flash('success', 'Category "' . $old['category_name'] . '" has been created.');
        redirect('categories.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>Add New Category</h2>
      <p>Categories group your products (e.g. Home Loans, Car Loans).</p>
    </div>
    <a href="categories.php" class="btn btn-ghost btn-sm">Back to Categories</a>
  </div>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="add-category.php" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />

    <div class="form-grid">
      <div class="form-group full">
        <label for="category_name">Category Name <span class="req">*</span></label>
        <input class="form-control" type="text" id="category_name" name="category_name"
               value="<?= e($old['category_name']) ?>" placeholder="e.g. Two Wheeler Loans" required />
      </div>

      <div class="form-group full">
        <label for="description">Description</label>
        <textarea class="form-control" id="description" name="description"
                  placeholder="Short description shown in the admin panel"><?= e($old['description']) ?></textarea>
      </div>

      <div class="form-group full">
        <label>Status</label>
        <select class="form-control" name="status">
          <option value="1" <?= $old['status'] === '1' ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= $old['status'] === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
        <span class="form-hint">Inactive categories are hidden from the public frontend.</span>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Create Category</button>
      <a href="categories.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>