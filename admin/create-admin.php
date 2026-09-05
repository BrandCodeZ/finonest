<?php
/**
 * ============================================================
 * Create a New Admin (Command-line only)
 * ============================================================
 * SECURE default admin creation method. This script refuses to
 * run over the web (CLI only), so it can safely be left on the
 * server. It stores the password with password_hash() (bcrypt).
 *
 * Usage (XAMPP):
 *   C:\xampp\php\php.exe admin\create-admin.php <username> <email> <password>
 * ============================================================
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Forbidden: this script may only be run from the command line.\n");
}

require_once __DIR__ . '/../config/database.php';

function usage(): void
{
    echo "Usage: php admin\\create-admin.php <username> <email> <password>\n";
    echo "Example: php admin\\create-admin.php admin admin@finonest.com mySecretPass\n";
}

$args = array_slice($argv, 1);
if (count($args) !== 3) {
    usage();
    exit(1);
}

[$username, $email, $password] = $args;

if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Error: username must not be empty and email must be valid.\n");
    exit(1);
}
if (strlen($password) < 8) {
    fwrite(STDERR, "Error: password must be at least 8 characters.\n");
    exit(1);
}

// Prevent duplicates
$stmt = $pdo->prepare('SELECT id FROM admins WHERE username = ? OR email = ? LIMIT 1');
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    fwrite(STDERR, "Error: an admin with this username or email already exists.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO admins (username, email, password) VALUES (?, ?, ?)');
$stmt->execute([$username, $email, $hash]);

echo "Admin \"$username\" created successfully.\n";
echo "Login at: http://localhost/FinTechwebsite/admin/login.php\n";
exit(0);