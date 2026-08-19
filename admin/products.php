<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Manage Shop';

$allowedExt = ['jpg','jpeg','png','webp','gif'];
$maxBytes = 8 * 1024 * 1024;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = in_array($_POST['category'] ?? '', ['food','item','other'], true) ? $_POST['category'] : 'item';
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock_quantity'] ?? 0);
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;
        $imageUrl = trim($_POST['image_url'] ?? '');

        if ($name === '' || $price <= 0) {
            $errors[] = 'Name and a valid price are required.';
        }

        $finalImage = $imageUrl ?: null;

        if (!empty($_FILES['image_file']['name'])) {
            $file = $_FILES['image_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Image upload failed.';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    $errors[] = 'Product image must be jpg, png, webp, or gif.';
                } elseif ($file['size'] > $maxBytes) {
                    $errors[] = 'Product image must be smaller than 8 MB.';
                } else {
                    $destDir = __DIR__ . '/../assets/uploads/products';
                    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                    $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $destDir . '/' . $safeName)) {
                        $finalImage = '/assets/uploads/products/' . $safeName;
                    } else {
                        $errors[] = 'Could not save the uploaded image.';
                    }
                }
            }
        }

        if (!$errors) {
            if (!$finalImage) {
                $finalImage = 'https://picsum.photos/seed/' . urlencode($name) . '/500/400';
            }
            if ($id) {
                $pdo->prepare("UPDATE products SET name=?, description=?, category=?, price=?, image=?, stock_quantity=?, is_available=? WHERE id=?")
                    ->execute([$name, $description, $category, $price, $finalImage, $stock, $isAvailable, $id]);
                set_flash('success', 'Product updated.');
            } else {
                $pdo->prepare("INSERT INTO products (name, description, category, price, image, stock_quantity, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$name, $description, $category, $price, $finalImage, $stock, $isAvailable]);
                set_flash('success', 'Product added.');
            }
            redirect('/admin/products.php');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product']) && verify_csrf($_POST['csrf'] ?? '')) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([(int)$_POST['id']]);
    set_flash('success', 'Product deleted.');
    redirect('/admin/products.php');
}

$editId = (int)($_GET['edit'] ?? 0);
$editing = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
}

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2><i class="bi bi-bag"></i> Manage Shop</h2>
  <p class="text-muted">Add and manage the food, items, and products students can order for delivery.</p>

  <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>

  <div class="card p-4 mb-4">
    <h5><?= $editing ? 'Edit Product' : 'Add New Product' ?></h5>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="save_product" value="1">
      <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Product Name</label>
          <input type="text" class="form-control" name="name" required value="<?= h($editing['name'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Category</label>
          <select class="form-select" name="category">
            <option value="food" <?= ($editing['category'] ?? '') === 'food' ? 'selected' : '' ?>>Food</option>
            <option value="item" <?= ($editing['category'] ?? 'item') === 'item' ? 'selected' : '' ?>>Item</option>
            <option value="other" <?= ($editing['category'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Price (GH₵)</label>
          <input type="number" step="0.01" class="form-control" name="price" required value="<?= h($editing['price'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" rows="2"><?= h($editing['description'] ?? '') ?></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label">Stock Quantity</label>
          <input type="number" class="form-control" name="stock_quantity" value="<?= h($editing['stock_quantity'] ?? 20) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Image URL (optional)</label>
          <input type="text" class="form-control" name="image_url" value="<?= (!empty($editing['image']) && strpos($editing['image'], 'http') === 0) ? h($editing['image']) : '' ?>" placeholder="https://...">
        </div>
        <div class="col-md-4">
          <label class="form-label">Or Upload an Image</label>
          <input type="file" class="form-control" name="image_file" accept=".jpg,.jpeg,.png,.webp,.gif">
        </div>
        <div class="col-12">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_available" id="is_available" <?= (!$editing || $editing['is_available']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_available">Available for order</label>
          </div>
        </div>
      </div>
      <button class="btn btn-primary mt-3"><?= $editing ? 'Update' : 'Add' ?> Product</button>
      <?php if ($editing): ?><a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-outline-secondary mt-3">Cancel</a><?php endif; ?>
    </form>
  </div>

  <h5>All Products</h5>
  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead class="table-light"><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (!$products): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No products yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><img src="<?= h(media_url($p['image'])) ?>" width="60" height="45" style="object-fit:cover;border-radius:6px;" alt=""></td>
            <td><?= h($p['name']) ?></td>
            <td class="text-capitalize"><?= h($p['category']) ?></td>
            <td><?= money($p['price']) ?></td>
            <td><?= (int)$p['stock_quantity'] ?></td>
            <td><span class="badge bg-<?= $p['is_available'] ? 'success' : 'secondary' ?>"><?= $p['is_available'] ? 'Available' : 'Hidden' ?></span></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/products.php?edit=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
              <form method="post" class="d-inline" onsubmit="return confirm('Delete this product?');">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="delete_product" value="1">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
