<?php
/**
 * ============================================================
 * Update Loan Application (Status + Admin Notes)
 * ============================================================
 * Handles POST from view-application.php. Validates the ID,
 * CSRF and the new status, then updates the record with a
 * prepared statement. updated_at is managed by the database.
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

// ---- Validate status against the whitelist ----
$newStatus = post('application_status');
$statuses  = loan_statuses();
if (!isset($statuses[$newStatus])) {
    set_flash('danger', 'Invalid application status.');
    redirect('view-application.php?id=' . $id);
}

// ---- Sanitize notes ----
$notes = trim((string) ($_POST['admin_notes'] ?? ''));

$stmt = $pdo->prepare(
    'UPDATE loan_applications
        SET application_status = ?, admin_notes = ?
      WHERE id = ?'
);
$stmt->execute([$newStatus, $notes !== '' ? $notes : null, $id]);

set_flash('success', 'Application #' . $id . ' updated successfully.');
redirect('view-application.php?id=' . $id);
