<?php
/**
 * Paystack configuration.
 *
 * Keys are resolved in this order:
 *   1. config/paystack.local.php   (gitignored - use for local development)
 *   2. Environment variables PAYSTACK_PUBLIC_KEY / PAYSTACK_SECRET_KEY
 *      (set these in Railway Dashboard / your host)
 *
 * NEVER commit real secret keys to a public repository.
 */
$localCfg = __DIR__ . '/paystack.local.php';
if (is_file($localCfg)) {
    require_once $localCfg;
}

if (!function_exists('env_value')) {
    function env_value($name) {
        $v = getenv($name);
        if ($v !== false && $v !== '') return $v;
        if (isset($_SERVER[$name]) && $_SERVER[$name] !== '') return $_SERVER[$name];
        if (isset($_ENV[$name]) && $_ENV[$name] !== '') return $_ENV[$name];
        return false;
    }
}

if (!defined('PAYSTACK_PUBLIC_KEY')) {
    define('PAYSTACK_PUBLIC_KEY', env_value('PAYSTACK_PUBLIC_KEY') ?: '');
}
if (!defined('PAYSTACK_SECRET_KEY')) {
    define('PAYSTACK_SECRET_KEY', env_value('PAYSTACK_SECRET_KEY') ?: '');
}

// Currency Paystack should charge in (Paystack Ghana supports GHS)
define('PAYSTACK_CURRENCY', 'GHS');

// Auto-built callback base so it works whether the app lives at the domain
// root or in a subfolder. Individual payment flows append their own path.
require_once __DIR__ . '/app.php';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('PAYSTACK_SITE_URL', "{$scheme}://{$host}" . BASE_URL);
define('PAYSTACK_CALLBACK_URL', PAYSTACK_SITE_URL . '/student/payment/callback.php');
define('PAYSTACK_ORDER_CALLBACK_URL', PAYSTACK_SITE_URL . '/student/order_payment/callback.php');
