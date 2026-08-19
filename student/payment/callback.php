<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/paystack_helper.php';
require_student();

$pageTitle = 'Payment Result';
$reference = $_GET['reference'] ?? $_GET['trxref'] ?? '';

$result = ['success' => false, 'message' => 'No payment reference supplied.'];

if ($reference) {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE reference = ?");
    $stmt->execute([$reference]);
    $paymentRow = $stmt->fetch();

    if (!$paymentRow) {
        $result['message'] = 'Payment record not found.';
    } else {
        $verify = paystack_verify($reference);

        if (!empty($verify['status']) && ($verify['data']['status'] ?? '') === 'success') {
            $paidAmount = ((float)$verify['data']['amount']) / 100; // Paystack returns pesewas
            $channel = $verify['data']['channel'] ?? null;

            if ($paymentRow['status'] !== 'success') {
                $pdo->prepare("UPDATE payments SET status = 'success', amount = ?, channel = ?, paid_at = NOW(), gateway_response = ? WHERE id = ?")
                    ->execute([$paidAmount, $channel, $verify['data']['gateway_response'] ?? '', $paymentRow['id']]);
                recalc_booking_payment_status($pdo, $paymentRow['booking_id']);

                // Mark the earliest unpaid installment(s) covered by this payment as paid
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

            $result = ['success' => true, 'message' => 'Payment successful! Thank you.', 'amount' => $paidAmount, 'booking_id' => $paymentRow['booking_id']];
        } else {
            $pdo->prepare("UPDATE payments SET status = 'failed', gateway_response = ? WHERE id = ? AND status = 'pending'")
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
      <p class="text-muted">You paid <?= money($result['amount']) ?> towards your booking.</p>
      <a href="<?= BASE_URL ?>/student/bookings.php" class="btn btn-primary w-100 mt-2">View My Bookings</a>
    <?php else: ?>
      <i class="bi bi-x-circle-fill text-danger" style="font-size:3rem;"></i>
      <h3 class="mt-3">Payment Not Completed</h3>
      <p class="text-muted"><?= h($result['message']) ?></p>
      <a href="<?= BASE_URL ?>/student/bookings.php" class="btn btn-outline-primary w-100 mt-2">Back to My Bookings</a>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
