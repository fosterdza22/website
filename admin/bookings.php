<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Manage Bookings';

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT b.*, u.full_name, u.email, h.name AS hostel_name, rt.type
        FROM bookings b
        JOIN users u ON u.id = b.user_id
        JOIN hostels h ON h.id = b.hostel_id
        JOIN room_types rt ON rt.id = b.room_type_id";
$params = [];
if ($statusFilter) {
    $sql .= " WHERE b.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY b.booked_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Manage Bookings</h2>
    <form class="d-flex gap-2">
      <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>Pending</option>
        <option value="confirmed" <?= $statusFilter==='confirmed'?'selected':'' ?>>Confirmed</option>
        <option value="cancelled" <?= $statusFilter==='cancelled'?'selected':'' ?>>Cancelled</option>
      </select>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead class="table-light">
        <tr><th>Student</th><th>Hostel</th><th>Room</th><th>Year</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Booking Status</th><th>Payment</th><th>Booked</th><th>Update</th></tr>
      </thead>
      <tbody>
        <?php if (!$bookings): ?>
          <tr><td colspan="11" class="text-center text-muted py-4">No bookings found.</td></tr>
        <?php endif; ?>
        <?php foreach ($bookings as $b):
          $badge = ['pending' => 'warning', 'confirmed' => 'success', 'cancelled' => 'danger'][$b['status']];
          $payBadge = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success'][$b['payment_status']];
          $balance = (float)$b['amount'] - (float)$b['amount_paid'];
        ?>
          <tr>
            <td><?= h($b['full_name']) ?><br><span class="small text-muted"><?= h($b['email']) ?></span></td>
            <td><?= h($b['hostel_name']) ?></td>
            <td class="text-capitalize"><?= h($b['type']) ?></td>
            <td><?= h($b['academic_year']) ?></td>
            <td><?= money($b['amount']) ?></td>
            <td class="text-success"><?= money($b['amount_paid']) ?></td>
            <td class="<?= $balance > 0 ? 'text-danger' : '' ?>"><?= money($balance) ?></td>
            <td><span class="badge bg-<?= $badge ?> text-capitalize"><?= h($b['status']) ?></span></td>
            <td><span class="badge bg-<?= $payBadge ?> text-capitalize"><?= h($b['payment_status']) ?></span></td>
            <td><?= date('d M Y', strtotime($b['booked_at'])) ?></td>
            <td>
              <form method="post" action="<?= BASE_URL ?>/admin/booking_update.php" class="d-flex gap-1">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                <select name="status" class="form-select form-select-sm">
                  <option value="pending" <?= $b['status']==='pending'?'selected':'' ?>>Pending</option>
                  <option value="confirmed" <?= $b['status']==='confirmed'?'selected':'' ?>>Confirmed</option>
                  <option value="cancelled" <?= $b['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                </select>
                <button class="btn btn-sm btn-primary">Save</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
