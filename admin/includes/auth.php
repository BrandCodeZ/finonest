<?php
/**
 * ============================================================
 * Finonest - Admin Authentication Guard
 * ============================================================
 * Include this file at the top of EVERY protected admin page.
 *
 * - Starts a secure PHP session.
 * - Requires the PDO connection.
 * - Redirects unauthenticated visitors to login.php.
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Deny access if the user is not logged in.
if (empty($_SESSION['admin_id'])) {
    redirect('login.php');
}