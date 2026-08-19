<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Manage Hostels';

$hostels = $pdo->query("SELECT h.*, MIN(rt.price_per_year) AS min_price, COUNT(rt.id) AS room_type_count
    FROM hostels h LEFT JOIN room_types rt ON rt.hostel_id = h.id
    GROUP BY h.id ORDER BY h.id DESC")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Manage Hostels</h2>
    <a href="<?= BASE_URL ?>/admin/hostel_form.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Hostel</a>
  </div>

  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead class="table-light">
        <tr><th>Image</th><th>Name</th><th>Distance</th><th>From Price</th><th>Room Types</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($hostels as $h): ?>
          <tr>
            <td><img src="<?= h(media_url($h['main_image'])) ?>" width="70" height="50" style="object-fit:cover;border-radius:6px;" alt=""></td>
            <td><?= h($h['name']) ?></td>
            <td><?= h($h['distance_to_campus_km']) ?> km</td>
            <td><?= $h['min_price'] ? money($h['min_price']) : '—' ?></td>
            <td><?= (int)$h['room_type_count'] ?></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/hostel_form.php?id=<?= (int)$h['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
              <a href="<?= BASE_URL ?>/admin/rooms.php?hostel_id=<?= (int)$h['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-door-open"></i> Rooms</a>
              <a href="<?= BASE_URL ?>/admin/media.php?hostel_id=<?= (int)$h['id'] ?>" class="btn btn-sm btn-outline-info text-dark"><i class="bi bi-images"></i> Media</a>
              <form method="post" action="<?= BASE_URL ?>/admin/hostel_delete.php" class="d-inline" onsubmit="return confirm('Delete this hostel and all related data?');">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$h['id'] ?>">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
