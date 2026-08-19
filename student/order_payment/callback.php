<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/paystack_helper.php';
require_student();

$pageTitle = 'Payment Result';
$reference = $_GET['reference'] ?? $_GET['trxref'] ?? '';

$result = ['success' => false, 'message' => 'No payment reference supplied.'];

if ($reference) {
    $stmt = $pdo->prepare("SELECT * FROM order_payments WHERE reference = ?");
    $stmt->execute([$reference]);
    $paymentRow = $stmt->fetch();

    if (!$paymentRow) {
        $result['message'] = 'Payment record not found.';
    } else {
        $verify = paystack_verify($reference);

        if (!empty($verify['status']) && ($verify['data']['status'] ?? '') === 'success') {
            $paidAmount = ((float)$verify['data']['amount']) / 100;
            $channel = $verify['data']['channel'] ?? null;

            if ($paymentRow['status'] !== 'success') {
                $pdo->prepare("UPDATE order_payments SET status = 'success', amount = ?, channel = ?, paid_at = NOW(), gateway_response = ? WHERE id = ?")
                    ->execute([$paidAmount, $channel, $verify['data']['gateway_response'] ?? '', $paymentRow['id']]);
                recalc_order_payment_status($pdo, $paymentRow['order_id']);
            }

            $result = ['success' => true, 'message' => 'Payment successful!', 'amount' => $paidAmount, 'order_id' => $paymentRow['order_id']];
        } else {
            $pdo->prepare("UPDATE order_payments SET status = 'failed', gateway_response = ? WHERE id = ? AND status = 'pending'")
                ->execute([$verify['data']['gateway_response'] ?? ($verify['message'] ?? 'failed'), $paymentRow['id']]);
            $result['message'] = 'Payment was not successful: ' . ($verify['data']['gateway_response'] ?? $verify['message'] ?? 'Unknown reason');
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="container py-5">
  <div class="auth-box text-center">
    <?php if ($result['success']): ?>
      <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
      <h3 class="mt-3">Payment Successful!</h3>
      <p class="text-muted">You paid <?= money($result['amount']) ?> for your order. It's now being processed for delivery.</p>
      <a href="<?= BASE_URL ?>/student/orders.php" class="btn btn-primary w-100 mt-2">View My Orders</a>
    <?php else: ?>
      <i class="bi bi-x-circle-fill text-danger" style="font-size:3rem;"></i>
      <h3 class="mt-3">Payment Not Completed</h3>
      <p class="text-muted"><?= h($result['message']) ?></p>
      <a href="<?= BASE_URL ?>/student/orders.php" class="btn btn-outline-primary w-100 mt-2">Back to My Orders</a>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
