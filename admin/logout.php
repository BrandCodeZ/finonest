<?php
/**
 * ============================================================
 * Admin Logout
 * ============================================================
 * Destroys the admin session and redirects to the login page.
 * ============================================================
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data securely.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

header('Location: login.php');
exit;