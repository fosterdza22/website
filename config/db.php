<?php
/**
 * Database connection (PDO)
 *
 * Credentials come from environment variables first (used on Railway /
 * hosted deployments), falling back to local XAMPP defaults.
 *
 *   DB_HOST   - server host (e.g. gateway01.eu-central-1.prod.aws.tidbcloud.com)
 *   DB_PORT   - server port (TiDB Cloud uses 4000; local MySQL uses 3306)
 *   DB_NAME   - database name (default: hostel_agency)
 *   DB_USER   - username (e.g. 2rCWfbneuxXyXZn.root)
 *   DB_PASS   - password
 *   DB_SSL    - set to 1 to force TLS (required by TiDB Cloud Serverless)
 */
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_PORT = getenv('DB_PORT') ?: '3306';
$DB_NAME = getenv('DB_NAME') ?: 'hostel_agency';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_SSL  = getenv('DB_SSL') ?: '0';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

if ($DB_SSL === '1') {
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_PEER_NAME]   = false;
}

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        $options
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}
