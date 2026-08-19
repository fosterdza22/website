<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$hostelId = (int)($_GET['hostel_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM hostels WHERE id = ?");
$stmt->execute([$hostelId]);
$hostel = $stmt->fetch();
if (!$hostel) {
    set_flash('error', 'Hostel not found.');
    redirect('/admin/hostels.php');
}

$pageTitle = 'Rooms — ' . $hostel['name'];
$rooms = get_room_types($pdo, $hostelId);

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>Room Types — <?= h($hostel['name']) ?></h2>
  <a href="<?= BASE_URL ?>/admin/hostels.php" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to Hostels</a>

  <div class="table-responsive mb-4">
    <table class="table table-hover bg-white align-middle">
      <thead class="table-light">
        <tr><th>Type</th><th>Price/Year</th><th>Size</th><th>Furnishing</th><th>Total</th><th>Booked</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rooms as $r): ?>
          <tr>
            <td class="text-capitalize"><?= h($r['type']) ?></td>
            <td><?= money($r['price_per_year']) ?></td>
            <td><?= h($r['size_sqm']) ?> m²</td>
            <td class="small"><?= h($r['furnishing']) ?></td>
            <td><?= (int)$r['total_rooms'] ?></td>
            <td><?= (int)$r['booked_rooms'] ?></td>
            <td>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit<?= (int)$r['id'] ?>">Edit</button>
              <form method="post" action="<?= BASE_URL ?>/admin/room_delete.php" class="d-inline" onsubmit="return confirm('Delete this room type?');">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="hostel_id" value="<?= (int)$hostelId ?>">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
          <tr class="collapse" id="edit<?= (int)$r['id'] ?>">
            <td colspan="7">
              <form method="post" action="<?= BASE_URL ?>/admin/room_save.php" class="row g-2 align-items-end">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="hostel_id" value="<?= (int)$hostelId ?>">
                <div class="col-md-2">
                  <label class="form-label small">Type</label>
                  <select class="form-select form-select-sm" name="type">
                    <option value="single" <?= $r['type']==='single'?'selected':'' ?>>Single</option>
                    <option value="shared" <?= $r['type']==='shared'?'selected':'' ?>>Shared</option>
                    <option value="premium" <?= $r['type']==='premium'?'selected':'' ?>>Premium</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Price/Year</label>
                  <input type="number" step="0.01" class="form-control form-control-sm" name="price_per_year" value="<?= h($r['price_per_year']) ?>">
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Size (m²)</label>
                  <input type="number" step="0.1" class="form-control form-control-sm" name="size_sqm" value="<?= h($r['size_sqm']) ?>">
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Total Rooms</label>
                  <input type="number" class="form-control form-control-sm" name="total_rooms" value="<?= (int)$r['total_rooms'] ?>">
                </div>
                <div class="col-md-3">
                  <label class="form-label small">Furnishing</label>
                  <input type="text" class="form-control form-control-sm" name="furnishing" value="<?= h($r['furnishing']) ?>">
                </div>
                <div class="col-md-1">
                  <button class="btn btn-sm btn-success w-100">Save</button>
                </div>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card p-3">
    <h5>Add New Room Type</h5>
    <form method="post" action="<?= BASE_URL ?>/admin/room_save.php" class="row g-2 align-items-end">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="hostel_id" value="<?= (int)$hostelId ?>">
      <div class="col-md-2">
        <label class="form-label small">Type</label>
        <select class="form-select form-select-sm" name="type" required>
          <option value="single">Single</option>
          <option value="shared">Shared</option>
          <option value="premium">Premium</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Price/Year</label>
        <input type="number" step="0.01" class="form-control form-control-sm" name="price_per_year" required>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Size (m²)</label>
        <input type="number" step="0.1" class="form-control form-control-sm" name="size_sqm">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Total Rooms</label>
        <input type="number" class="form-control form-control-sm" name="total_rooms" value="10">
      </div>
      <div class="col-md-3">
        <label class="form-label small">Furnishing</label>
        <input type="text" class="form-control form-control-sm" name="furnishing" placeholder="Bed, wardrobe, desk...">
      </div>
      <div class="col-md-1">
        <button class="btn btn-sm btn-primary w-100">Add</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
