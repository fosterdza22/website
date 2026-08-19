<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/paystack_helper.php';
require_student();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    set_flash('error', 'Invalid request.');
    redirect('/student/bookings.php');
}

$user = current_user();
$bookingId = (int)($_POST['booking_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
$stmt->execute([$bookingId, $user['id']]);
$booking = $stmt->fetch();

if (!$booking) {
    set_flash('error', 'Booking not found.');
    redirect('/student/bookings.php');
}

$balance = (float)$booking['amount'] - (float)$booking['amount_paid'];
if ($amount <= 0 || $amount > $balance + 0.01) {
    set_flash('error', 'Please enter a valid payment amount.');
    redirect('/student/payment/pay.php?booking_id=' . $bookingId);
}

$reference = paystack_generate_reference('HABK');

$logStmt = $pdo->prepare("INSERT INTO payments (booking_id, user_id, amount, reference, status) VALUES (?, ?, ?, ?, 'pending')");
$logStmt->execute([$bookingId, $user['id'], $amount, $reference]);

$emailForCheckout = current_user()['email'];
$response = paystack_initialize($emailForCheckout, $amount, $reference, PAYSTACK_CALLBACK_URL, [
    'booking_id' => $bookingId,
    'user_id' => $user['id'],
    'type' => 'booking',
]);

if (!empty($response['status']) && !empty($response['data']['authorization_url'])) {
    header('Location: ' . $response['data']['authorization_url']);
    exit;
}

set_flash('error', 'Could not start payment: ' . ($response['message'] ?? 'Unknown error connecting to Paystack.'));
redirect('/student/payment/pay.php?booking_id=' . $bookingId);
