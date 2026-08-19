<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM hostels WHERE id = ?");
$stmt->execute([$id]);
$hostel = $stmt->fetch();

if (!$hostel) {
    set_flash('error', 'Hostel not found.');
    redirect('/student/hostels.php');
}

$pageTitle = $hostel['name'];
$amenities = get_hostel_amenities($pdo, $id);
$roomTypes = get_room_types($pdo, $id);
$photos = get_hostel_photos($pdo, $id);
$videos = get_hostel_videos($pdo, $id);

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/student/hostels.php">Hostels</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= h($hostel['name']) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-lg-7">
      <img src="<?= h(media_url($hostel['main_image'])) ?>" class="img-fluid rounded mb-3" alt="<?= h($hostel['name']) ?>">
      <div class="row g-2 mb-3">
        <?php foreach ($photos as $p): ?>
          <div class="col-4">
            <img src="<?= h(media_url($p['photo_path'])) ?>" class="gallery-thumb" alt="Gallery photo of <?= h($hostel['name']) ?>">
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($videos): ?>
        <h6>Video Tour</h6>
        <div class="row g-2">
          <?php foreach ($videos as $v): ?>
            <div class="col-md-6">
              <video controls class="w-100 rounded" style="max-height:220px;background:#000;">
                <source src="<?= h(media_url($v['video_path'])) ?>">
              </video>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-lg-5">
      <h2><?= h($hostel['name']) ?></h2>
      <p class="text-muted"><i class="bi bi-geo-alt"></i> <?= h($hostel['address']) ?> &middot; <?= h($hostel['distance_to_campus_km']) ?> km from campus</p>
      <p><?= nl2br(h($hostel['description'])) ?></p>

      <h5 class="mt-4">Amenities</h5>
      <div class="mb-3">
        <?php foreach ($amenities as $a): ?>
          <span class="amenity-chip"><i class="bi bi-<?= h($a['icon']) ?>"></i> <?= h($a['name']) ?></span>
        <?php endforeach; ?>
      </div>

      <h5 class="mt-4">Room Types &amp; Pricing (this hostel only)</h5>
      <form method="post" action="<?= BASE_URL ?>/student/book.php">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="hostel_id" value="<?= (int)$hostel['id'] ?>">
        <div class="table-responsive">
          <table class="table bg-white align-middle">
            <thead class="table-light"><tr><th></th><th>Type</th><th>Size</th><th>Furnishing</th><th>Price/Year</th><th>Availability</th></tr></thead>
            <tbody>
              <?php foreach ($roomTypes as $rt):
                $available = $rt['total_rooms'] - $rt['booked_rooms'];
              ?>
                <tr>
                  <td><input class="form-check-input" type="radio" name="room_type_id" value="<?= (int)$rt['id'] ?>" <?= $available <= 0 ? 'disabled' : '' ?> required></td>
                  <td class="text-capitalize"><?= h($rt['type']) ?></td>
                  <td><?= h($rt['size_sqm']) ?> m²</td>
                  <td class="small"><?= h($rt['furnishing']) ?></td>
                  <td><?= money($rt['price_per_year']) ?></td>
                  <td><?= $available > 0 ? "<span class='badge bg-success'>$available available</span>" : "<span class='badge bg-secondary'>Full</span>" ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mb-3">
          <label class="form-label" for="academic_year">Academic Year</label>
          <select class="form-select" name="academic_year" id="academic_year">
            <option value="2026/2027">2026/2027</option>
            <option value="2027/2028">2027/2028</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Payment Plan</label>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_plan" value="full" id="planFull" checked>
            <label class="form-check-label" for="planFull">Pay in full</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_plan" value="installment" id="planInstallment">
            <label class="form-check-label" for="planInstallment">Pay in 3 installments (40% / 30% / 30%)</label>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Reserve Room</button>
      </form>
    </div>
  </div>

  <hr class="my-4">
  <h5><i class="bi bi-map"></i> Location</h5>
  <div id="map"></div>
</div>

<script>
  fetch(window.BASE_URL + '/student/map_points.php')
    .then(r => r.json())
    .then(data => {
      const only = data.points.filter(p => p.id === <?= (int)$hostel['id'] ?>);
      buildMap('map', only, [<?= (float)$hostel['latitude'] ?>, <?= (float)$hostel['longitude'] ?>]);
    });
</script>

<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img id="lightboxImg" src="" class="img-fluid w-100 rounded" alt="Enlarged gallery photo">
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
