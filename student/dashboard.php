<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'Dashboard';
$user = current_user();

$stmt = $pdo->prepare("SELECT b.*, h.name AS hostel_name, rt.type FROM bookings b
    JOIN hostels h ON h.id = b.hostel_id
    JOIN room_types rt ON rt.id = b.room_type_id
    WHERE b.user_id = ? ORDER BY b.booked_at DESC LIMIT 5");
$stmt->execute([$user['id']]);
$recentBookings = $stmt->fetchAll();

$countStmt = $pdo->prepare("SELECT COUNT(*) AS c FROM bookings WHERE user_id = ?");
$countStmt->execute([$user['id']]);
$totalBookings = $countStmt->fetch()['c'];

$orderCountStmt = $pdo->prepare("SELECT COUNT(*) AS c FROM orders WHERE user_id = ?");
$orderCountStmt->execute([$user['id']]);
$totalOrders = $orderCountStmt->fetch()['c'];

$dobStmt = $pdo->prepare("SELECT date_of_birth FROM users WHERE id = ?");
$dobStmt->execute([$user['id']]);
$dob = $dobStmt->fetch()['date_of_birth'] ?? null;
$daysUntilBday = days_until_birthday($dob);

$recentWishStmt = $pdo->prepare("SELECT * FROM birthday_wishes WHERE user_id = ? AND sent_at >= (NOW() - INTERVAL 3 DAY) ORDER BY sent_at DESC LIMIT 1");
$recentWishStmt->execute([$user['id']]);
$recentWish = $recentWishStmt->fetch();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>Welcome, <?= h($user['full_name']) ?> 👋</h2>
  <p class="text-muted">Here's a quick overview of your account.</p>

  <?php if ($daysUntilBday === 0): ?>
    <div class="card birthday-card text-center p-4 mb-3">
      <h3 class="mb-1">🎉🎂 Happy Birthday, <?= h($user['full_name']) ?>! 🎂🎉</h3>
      <p class="mb-0">Everyone at Hostel Agency wishes you a wonderful day!</p>
    </div>
  <?php elseif ($recentWish): ?>
    <div class="card wish-card p-3 mb-3">
      <p class="mb-1"><i class="bi bi-gift-fill text-danger"></i> You received a birthday wish: "<?= nl2br(h($recentWish['message'])) ?>"</p>
      <a href="<?= BASE_URL ?>/student/birthday.php" class="small">View all your birthday wishes &rarr;</a>
    </div>
  <?php elseif ($daysUntilBday !== null): ?>
    <div class="card p-3 mb-3 d-flex flex-row align-items-center justify-content-between" style="background:#eef1f7;border-color:#c9cedd;">
      <span><i class="bi bi-gift text-primary"></i> Your birthday is in <strong><?= (int)$daysUntilBday ?></strong> day<?= $daysUntilBday == 1 ? '' : 's' ?>!</span>
      <a href="<?= BASE_URL ?>/student/birthday.php" class="btn btn-sm btn-outline-primary">View Countdown</a>
    </div>
  <?php else: ?>
    <div class="card p-3 mb-3 d-flex flex-row align-items-center justify-content-between">
      <span><i class="bi bi-gift text-muted"></i> Add your date of birth so we can wish you happy birthday!</span>
      <a href="<?= BASE_URL ?>/student/profile.php" class="btn btn-sm btn-outline-secondary">Add Birthday</a>
    </div>
  <?php endif; ?>

  <div class="row g-3 my-3">
    <div class="col-md-3 col-6">
      <div class="stat-card blue">
        <h6>Room Bookings</h6>
        <h2><?= (int)$totalBookings ?></h2>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="stat-card purple">
        <h6>Shop Orders</h6>
        <h2><?= (int)$totalOrders ?></h2>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="stat-card green">
        <h6>Browse Hostels</h6>
        <a href="<?= BASE_URL ?>/student/hostels.php" class="btn btn-light btn-sm mt-2">Explore Now</a>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="stat-card orange">
        <h6>Order Essentials</h6>
        <a href="<?= BASE_URL ?>/student/shop.php" class="btn btn-light btn-sm mt-2">Go to Shop</a>
      </div>
    </div>
  </div>

  <h4 class="mt-4">Recent Bookings</h4>
  <div class="table-responsive">
    <table class="table table-hover bg-white">
      <thead class="table-light">
        <tr><th>Hostel</th><th>Room Type</th><th>Amount</th><th>Status</th><th>Payment</th><th>Date</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (!$recentBookings): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No bookings yet. <a href="<?= BASE_URL ?>/student/hostels.php">Browse hostels</a> to get started.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentBookings as $b):
          $balance = (float)$b['amount'] - (float)$b['amount_paid'];
          $payBadge = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'][$b['payment_status']];
          $badge = ['pending' => 'warning', 'confirmed' => 'success', 'cancelled' => 'danger'][$b['status']];
        ?>
          <tr>
            <td><?= h($b['hostel_name']) ?></td>
            <td class="text-capitalize"><?= h($b['type']) ?></td>
            <td><?= money($b['amount']) ?></td>
            <td><span class="badge bg-<?= $badge ?> text-capitalize"><?= h($b['status']) ?></span></td>
            <td><span class="badge bg-<?= $payBadge ?> text-capitalize"><?= h($b['payment_status']) ?></span></td>
            <td><?= date('d M Y', strtotime($b['booked_at'])) ?></td>
            <td>
              <?php if ($balance > 0 && $b['status'] !== 'cancelled'): ?>
                <a href="<?= BASE_URL ?>/student/payment/pay.php?booking_id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-primary">Pay</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>