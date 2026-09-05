<?php
/**
 * ============================================================
 * Edit Category
 * ============================================================
 * Updates an existing category. Prevents duplicate names
 * (excluding the category being edited).
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Edit Category';

$id = (int) get('id');
if ($id <= 0) {
    set_flash('danger', 'Invalid category.');
    redirect('categories.php');
}

// Load current category
$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    set_flash('danger', 'Category not found.');
    redirect('categories.php');
}

$errors = [];
$old = [
    'category_name' => $category['category_name'],
    'description'   => $category['description'],
    'status'        => (string) $category['status'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify(post('csrf_token'))) {
        set_flash('danger', 'Invalid session token. Please try again.');
        redirect('edit-category.php?id=' . $id);
    }

    $old['category_name'] = post('category_name');
    $old['description']   = post('description');
    $old['status']        = isset($_POST['status']) && $_POST['status'] === '0' ? '0' : '1';

    if ($old['category_name'] === '') {
        $errors[] = 'Category name is required.';
    } elseif (strlen($old['category_name']) > 150) {
        $errors[] = 'Category name must be 150 characters or fewer.';
    }

    // Duplicate check excluding this category
    if ($errors === []) {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE category_name = ? AND id != ? LIMIT 1');
        $stmt->execute([$old['category_name'], $id]);
        if ($stmt->fetch()) {
            $errors[] = 'A category with this name already exists.';
        }
    }

    if ($errors === []) {
        $stmt = $pdo->prepare(
            'UPDATE categories SET category_name = ?, description = ?, status = ? WHERE id = ?'
        );
        $stmt->execute([
            $old['category_name'],
            $old['description'],
            (int) $old['status'],
            $id,
        ]);

        set_flash('success', 'Category updated successfully.');
        redirect('categories.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>Edit Category</h2>
      <p>Update the name, description or status of this category.</p>
    </div>
    <a href="categories.php" class="btn btn-ghost btn-sm">Back to Categories</a>
  </div>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="post" action="edit-category.php?id=<?= $id ?>" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />

    <div class="form-grid">
      <div class="form-group full">
        <label for="category_name">Category Name <span class="req">*</span></label>
        <input class="form-control" type="text" id="category_name" name="category_name"
               value="<?= e($old['category_name']) ?>" required />
      </div>

      <div class="form-group full">
        <label for="description">Description</label>
        <textarea class="form-control" id="description" name="description"><?= e($old['description']) ?></textarea>
      </div>

      <div class="form-group full">
        <label>Status</label>
        <select class="form-control" name="status">
          <option value="1" <?= $old['status'] === '1' ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= $old['status'] === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Update Category</button>
      <a href="categories.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>