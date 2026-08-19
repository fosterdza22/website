<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$hostel = ['name'=>'','description'=>'','address'=>'','latitude'=>'','longitude'=>'','distance_to_campus_km'=>'','main_image'=>''];
$amenities = $pdo->query("SELECT * FROM amenities ORDER BY name")->fetchAll();
$selectedAmenities = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM hostels WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $hostel = $found;
        $selectedAmenities = array_column(get_hostel_amenities($pdo, $id), 'id');
    }
}

$pageTitle = $id ? 'Edit Hostel' : 'Add Hostel';
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2><?= $id ? 'Edit' : 'Add' ?> Hostel</h2>
  <form method="post" action="<?= BASE_URL ?>/admin/hostel_save.php" class="card p-4">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Hostel Name</label>
        <input type="text" class="form-control" name="name" required value="<?= h($hostel['name']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Address</label>
        <input type="text" class="form-control" name="address" value="<?= h($hostel['address']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="3" required><?= h($hostel['description']) ?></textarea>
      </div>
      <div class="col-md-3">
        <label class="form-label">Latitude</label>
        <input type="text" class="form-control" name="latitude" value="<?= h($hostel['latitude']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Longitude</label>
        <input type="text" class="form-control" name="longitude" value="<?= h($hostel['longitude']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Distance to Campus (km)</label>
        <input type="number" step="0.1" class="form-control" name="distance_to_campus_km" value="<?= h($hostel['distance_to_campus_km']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Main Image URL</label>
        <input type="text" class="form-control" name="main_image" value="<?= h($hostel['main_image']) ?>" placeholder="https://...">
      </div>

      <div class="col-12">
        <label class="form-label">Amenities</label><br>
        <?php foreach ($amenities as $a): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="amenities[]" value="<?= (int)$a['id'] ?>"
              id="fam<?= (int)$a['id'] ?>" <?= in_array($a['id'], $selectedAmenities) ? 'checked' : '' ?>>
            <label class="form-check-label" for="fam<?= (int)$a['id'] ?>"><?= h($a['name']) ?></label>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit" class="btn btn-primary mt-4"><?= $id ? 'Update' : 'Create' ?> Hostel</button>
    <a href="<?= BASE_URL ?>/admin/hostels.php" class="btn btn-outline-secondary mt-4">Cancel</a>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
