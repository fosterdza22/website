<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    set_flash('error', 'Invalid request.');
    redirect('/admin/orders.php');
}

$id = (int)($_POST['id'] ?? 0);
$newStatus = $_POST['status'] ?? 'pending';
$validStatuses = ['pending', 'processing', 'out_for_delivery', 'delivered', 'cancelled'];

if (in_array($newStatus, $validStatuses, true)) {
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
    set_flash('success', 'Order status updated.');
}

redirect('/admin/orders.php');
