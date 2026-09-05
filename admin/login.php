<?php
/**
 * ============================================================
 * Admin Login
 * ============================================================
 * Authenticates an admin using username/email + password.
 * Uses password_verify() against the bcrypt hash in the DB.
 * ============================================================
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in? Go to dashboard.
if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = '';
$oldUser = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldUser   = post('username');
    $userInput = $oldUser;
    $password  = (string) ($_POST['password'] ?? '');
    $token     = (string) ($_POST['csrf_token'] ?? '');

    // 1. CSRF check
    if (!csrf_verify($token)) {
        $error = 'Invalid session token. Please try again.';
    }
    // 2. Basic validation
    elseif ($userInput === '' || $password === '') {
        $error = 'Please enter both username/email and password.';
    } else {
        // 3. Find the admin row (by username OR email)
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$userInput, $userInput]);
        $admin = $stmt->fetch();

        // 4. Verify password
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true); // prevent session fixation
            $_SESSION['admin_id']       = (int) $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email']    = $admin['email'];

            set_flash('success', 'Welcome back, ' . $admin['username'] . '!');
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login | Finonest</title>
  <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg" />
  <link rel="stylesheet" href="css/admin.css" />
</head>
<body class="login-body">
  <div class="login-card">
    <div class="login-logo">F</div>
    <h1>Admin Login</h1>
    <p class="login-sub">Sign in to manage your Finonest products</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php" class="admin-form login-form" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>" />

      <div class="form-group">
        <label for="username">Username or Email</label>
        <input class="form-control" type="text" id="username" name="username"
               value="<?= e($oldUser) ?>" placeholder="Enter your username or email" required />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password"
               placeholder="Enter your password" required />
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
    </form>

    <p style="text-align:center;margin-top:18px;font-size:12.5px;color:var(--muted)">
      Default login: <strong>admin</strong> / <strong>admin123</strong>
    </p>
  </div>
</body>
</html>