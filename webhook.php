<?php
/**
 * Paystack Webhook
 * Configure this URL in your Paystack dashboard: Settings -> API Keys & Webhooks
 *   https://yourdomain.com/hostel-agency/webhook.php
 *
 * This provides a reliable server-to-server payment confirmation even if a
 * student closes their browser before returning from checkout.
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/paystack_helper.php';

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

// Verify the request genuinely came from Paystack
$expectedSignature = hash_hmac('sha512', $payload, PAYSTACK_SECRET_KEY);
if (!$signature || !hash_equals($expectedSignature, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}

$event = json_decode($payload, true);
if (!$event || ($event['event'] ?? '') !== 'charge.success') {
    http_response_code(200);
    exit('Ignored');
}

$data = $event['data'];
$reference = $data['reference'] ?? '';
$paidAmount = ((float)($data['amount'] ?? 0)) / 100;
$channel = $data['channel'] ?? null;
$gatewayResponse = $data['gateway_response'] ?? '';

if (!$reference) {
    http_response_code(200);
    exit('No reference');
}

// Try booking payments first
$stmt = $pdo->prepare("SELECT * FROM payments WHERE reference = ?");
$stmt->execute([$reference]);
$paymentRow = $stmt->fetch();

if ($paymentRow) {
    if ($paymentRow['status'] !== 'success') {
        $pdo->prepare("UPDATE payments SET status = 'success', amount = ?, channel = ?, paid_at = NOW(), gateway_response = ? WHERE id = ?")
            ->execute([$paidAmount, $channel, $gatewayResponse, $paymentRow['id']]);
        recalc_booking_payment_status($pdo, $paymentRow['booking_id']);

        $instStmt = $pdo->prepare("SELECT * FROM installment_plans WHERE booking_id = ? AND status = 'unpaid' ORDER BY installment_number ASC");
        $instStmt->execute([$paymentRow['booking_id']]);
        $remaining = $paidAmount;
        foreach ($instStmt->fetchAll() as $inst) {
            if ($remaining <= 0) break;
            if ($remaining + 0.01 >= (float)$inst['amount_due']) {
                $pdo->prepare("UPDATE installment_plans SET status = 'paid', paid_at = NOW() WHERE id = ?")->execute([$inst['id']]);
                $remaining -= (float)$inst['amount_due'];
            }
        }
    }
    http_response_code(200);
    exit('OK - booking payment processed');
}

// Otherwise try order payments
$stmt = $pdo->prepare("SELECT * FROM order_payments WHERE reference = ?");
$stmt->execute([$reference]);
$orderPaymentRow = $stmt->fetch();

if ($orderPaymentRow) {
    if ($orderPaymentRow['status'] !== 'success') {
        $pdo->prepare("UPDATE order_payments SET status = 'success', amount = ?, channel = ?, paid_at = NOW(), gateway_response = ? WHERE id = ?")
            ->execute([$paidAmount, $channel, $gatewayResponse, $orderPaymentRow['id']]);
        recalc_order_payment_status($pdo, $orderPaymentRow['order_id']);
    }
    http_response_code(200);
    exit('OK - order payment processed');
}

http_response_code(200);
exit('No matching payment record');
