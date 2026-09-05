<?php
/**
 * ============================================================
 * Delete Loan Application
 * ============================================================
 * Handles POST from applications.php / view-application.php.
 * Requires a valid CSRF token and admin session, then deletes
 * the record safely with a prepared statement.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

// Only accept POST submissions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('applications.php');
}

// ---- CSRF check ----
if (!csrf_verify(post('csrf_token'))) {
    set_flash('danger', 'Security token expired. Please try again.');
    redirect('applications.php');
}

// ---- Validate ID ----
$id = post('id');
if ($id === '' || !ctype_digit($id)) {
    set_flash('danger', 'Invalid application ID.');
    redirect('applications.php');
}
$id = (int) $id;

$stmt = $pdo->prepare('DELETE FROM loan_applications WHERE id = ?');
$stmt->execute([$id]);

set_flash('success', 'Application #' . $id . ' deleted successfully.');
redirect('applications.php');
