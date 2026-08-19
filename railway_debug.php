<?php
header('Content-Type: text/plain; charset=utf-8');

function show($name) {
    $fromGetenv = getenv($name);
    $fromServer = $_SERVER[$name] ?? null;
    $fromEnv    = $_ENV[$name] ?? null;
    echo $name . ': getenv=' . var_export($fromGetenv, true)
        . ' $_SERVER=' . var_export($fromServer, true)
        . ' $_ENV=' . var_export($fromEnv, true) . PHP_EOL;
}

echo "PHP SAPI: " . php_sapi_name() . PHP_EOL;
show('DB_HOST');
show('DB_PORT');
show('DB_NAME');
show('DB_USER');
show('DB_PASS');
show('DB_SSL');
show('PAYSTACK_PUBLIC_KEY');
show('PAYSTACK_SECRET_KEY');
echo PHP_EOL;

$serverKeys = array_keys($_SERVER);
sort($serverKeys);
echo "Server keys (" . count($serverKeys) . "):" . PHP_EOL;
echo implode(PHP_EOL, $serverKeys) . PHP_EOL;

$envKeys = array_keys($_ENV);
sort($envKeys);
echo PHP_EOL . "Env keys (" . count($envKeys) . "):" . PHP_EOL;
echo implode(PHP_EOL, $envKeys) . PHP_EOL;
