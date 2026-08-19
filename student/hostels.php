<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'Browse Hostels';

$amenities = $pdo->query("SELECT * FROM amenities ORDER BY name")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>Browse Hostels</h2>
  <p class="text-muted">Filter by price, amenities, and distance to find your ideal hostel.</p>

  <div class="row">
    <div class="col-lg-3 mb-4">
      <div class="card p-3">
        <h5>Filters</h5>
        <form id="filterForm">
          <div class="mb-3">
            <label class="form-label">Max Price / Year</label>
            <input type="range" class="form-range" name="max_price" min="1000" max="9000" step="200" value="9000" oninput="document.getElementById('priceOut').innerText=this.value">
            <div class="small text-muted">Up to GH₵ <span id="priceOut">9000</span></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Max Distance (km)</label>
            <input type="range" class="form-range" name="max_distance" min="0" max="5" step="0.1" value="5" oninput="document.getElementById('distOut').innerText=this.value">
            <div class="small text-muted">Within <span id="distOut">5</span> km</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Room Type</label>
            <select class="form-select" name="room_type">
              <option value="">Any</option>
              <option value="single">Single</option>
              <option value="shared">Shared</option>
              <option value="premium">Premium</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Amenities</label>
            <?php foreach ($amenities as $a): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="amenities[]" value="<?= (int)$a['id'] ?>" id="am<?= (int)$a['id'] ?>">
                <label class="form-check-label" for="am<?= (int)$a['id'] ?>"><?= h($a['name']) ?></label>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Sort By</label>
            <select class="form-select" name="sort">
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
              <option value="distance_asc">Distance: Nearest First</option>
            </select>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-9">
      <div id="hostelResults" class="row g-4"></div>
    </div>
  </div>

  <hr class="my-5">
  <h4 class="mb-3"><i class="bi bi-map"></i> Hostel Locations Relative to Campus</h4>
  <div id="map"></div>
</div>

<script>
  fetch(window.BASE_URL + '/student/map_points.php')
    .then(r => r.json())
    .then(data => buildMap('map', data.points, [data.campus.lat, data.campus.lng]));
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
