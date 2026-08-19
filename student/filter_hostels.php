<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();

$maxPrice    = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;
$maxDistance = isset($_GET['max_distance']) ? (float)$_GET['max_distance'] : 999;
$roomType    = $_GET['room_type'] ?? '';
$sort        = $_GET['sort'] ?? 'price_asc';
$amenityIds  = array_map('intval', $_GET['amenities'] ?? []);

$sql = "SELECT h.*, MIN(rt.price_per_year) AS min_price
        FROM hostels h
        JOIN room_types rt ON rt.hostel_id = h.id
        WHERE h.distance_to_campus_km <= ?";
$execParams = [$maxDistance];

if ($roomType !== '') {
    $sql .= " AND rt.type = ?";
    $execParams[] = $roomType;
}

if ($amenityIds) {
    $placeholders = implode(',', array_fill(0, count($amenityIds), '?'));
    $sql .= " AND h.id IN (
                SELECT hostel_id FROM hostel_amenities
                WHERE amenity_id IN ($placeholders)
                GROUP BY hostel_id
                HAVING COUNT(DISTINCT amenity_id) = " . count($amenityIds) . "
              )";
    foreach ($amenityIds as $id) $execParams[] = $id;
}

$sql .= " GROUP BY h.id HAVING min_price <= ?";
$execParams[] = $maxPrice;

switch ($sort) {
    case 'price_desc':    $sql .= " ORDER BY min_price DESC"; break;
    case 'distance_asc':  $sql .= " ORDER BY h.distance_to_campus_km ASC"; break;
    default:              $sql .= " ORDER BY min_price ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($execParams);
$hostels = $stmt->fetchAll();

if (!$hostels) {
    echo '<div class="col-12"><div class="alert alert-info">No hostels match your filters. Try widening your search.</div></div>';
    return;
}

foreach ($hostels as $hostel):
    $amenityList = get_hostel_amenities($pdo, $hostel['id']);
    ?>
    <div class="col-md-6 col-xl-4">
      <div class="card hostel-card">
        <img src="<?= h(media_url($hostel['main_image'])) ?>" alt="Photo of <?= h($hostel['name']) ?>">
        <div class="card-body">
          <h5 class="card-title"><?= h($hostel['name']) ?></h5>
          <p class="text-muted small mb-1"><i class="bi bi-geo-alt"></i> <?= h($hostel['distance_to_campus_km']) ?> km from campus</p>
          <div class="mb-2">
            <?php foreach (array_slice($amenityList, 0, 4) as $a): ?>
              <span class="amenity-chip"><?= h($a['name']) ?></span>
            <?php endforeach; ?>
          </div>
          <span class="price-badge">From <?= money($hostel['min_price']) ?>/yr</span>
        </div>
        <div class="card-footer bg-white border-0 pb-3">
          <a href="<?= u('/student/hostel_detail.php?id=' . (int)$hostel['id']) ?>" class="btn btn-primary w-100">View Details</a>
        </div>
      </div>
    </div>
<?php endforeach; ?>
