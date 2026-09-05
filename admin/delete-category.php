<?php
/**
 * ============================================================
 * Delete Category
 * ============================================================
 * Secure deletion via POST only. If products belong to this
 * category, deletion is blocked with a friendly message.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('warning', 'Invalid request.');
    redirect('categories.php');
}

if (!csrf_verify(post('csrf_token'))) {
    set_flash('danger', 'Invalid session token. Please try again.');
    redirect('categories.php');
}

$id = (int) post('id');
if ($id <= 0) {
    set_flash('danger', 'Invalid category.');
    redirect('categories.php');
}

// Does the category exist?
$stmt = $pdo->prepare('SELECT category_name FROM categories WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    set_flash('danger', 'Category not found.');
    redirect('categories.php');
}

// Are there products using this category?
$stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
$stmt->execute([$id]);
$productCount = (int) $stmt->fetchColumn();

if ($productCount > 0) {
    set_flash(
        'danger',
        'Cannot delete "' . $category['category_name'] . '" because ' . $productCount .
        ' product(s) are linked to it. Move those products to another category first.'
    );
    redirect('categories.php');
}

// Safe to delete
$stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
$stmt->execute([$id]);

set_flash('success', 'Category "' . $category['category_name'] . '" has been deleted.');
redirect('categories.php');