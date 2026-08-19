<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'Shop';

$category = $_GET['category'] ?? '';
$sql = "SELECT * FROM products WHERE is_available = 1";
$params = [];
if (in_array($category, ['food', 'item', 'other'], true)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
    <h2 class="mb-0"><i class="bi bi-bag"></i> Shop</h2>
    <a href="<?= BASE_URL ?>/student/cart.php" class="btn btn-outline-primary">
      <i class="bi bi-cart3"></i> View Cart (<?= (int)cart_count() ?>)
    </a>
  </div>
  <p class="text-muted">Order food, everyday items, and other essentials — delivered right to your hostel.</p>

  <div class="btn-group mb-4" role="group" aria-label="Filter by category">
    <a href="<?= BASE_URL ?>/student/shop.php" class="btn btn-sm btn-outline-secondary <?= $category === '' ? 'active' : '' ?>">All</a>
    <a href="<?= BASE_URL ?>/student/shop.php?category=food" class="btn btn-sm btn-outline-secondary <?= $category === 'food' ? 'active' : '' ?>">Food</a>
    <a href="<?= BASE_URL ?>/student/shop.php?category=item" class="btn btn-sm btn-outline-secondary <?= $category === 'item' ? 'active' : '' ?>">Items</a>
    <a href="<?= BASE_URL ?>/student/shop.php?category=other" class="btn btn-sm btn-outline-secondary <?= $category === 'other' ? 'active' : '' ?>">Other</a>
  </div>

  <div class="row g-4">
    <?php if (!$products): ?>
      <p class="text-muted">No products available right now — check back soon.</p>
    <?php endif; ?>
    <?php foreach ($products as $p): ?>
      <div class="col-md-4 col-lg-3">
        <div class="card product-card">
          <img src="<?= h(media_url($p['image'])) ?>" alt="<?= h($p['name']) ?>">
          <div class="card-body">
            <span class="category-chip mb-2"><?= h($p['category']) ?></span>
            <h6 class="card-title mt-2"><?= h($p['name']) ?></h6>
            <p class="card-text small text-muted"><?= h(mb_strimwidth($p['description'], 0, 70, '...')) ?></p>
            <p class="fw-bold text-primary"><?= money($p['price']) ?></p>
          </div>
          <div class="card-footer bg-white border-0 pb-3">
            <form method="post" action="<?= BASE_URL ?>/student/cart_add.php">
              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="redirect" value="shop">
              <div class="input-group input-group-sm mb-2">
                <input type="number" name="quantity" value="1" min="1" max="20" class="form-control">
              </div>
              <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-cart-plus"></i> Add to Cart</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
