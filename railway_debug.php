<?php
header('Content-Type: text/plain; charset=utf-8');

echo "PHP SAPI: " . php_sapi_name() . PHP_EOL;
echo "getenv('DB_HOST'): " . var_export(getenv('DB_HOST'), true) . PHP_EOL;
echo "getenv('DB_PORT'): " . var_export(getenv('DB_PORT'), true) . PHP_EOL;
echo "getenv('DB_NAME'): " . var_export(getenv('DB_NAME'), true) . PHP_EOL;
echo "getenv('DB_USER'): " . var_export(getenv('DB_USER'), true) . PHP_EOL;
echo "getenv('DB_PASS') set: " . var_export(getenv('DB_PASS') !== false, true) . PHP_EOL;
echo "getenv('DB_SSL'): " . var_export(getenv('DB_SSL'), true) . PHP_EOL;
echo "getenv('PAYSTACK_PUBLIC_KEY') set: " . var_export(getenv('PAYSTACK_PUBLIC_KEY') !== false, true) . PHP_EOL;
echo PHP_EOL;

// Dump all env var NAMES (values redacted except PORT)
$names = array_keys($_ENV);
sort($names);
echo "Environment variable names (" . count($names) . "):" . PHP_EOL;
foreach ($names as $n) {
    echo "  $n" . PHP_EOL;
}
