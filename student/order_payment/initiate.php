<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/paystack_helper.php';
require_student();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    set_flash('error', 'Invalid request.');
    redirect('/student/orders.php');
}

$user = current_user();
$orderId = (int)($_POST['order_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Order not found.');
    redirect('/student/orders.php');
}

$balance = (float)$order['total_amount'] - (float)$order['amount_paid'];
if ($balance <= 0) {
    redirect('/student/order_payment/pay.php?order_id=' . $orderId);
}

$reference = paystack_generate_reference('HAOR');

$logStmt = $pdo->prepare("INSERT INTO order_payments (order_id, user_id, amount, reference, status) VALUES (?, ?, ?, ?, 'pending')");
$logStmt->execute([$orderId, $user['id'], $balance, $reference]);

$response = paystack_initialize($user['email'], $balance, $reference, PAYSTACK_ORDER_CALLBACK_URL, [
    'order_id' => $orderId,
    'user_id' => $user['id'],
    'type' => 'order',
]);

if (!empty($response['status']) && !empty($response['data']['authorization_url'])) {
    header('Location: ' . $response['data']['authorization_url']);
    exit;
}

set_flash('error', 'Could not start payment: ' . ($response['message'] ?? 'Unknown error connecting to Paystack.'));
redirect('/student/order_payment/pay.php?order_id=' . $orderId);
