<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Admin Analytics';

$totalHostels = $pdo->query("SELECT COUNT(*) c FROM hostels")->fetch()['c'];
$totalRooms   = $pdo->query("SELECT SUM(total_rooms) c FROM room_types")->fetch()['c'];
$bookedRooms  = $pdo->query("SELECT SUM(booked_rooms) c FROM room_types")->fetch()['c'];
$occupancy    = $totalRooms > 0 ? round(($bookedRooms / $totalRooms) * 100, 1) : 0;
$bookingRevenue = $pdo->query("SELECT SUM(amount_paid) c FROM bookings WHERE status != 'cancelled'")->fetch()['c'] ?? 0;
$pendingCollections = $pdo->query("SELECT SUM(amount - amount_paid) c FROM bookings WHERE status != 'cancelled'")->fetch()['c'] ?? 0;
$orderRevenue = $pdo->query("SELECT SUM(amount_paid) c FROM orders WHERE status != 'cancelled'")->fetch()['c'] ?? 0;
$totalStudents = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='student'")->fetch()['c'];
$totalOrders = $pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];

$prefStmt = $pdo->query("SELECT rt.type, COUNT(*) c FROM bookings b
    JOIN room_types rt ON rt.id = b.room_type_id
    WHERE b.status != 'cancelled' GROUP BY rt.type ORDER BY c DESC");
$preferences = $prefStmt->fetchAll();

$occStmt = $pdo->query("SELECT h.name, SUM(rt.total_rooms) total, SUM(rt.booked_rooms) booked
    FROM hostels h JOIN room_types rt ON rt.hostel_id = h.id
    GROUP BY h.id ORDER BY h.name");
$hostelOccupancy = $occStmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>Analytics Dashboard</h2>
  <p class="text-muted">Track occupancy, revenue, orders, and student booking preferences.</p>

  <div class="row g-3 my-3">
    <div class="col-md-3 col-6">
      <div class="stat-card blue"><h6>Total Hostels</h6><h2><?= (int)$totalHostels ?></h2></div>
    </div>
    <div class="col-md-3 col-6">
      <div class="stat-card green"><h6>Occupancy Rate</h6><h2><?= $occupancy ?>%</h2></div>
    </div>
    <div class="col-md-3 col-6">
      <div class="stat-card orange"><h6>Room Revenue Collected</h6><h2><?= money($bookingRevenue) ?></h2></div>
    </div>
    <div class="col-md-3 col-6">
      <div class="stat-card purple"><h6>Registered Students</h6><h2><?= (int)$totalStudents ?></h2></div>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-md-4 col-6">
      <div class="stat-card" style="background:#7a2e2e;"><h6>Pending Room Collections</h6><h2><?= money($pendingCollections) ?></h2></div>
    </div>
    <div class="col-md-4 col-6">
      <div class="stat-card green"><h6>Shop Revenue Collected</h6><h2><?= money($orderRevenue) ?></h2></div>
    </div>
    <div class="col-md-4 col-6">
      <div class="stat-card orange"><h6>Total Shop Orders</h6><h2><?= (int)$totalOrders ?></h2></div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 my-4">
    <a href="<?= BASE_URL ?>/admin/birthdays.php" class="btn btn-outline-danger"><i class="bi bi-gift-fill"></i> Student Birthdays</a>
    <a href="<?= BASE_URL ?>/admin/testimonials.php" class="btn btn-outline-primary"><i class="bi bi-chat-quote"></i> Manage Testimonials</a>
    <a href="<?= BASE_URL ?>/admin/news.php" class="btn btn-outline-secondary"><i class="bi bi-newspaper"></i> Manage News</a>
    <a href="<?= BASE_URL ?>/admin/feedback.php" class="btn btn-outline-dark"><i class="bi bi-chat-square-text"></i> Feedback &amp; Questions</a>
    <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-outline-success"><i class="bi bi-bag"></i> Manage Shop</a>
    <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline-dark"><i class="bi bi-box-seam"></i> Manage Orders</a>
  </div>

  <div class="row g-4 mt-2">
    <div class="col-lg-6">
      <div class="card p-3">
        <h5>Occupancy by Hostel</h5>
        <canvas id="occChart" height="220"></canvas>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card p-3">
        <h5>Booking Preferences (Room Type)</h5>
        <canvas id="prefChart" height="220"></canvas>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#6b7280';
Chart.defaults.borderColor = 'rgba(15,28,63,.08)';

new Chart(document.getElementById('occChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($hostelOccupancy, 'name')) ?>,
    datasets: [
      { label: 'Total Rooms', data: <?= json_encode(array_map('intval', array_column($hostelOccupancy, 'total'))) ?>, backgroundColor: '#c9cedd' },
      { label: 'Booked Rooms', data: <?= json_encode(array_map('intval', array_column($hostelOccupancy, 'booked'))) ?>, backgroundColor: '#0f1c3f' }
    ]
  },
  options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('prefChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_map('ucfirst', array_column($preferences, 'type'))) ?>,
    datasets: [{ data: <?= json_encode(array_map('intval', array_column($preferences, 'c'))) ?>, backgroundColor: ['#0f1c3f', '#2f5233', '#b8912c'] }]
  },
  options: { responsive: true }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>