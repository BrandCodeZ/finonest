<?php
/**
 * ============================================================
 * Finonest - Shared Helper Functions
 * ============================================================
 * Small reusable helpers used by both the admin panel and the
 * public frontend.
 * ============================================================
 */

/**
 * Escape a value for safe HTML output (XSS protection).
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format a number as Indian Rupees, e.g. 1250000 -> ₹12,50,000
 */
function money($amount): string
{
    $amount = (float) $amount;
    return '₹' . number_format($amount, 2, '.', ',');
}

/**
 * Format an amount as whole Indian Rupees (no decimals).
 */
function inr($amount): string
{
    return '₹' . number_format((float) round((float) $amount), 0, '.', ',');
}

/**
 * Create a CSRF token and store it in the session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token against the session token.
 */
function csrf_verify(?string $token): bool
{
    return isset($_SESSION['csrf_token'], $token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect to a given path and stop execution.
 */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/**
 * Read a trimmed string from POST with an optional fallback.
 */
function post($key, $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

/**
 * Read a trimmed string from GET with an optional fallback.
 */
function get($key, $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $default));
}

/**
 * Flash message helper: set + one-time display.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Allowed loan application statuses and their display colours.
 * Returns an associative map: status => [label, cssClass].
 */
function loan_statuses(): array
{
    return [
        'New'          => ['label' => 'New',            'class' => 'badge-status-new'],
        'Under Review' => ['label' => 'Under Review',   'class' => 'badge-status-review'],
        'Contacted'    => ['label' => 'Contacted',      'class' => 'badge-status-contacted'],
        'Approved'     => ['label' => 'Approved',       'class' => 'badge-status-approved'],
        'Rejected'     => ['label' => 'Rejected',       'class' => 'badge-status-rejected'],
        'Closed'       => ['label' => 'Closed',         'class' => 'badge-status-closed'],
    ];
}

/**
 * Render a badge for a loan application status.
 */
function loan_status_badge(?string $status): string
{
    $map  = loan_statuses();
    $item = $map[$status] ?? ['label' => $status ?: 'New', 'class' => 'badge-status-new'];
    return '<span class="badge ' . e($item['class']) . '">' . e($item['label']) . '</span>';
}

/**
 * Default product image when no image has been uploaded.
 */
function default_product_image(): string
{
    return 'uploads/products/default-product.svg';
}

/**
 * Resolve the URL for a stored product image.
 */
function product_image_url(?string $image): string
{
    if ($image && is_file(__DIR__ . '/../' . $image)) {
        return $image;
    }
    return default_product_image();
}

/**
 * Whitelist of allowed image types for product uploads.
 */
function allowed_image_types(): array
{
    return [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
}

/**
 * Handle a product image upload.
 *
 * @param array  $file      The $_FILES['image'] entry.
 * @param string $oldImage  Existing image path (e.g. uploads/products/xyz.jpg) to replace.
 *
 * @return string|null  New stored relative path, or null when no file uploaded.
 * @throws RuntimeException When the upload is invalid or dangerous.
 */
function upload_product_image(array $file, ?string $oldImage = null): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no new image chosen — keep existing
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed (error code ' . (int) $file['error'] . ').');
    }

    $tmpPath     = $file['tmp_name'];
    $originalName = $file['name'];
    $maxSize     = 2 * 1024 * 1024; // 2 MB

    if (filesize($tmpPath) > $maxSize) {
        throw new RuntimeException('Image must be 2 MB or smaller.');
    }

    // Detect real MIME type from file contents, not the browser header.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $allowed = allowed_image_types();
    if (!isset($allowed[$mime])) {
        throw new RuntimeException(
            'Only JPG, PNG, GIF or WEBP images are allowed. Detected: ' . ($mime ?: 'unknown')
        );
    }

    // Build a unique filename.
    $ext      = $allowed[$mime];
    $newName  = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
    $newName  = mb_substr($newName, 0, 90) . '.' . $ext;
    $destDir  = __DIR__ . '/../uploads/products/';

    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $destPath = $destDir . $newName;
    if (!move_uploaded_file($tmpPath, $destPath)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }

    // Replace the old image: only ever delete files inside our uploads folder,
    // and never the default placeholder image.
    if ($oldImage && $oldImage !== default_product_image()) {
        $safeOld = __DIR__ . '/../' . ltrim($oldImage, '/');
        $safeOld = realpath($safeOld);
        $uploadsReal = realpath($destDir);
        if ($safeOld !== false && $uploadsReal !== false
            && strpos($safeOld, $uploadsReal) === 0
            && is_file($safeOld)) {
            @unlink($safeOld);
        }
    }

    return 'uploads/products/' . $newName;
}