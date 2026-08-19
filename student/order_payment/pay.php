<?php
require_once __DIR__ . '/../../includes/functions.php';
require_student();
$pageTitle = 'Pay for Order';
$user = current_user();

$orderId = (int)($_GET['order_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Order not found.');
    redirect('/student/orders.php');
}

$itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

$balance = (float)$order['total_amount'] - (float)$order['amount_paid'];

require __DIR__ . '/../../includes/header.php';
?>
<div class="container py-4">
  <div class="auth-box" style="max-width:560px;">
    <h3 class="mb-1">Order #<?= (int)$order['id'] ?></h3>
    <p class="text-muted">Delivering to: <?= h($order['delivery_address']) ?></p>

    <table class="table table-sm">
      <thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr><td><?= h($it['product_name']) ?></td><td><?= (int)$it['quantity'] ?></td><td><?= money($it['line_total']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <table class="table">
      <tr><td>Subtotal</td><td class="text-end"><?= money($order['subtotal']) ?></td></tr>
      <tr><td>Delivery Fee</td><td class="text-end"><?= money($order['delivery_fee']) ?></td></tr>
      <tr><td>Amount Paid</td><td class="text-end text-success"><?= money($order['amount_paid']) ?></td></tr>
      <tr class="fw-bold"><td>Balance Due</td><td class="text-end"><?= money($balance) ?></td></tr>
    </table>

    <?php if ($balance <= 0): ?>
      <div class="alert alert-success">This order is fully paid. Your delivery is being processed!</div>
      <a href="<?= BASE_URL ?>/student/orders.php" class="btn btn-primary w-100">View My Orders</a>
    <?php else: ?>
      <form method="post" action="<?= BASE_URL ?>/student/order_payment/initiate.php">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-credit-card"></i> Pay <?= money($balance) ?> with Paystack</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
