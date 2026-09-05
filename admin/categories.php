<?php
/**
 * ============================================================
 * View Categories
 * ============================================================
 * Lists all categories with product counts, edit & delete actions.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Categories';

$stmt = $pdo->query(
    'SELECT c.*, 
            (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c
     ORDER BY c.created_at DESC'
);
$categories = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>All Categories</h2>
      <p><?= count($categories) ?> categor<?= count($categories) === 1 ? 'y' : 'ies' ?> total.</p>
    </div>
    <a href="add-category.php" class="btn btn-primary btn-sm">+ Add Category</a>
  </div>

  <?php if ($categories): ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Description</th>
            <th>Products</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td class="t-muted">#<?= (int)$cat['id'] ?></td>
              <td class="t-name"><?= e($cat['category_name']) ?></td>
              <td class="t-muted"><?= e(mb_strimwidth((string)$cat['description'], 0, 70, '…')) ?></td>
              <td><span class="badge badge-stock-ok"><?= (int)$cat['product_count'] ?></span></td>
              <td>
                <?php if ($cat['status'] == 1): ?>
                  <span class="badge badge-active">Active</span>
                <?php else: ?>
                  <span class="badge badge-inactive">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="t-muted"><?= e(date('d M Y', strtotime($cat['created_at']))) ?></td>
              <td>
                <div class="actions">
                  <a class="action-btn edit" href="edit-category.php?id=<?= (int)$cat['id'] ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    Edit
                  </a>
                  <form method="post" action="delete-category.php" style="display:inline"
                        onsubmit="return confirm('Delete this category permanently?');">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />
                    <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>" />
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
  <?php else: ?>
    <div class="empty-state">
      <div class="es-icon">🗂️</div>
      <h3>No categories yet</h3>
      <p>Create a category before adding products.</p>
      <a href="add-category.php" class="btn btn-primary" style="margin-top:16px">+ Add Category</a>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>