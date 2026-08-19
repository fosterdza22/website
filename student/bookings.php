<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'My Bookings';
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id']) && verify_csrf($_POST['csrf'] ?? '')) {
    $bookingId = (int)$_POST['cancel_booking_id'];
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $user['id']]);
    $booking = $stmt->fetch();

    if ($booking && $booking['status'] !== 'cancelled' && (float)$booking['amount_paid'] > 0) {
        set_flash('error', 'This booking has a payment on record — please contact an administrator to cancel it.');
    } elseif ($booking && $booking['status'] !== 'cancelled') {
        $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?")->execute([$bookingId]);
        $pdo->prepare("UPDATE room_types SET booked_rooms = GREATEST(booked_rooms - 1, 0) WHERE id = ?")->execute([$booking['room_type_id']]);
        set_flash('success', 'Booking cancelled.');
    }
    redirect('/student/bookings.php');
}

$stmt = $pdo->prepare("SELECT b.*, h.name AS hostel_name, rt.type FROM bookings b
    JOIN hostels h ON h.id = b.hostel_id
    JOIN room_types rt ON rt.id = b.room_type_id
    WHERE b.user_id = ? ORDER BY b.booked_at DESC");
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>My Bookings</h2>
  <p class="text-muted">Track your current and past hostel reservations.</p>

  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead class="table-light">
        <tr><th>Hostel</th><th>Room Type</th><th>Year</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Booking Status</th><th>Payment</th><th>Booked On</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (!$bookings): ?>
          <tr><td colspan="10" class="text-center text-muted py-4">No bookings yet. <a href="<?= BASE_URL ?>/student/hostels.php">Browse hostels</a>.</td></tr>
        <?php endif; ?>
        <?php foreach ($bookings as $b):
          $badge = ['pending' => 'warning', 'confirmed' => 'success', 'cancelled' => 'danger'][$b['status']];
          $payBadge = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'][$b['payment_status']];
          $balance = (float)$b['amount'] - (float)$b['amount_paid'];
        ?>
          <tr>
            <td><?= h($b['hostel_name']) ?></td>
            <td class="text-capitalize"><?= h($b['type']) ?></td>
            <td><?= h($b['academic_year']) ?></td>
            <td><?= money($b['amount']) ?></td>
            <td class="text-success"><?= money($b['amount_paid']) ?></td>
            <td class="<?= $balance > 0 ? 'text-danger' : '' ?>"><?= money($balance) ?></td>
            <td><span class="badge bg-<?= $badge ?> text-capitalize"><?= h($b['status']) ?></span></td>
            <td><span class="badge bg-<?= $payBadge ?> text-capitalize"><?= h($b['payment_status']) ?></span></td>
            <td><?= date('d M Y', strtotime($b['booked_at'])) ?></td>
            <td class="d-flex flex-column gap-1">
              <?php if ($b['status'] !== 'cancelled' && $balance > 0): ?>
                <a href="<?= BASE_URL ?>/student/payment/pay.php?booking_id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-credit-card"></i> <?= $b['amount_paid'] > 0 ? 'Continue Payment' : 'Pay Now' ?></a>
              <?php endif; ?>
              <?php if ($b['status'] !== 'cancelled' && (float)$b['amount_paid'] <= 0): ?>
                <form method="post" onsubmit="return confirm('Cancel this booking?');">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="cancel_booking_id" value="<?= (int)$b['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger w-100">Cancel</button>
                </form>
              <?php elseif ($b['status'] !== 'cancelled'): ?>
                <span class="small text-muted">Contact admin to cancel a paid booking</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
