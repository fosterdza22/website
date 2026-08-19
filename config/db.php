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
function env_value($name) {
    $v = getenv($name);
    if ($v !== false && $v !== '') return $v;
    if (isset($_SERVER[$name]) && $_SERVER[$name] !== '') return $_SERVER[$name];
    if (isset($_ENV[$name]) && $_ENV[$name] !== '') return $_ENV[$name];
    return false;
}

$DB_HOST = env_value('DB_HOST') ?: 'localhost';
$DB_PORT = env_value('DB_PORT') ?: '3306';
$DB_NAME = env_value('DB_NAME') ?: 'hostel_agency';
$DB_USER = env_value('DB_USER') ?: 'root';
$DB_PASS = env_value('DB_PASS') ?: '';
$DB_SSL  = env_value('DB_SSL') ?: '0';

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
