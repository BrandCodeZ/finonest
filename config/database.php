<?php
/**
 * ============================================================
 * Finonest - Database Connection (PDO)
 * ============================================================
 * Opens a single PDO connection usable across the project.
 * Uses prepared statements everywhere to prevent SQL injection.
 * ============================================================
 */

declare(strict_types=1);

$dbConfig = require __DIR__ . '/config.php';

try {
    $dsn = 'mysql:host=' . $dbConfig['DB_HOST'] . ';dbname=' . $dbConfig['DB_NAME'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $dbConfig['DB_USER'], $dbConfig['DB_PASS'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Stop safely without leaking credentials.
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}