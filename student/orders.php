<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'My Orders';
$user = current_user();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
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

$statusBadge = ['pending' => 'secondary', 'processing' => 'info', 'out_for_delivery' => 'warning', 'delivered' => 'success', 'cancelled' => 'danger'];
$statusLabel = ['pending' => 'Pending Payment', 'processing' => 'Processing', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2><i class="bi bi-box-seam"></i> My Orders</h2>
  <p class="text-muted">Track your food and item deliveries.</p>

  <?php if (!$orders): ?>
    <div class="alert alert-info">No orders yet. <a href="<?= BASE_URL ?>/student/shop.php">Visit the shop</a> to place your first order.</div>
  <?php endif; ?>

  <?php foreach ($orders as $order):
    $balance = (float)$order['total_amount'] - (float)$order['amount_paid'];
  ?>
    <div class="card mb-3 p-3">
      <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
        <div>
          <strong>Order #<?= (int)$order['id'] ?></strong>
          <span class="text-muted small"> &middot; <?= date('d M Y, g:ia', strtotime($order['created_at'])) ?></span>
        </div>
        <div>
          <span class="badge bg-<?= $statusBadge[$order['status']] ?>"><?= h($statusLabel[$order['status']]) ?></span>
          <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'danger' ?>"><?= h(ucfirst($order['payment_status'])) ?></span>
        </div>
      </div>
      <p class="small text-muted mb-2"><i class="bi bi-geo-alt"></i> <?= h($order['delivery_address']) ?> &middot; <?= h($order['delivery_phone']) ?></p>
      <ul class="list-unstyled small mb-2">
        <?php foreach ($itemsByOrder[$order['id']] ?? [] as $item): ?>
          <li><?= (int)$item['quantity'] ?>x <?= h($item['product_name']) ?> — <?= money($item['line_total']) ?></li>
        <?php endforeach; ?>
      </ul>
      <div class="d-flex justify-content-between align-items-center">
        <strong>Total: <?= money($order['total_amount']) ?></strong>
        <?php if ($balance > 0 && $order['status'] !== 'cancelled'): ?>
          <a href="<?= BASE_URL ?>/student/order_payment/pay.php?order_id=<?= (int)$order['id'] ?>" class="btn btn-sm btn-primary">Pay <?= money($balance) ?></a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
