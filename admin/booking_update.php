<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    set_flash('error', 'Invalid request.');
    redirect('/admin/bookings.php');
}

$id = (int)($_POST['id'] ?? 0);
$newStatus = $_POST['status'] ?? 'pending';
if (!in_array($newStatus, ['pending','confirmed','cancelled'], true)) {
    redirect('/admin/bookings.php');
}

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if ($booking && $booking['status'] !== $newStatus) {
    if ($newStatus === 'cancelled' && $booking['status'] !== 'cancelled') {
        $pdo->prepare("UPDATE room_types SET booked_rooms = GREATEST(booked_rooms - 1, 0) WHERE id = ?")
            ->execute([$booking['room_type_id']]);
    } elseif ($booking['status'] === 'cancelled' && $newStatus !== 'cancelled') {
        $pdo->prepare("UPDATE room_types SET booked_rooms = booked_rooms + 1 WHERE id = ?")
            ->execute([$booking['room_type_id']]);
    }
    $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
    set_flash('success', 'Booking status updated.');
}

redirect('/admin/bookings.php');
