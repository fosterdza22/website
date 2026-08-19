<?php
require_once __DIR__ . '/../../includes/functions.php';
require_student();
$pageTitle = 'Complete Payment';
$user = current_user();

$bookingId = (int)($_GET['booking_id'] ?? 0);
$stmt = $pdo->prepare("SELECT b.*, h.name AS hostel_name, rt.type FROM bookings b
    JOIN hostels h ON h.id = b.hostel_id
    JOIN room_types rt ON rt.id = b.room_type_id
    WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$bookingId, $user['id']]);
$booking = $stmt->fetch();

if (!$booking) {
    set_flash('error', 'Booking not found.');
    redirect('/student/bookings.php');
}

$balance = (float)$booking['amount'] - (float)$booking['amount_paid'];

$installments = [];
if ($booking['payment_plan'] === 'installment') {
    $iStmt = $pdo->prepare("SELECT * FROM installment_plans WHERE booking_id = ? ORDER BY installment_number ASC");
    $iStmt->execute([$bookingId]);
    $installments = $iStmt->fetchAll();
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="container py-4">
  <div class="auth-box" style="max-width:560px;">
    <h3 class="mb-1">Complete Your Payment</h3>
    <p class="text-muted"><?= h($booking['hostel_name']) ?> &middot; <span class="text-capitalize"><?= h($booking['type']) ?></span> room &middot; <?= h($booking['academic_year']) ?></p>

    <table class="table">
      <tr><td>Total Fee</td><td class="text-end"><?= money($booking['amount']) ?></td></tr>
      <tr><td>Amount Paid</td><td class="text-end text-success"><?= money($booking['amount_paid']) ?></td></tr>
      <tr class="fw-bold"><td>Balance Due</td><td class="text-end"><?= money($balance) ?></td></tr>
    </table>

    <?php if ($balance <= 0): ?>
      <div class="alert alert-success">This booking is fully paid. Thank you!</div>
      <a href="<?= BASE_URL ?>/student/bookings.php" class="btn btn-primary w-100">Go to My Bookings</a>
    <?php else: ?>

      <?php if ($installments): ?>
        <h6 class="mt-3">Installment Schedule</h6>
        <table class="table table-sm">
          <thead><tr><th>#</th><th>Amount</th><th>Due</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($installments as $inst): ?>
              <tr>
                <td><?= (int)$inst['installment_number'] ?></td>
                <td><?= money($inst['amount_due']) ?></td>
                <td><?= date('d M Y', strtotime($inst['due_date'])) ?></td>
                <td><span class="badge bg-<?= $inst['status'] === 'paid' ? 'success' : 'warning' ?>"><?= h($inst['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <form method="post" action="<?= BASE_URL ?>/student/payment/initiate.php" class="mt-3">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
        <div class="mb-3">
          <label class="form-label">Amount to Pay Now</label>
          <input type="number" step="0.01" min="1" max="<?= h($balance) ?>" class="form-control" name="amount" value="<?= h($balance) ?>" required>
          <div class="form-text">You can pay the full balance, or any partial amount toward it.</div>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-credit-card"></i> Pay with Paystack</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
