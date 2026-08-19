<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'My Cart';
$user = current_user();

$DELIVERY_FEE = 15.00;
$errors = [];

// Update quantities / remove items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart']) && verify_csrf($_POST['csrf'] ?? '')) {
    foreach ($_POST['qty'] ?? [] as $productId => $qty) {
        cart_set((int)$productId, max(0, min(20, (int)$qty)));
    }
    set_flash('success', 'Cart updated.');
    redirect('/student/cart.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item']) && verify_csrf($_POST['csrf'] ?? '')) {
    cart_set((int)$_POST['remove_item'], 0);
    redirect('/student/cart.php');
}

// Place the order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && verify_csrf($_POST['csrf'] ?? '')) {
    $address = trim($_POST['delivery_address'] ?? '');
    $phone = trim($_POST['delivery_phone'] ?? '');
    $notes = trim($_POST['delivery_notes'] ?? '');

    $cart = cart_details($pdo);
    if (!$cart['lines']) {
        $errors[] = 'Your cart is empty.';
    }
    if ($address === '') {
        $errors[] = 'Please enter a delivery address (hostel name and room number work great).';
    }
    if ($phone === '') {
        $errors[] = 'Please enter a phone number for the delivery rider to reach you.';
    }

    if (!$errors) {
        $subtotal = $cart['subtotal'];
        $total = $subtotal + $DELIVERY_FEE;

        $pdo->beginTransaction();
        try {
            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, delivery_address, delivery_phone, delivery_notes, subtotal, delivery_fee, total_amount, amount_paid, payment_status, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'unpaid', 'pending')");
            $orderStmt->execute([$user['id'], $address, $phone, $notes, $subtotal, $DELIVERY_FEE, $total]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($cart['lines'] as $line) {
                $itemStmt->execute([$orderId, $line['product']['id'], $line['product']['name'], $line['product']['price'], $line['quantity'], $line['line_total']]);
            }

            $pdo->commit();
            cart_clear();
            set_flash('success', 'Order placed! Complete payment to confirm delivery.');
            redirect('/student/order_payment/pay.php?order_id=' . $orderId);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong placing your order. Please try again.';
        }
    }
}

$cart = cart_details($pdo);
$total = $cart['subtotal'] > 0 ? $cart['subtotal'] + $DELIVERY_FEE : 0;

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2><i class="bi bi-cart3"></i> My Cart</h2>

  <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>

  <?php if (!$cart['lines']): ?>
    <div class="alert alert-info">Your cart is empty. <a href="<?= BASE_URL ?>/student/shop.php">Browse the shop</a> to add items.</div>
  <?php else: ?>
    <form method="post" class="mb-4">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <div class="table-responsive">
        <table class="table bg-white align-middle">
          <thead class="table-light"><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Line Total</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($cart['lines'] as $line): ?>
              <tr>
                <td class="d-flex align-items-center gap-2">
                  <img src="<?= h(media_url($line['product']['image'])) ?>" width="50" height="50" style="object-fit:cover;border-radius:6px;" alt="">
                  <?= h($line['product']['name']) ?>
                </td>
                <td><?= money($line['product']['price']) ?></td>
                <td style="max-width:100px;">
                  <input type="number" name="qty[<?= (int)$line['product']['id'] ?>]" value="<?= (int)$line['quantity'] ?>" min="0" max="20" class="form-control form-control-sm">
                </td>
                <td><?= money($line['line_total']) ?></td>
                <td>
                  <button type="submit" name="remove_item" value="<?= (int)$line['product']['id'] ?>" class="btn btn-sm btn-outline-danger">Remove</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" name="update_cart" value="1" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-repeat"></i> Update Quantities</button>
    </form>

    <div class="row">
      <div class="col-md-6">
        <div class="card p-4">
          <h5>Delivery Details</h5>
          <form method="post">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="place_order" value="1">
            <div class="mb-3">
              <label class="form-label">Delivery Address</label>
              <input type="text" class="form-control" name="delivery_address" placeholder="e.g. Unity Hostel, Room B12" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Phone Number</label>
              <input type="tel" class="form-control" name="delivery_phone" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Delivery Notes (optional)</label>
              <textarea class="form-control" name="delivery_notes" rows="2" placeholder="Gate code, landmark, preferred time..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Place Order &amp; Pay</button>
          </form>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card p-4">
          <h5>Order Summary</h5>
          <table class="table">
            <tr><td>Subtotal</td><td class="text-end"><?= money($cart['subtotal']) ?></td></tr>
            <tr><td>Delivery Fee</td><td class="text-end"><?= money($DELIVERY_FEE) ?></td></tr>
            <tr class="fw-bold"><td>Total</td><td class="text-end"><?= money($total) ?></td></tr>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
