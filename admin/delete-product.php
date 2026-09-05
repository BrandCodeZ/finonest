<?php
/**
 * ============================================================
 * Delete Product
 * ============================================================
 * Secure deletion via POST only. Removes the database row and
 * deletes the associated image file from uploads/products/.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('warning', 'Invalid request.');
    redirect('products.php');
}

if (!csrf_verify(post('csrf_token'))) {
    set_flash('danger', 'Invalid session token. Please try again.');
    redirect('products.php');
}

$id = (int) post('id');
if ($id <= 0) {
    set_flash('danger', 'Invalid product.');
    redirect('products.php');
}

// Load product (need image filename)
$stmt = $pdo->prepare('SELECT product_name, image FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('danger', 'Product not found.');
    redirect('products.php');
}

// Delete the database row
$stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
$stmt->execute([$id]);

// Delete the image file (guarded: only inside uploads/products, never the placeholder)
$image = $product['image'];
if ($image && $image !== default_product_image()) {
    $target    = __DIR__ . '/../' . ltrim($image, '/');
    $realTarget    = realpath($target);
    $uploadsReal   = realpath(__DIR__ . '/../uploads/products/');
    if ($realTarget !== false && $uploadsReal !== false
        && strpos($realTarget, $uploadsReal) === 0
        && is_file($realTarget)) {
        @unlink($realTarget);
    }
}

set_flash('success', 'Product "' . $product['product_name'] . '" has been deleted.');
redirect('products.php');