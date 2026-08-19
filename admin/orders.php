<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Manage Orders';

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON u.id = o.user_id";
$params = [];
if ($statusFilter) {
    $sql .= " WHERE o.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$itemsByOrder = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders)");
    $itemStmt->execute($ids);
    foreach ($itemStmt->fetchAll() as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
}

$statusLabel = ['pending' => 'Pending Payment', 'processing' => 'Processing', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-box-seam"></i> Manage Orders</h2>
    <form class="d-flex gap-2">
      <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach ($statusLabel as $key => $label): ?>
          <option value="<?= h($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <?php if (!$orders): ?>
    <p class="text-muted">No orders found.</p>
  <?php endif; ?>

  <?php foreach ($orders as $order):
    $balance = (float)$order['total_amount'] - (float)$order['amount_paid'];
  ?>
    <div class="card mb-3 p-3">
      <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
        <div>
          <strong>Order #<?= (int)$order['id'] ?></strong> — <?= h($order['full_name']) ?>
          <span class="text-muted small"> &middot; <?= h($order['email']) ?> &middot; <?= date('d M Y, g:ia', strtotime($order['created_at'])) ?></span>
        </div>
        <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'danger' ?>"><?= h(ucfirst($order['payment_status'])) ?></span>
      </div>
      <p class="small text-muted mb-2"><i class="bi bi-geo-alt"></i> <?= h($order['delivery_address']) ?> &middot; <i class="bi bi-telephone"></i> <?= h($order['delivery_phone']) ?>
        <?php if ($order['delivery_notes']): ?><br><i class="bi bi-sticky"></i> <?= h($order['delivery_notes']) ?><?php endif; ?>
      </p>
      <ul class="list-unstyled small mb-2">
        <?php foreach ($itemsByOrder[$order['id']] ?? [] as $item): ?>
          <li><?= (int)$item['quantity'] ?>x <?= h($item['product_name']) ?> — <?= money($item['line_total']) ?></li>
        <?php endforeach; ?>
      </ul>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <strong>Total: <?= money($order['total_amount']) ?></strong>
          <span class="text-success small"> &middot; Paid: <?= money($order['amount_paid']) ?></span>
          <?php if ($balance > 0): ?><span class="text-danger small"> &middot; Balance: <?= money($balance) ?></span><?php endif; ?>
        </div>
        <form method="post" action="<?= BASE_URL ?>/admin/order_update.php" class="d-flex gap-1">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
          <select name="status" class="form-select form-select-sm">
            <?php foreach ($statusLabel as $key => $label): ?>
              <option value="<?= h($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-sm btn-primary">Update</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
