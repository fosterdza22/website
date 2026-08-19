<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'Compare Hostels';

$hostels = $pdo->query("SELECT id, name FROM hostels ORDER BY name")->fetchAll();

$selectedIds = array_map('intval', $_GET['ids'] ?? []);
$compareData = [];
if ($selectedIds) {
    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM hostels WHERE id IN ($placeholders)");
    $stmt->execute($selectedIds);
    $selectedHostels = $stmt->fetchAll();
    foreach ($selectedHostels as $h) {
        $compareData[] = [
            'hostel' => $h,
            'rooms' => get_room_types($pdo, $h['id']),
            'amenities' => get_hostel_amenities($pdo, $h['id']),
        ];
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>Compare Hostels</h2>
  <p class="text-muted">Select up to 3 hostels to compare prices and amenities side by side.</p>

  <form method="get" class="card p-3 mb-4">
    <div class="row">
      <?php foreach ($hostels as $h): ?>
        <div class="col-md-4 col-6">
          <div class="form-check">
            <input class="form-check-input compare-checkbox" type="checkbox" name="ids[]" value="<?= (int)$h['id'] ?>"
              id="chk<?= (int)$h['id'] ?>" <?= in_array($h['id'], $selectedIds) ? 'checked' : '' ?>>
            <label class="form-check-label" for="chk<?= (int)$h['id'] ?>"><?= h($h['name']) ?></label>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="btn btn-primary mt-3" style="max-width:220px;">Compare</button>
  </form>

  <?php if ($compareData): ?>
    <div class="table-responsive">
      <table class="table table-bordered bg-white align-middle">
        <thead class="table-light">
          <tr>
            <th>Hostel</th>
            <?php foreach ($compareData as $c): ?>
              <th><?= h($c['hostel']['name']) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-semibold">Distance to Campus</td>
            <?php foreach ($compareData as $c): ?>
              <td><?= h($c['hostel']['distance_to_campus_km']) ?> km</td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td class="fw-semibold">Single Room Price</td>
            <?php foreach ($compareData as $c):
              $single = array_values(array_filter($c['rooms'], fn($r) => $r['type'] === 'single'));
            ?>
              <td><?= $single ? money($single[0]['price_per_year']) : '—' ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td class="fw-semibold">Shared Room Price</td>
            <?php foreach ($compareData as $c):
              $shared = array_values(array_filter($c['rooms'], fn($r) => $r['type'] === 'shared'));
            ?>
              <td><?= $shared ? money($shared[0]['price_per_year']) : '—' ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td class="fw-semibold">Premium Room Price</td>
            <?php foreach ($compareData as $c):
              $premium = array_values(array_filter($c['rooms'], fn($r) => $r['type'] === 'premium'));
            ?>
              <td><?= $premium ? money($premium[0]['price_per_year']) : '—' ?></td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td class="fw-semibold">Amenities</td>
            <?php foreach ($compareData as $c): ?>
              <td>
                <?php foreach ($c['amenities'] as $a): ?>
                  <span class="amenity-chip"><?= h($a['name']) ?></span>
                <?php endforeach; ?>
              </td>
            <?php endforeach; ?>
          </tr>
          <tr>
            <td class="fw-semibold">Action</td>
            <?php foreach ($compareData as $c): ?>
              <td><a href="<?= BASE_URL ?>/student/hostel_detail.php?id=<?= (int)$c['hostel']['id'] ?>" class="btn btn-sm btn-primary">View & Book</a></td>
            <?php endforeach; ?>
          </tr>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
