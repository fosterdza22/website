<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    set_flash('error', 'Invalid request.');
    redirect('/student/shop.php');
}

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, min(20, (int)($_POST['quantity'] ?? 1)));

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_available = 1");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'That product is not available.');
    redirect('/student/shop.php');
}

cart_add($productId, $quantity);
set_flash('success', $product['name'] . ' added to your cart.');

$redirect = ($_POST['redirect'] ?? '') === 'cart' ? '/student/cart.php' : '/student/shop.php';
redirect($redirect);
