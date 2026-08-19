<?php
/**
 * Paystack configuration - TEMPLATE / EXAMPLE FILE.
 *
 * THIS FILE IS COMMITTED TO GIT. DO NOT PUT REAL KEYS HERE.
 *
 * To use this project locally:
 *   1. Copy this file to config/paystack.php
 *   2. Get your keys from https://dashboard.paystack.com/#/settings/developer
 *   3. Replace the placeholders with your OWN keys (test keys while
 *      developing, live keys only once you go live).
 *   4. config/paystack.php is gitignored so your real keys are never
 *      committed to the repository.
 */
define('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('PAYSTACK_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

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
