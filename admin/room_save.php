<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    set_flash('error', 'Invalid request.');
    redirect('/admin/hostels.php');
}

$id = (int)($_POST['id'] ?? 0);
$hostelId = (int)($_POST['hostel_id'] ?? 0);
$type = $_POST['type'] ?? 'single';
$price = (float)($_POST['price_per_year'] ?? 0);
$size = $_POST['size_sqm'] !== '' ? (float)$_POST['size_sqm'] : null;
$furnishing = trim($_POST['furnishing'] ?? '');
$totalRooms = (int)($_POST['total_rooms'] ?? 10);

if ($id) {
    $stmt = $pdo->prepare("UPDATE room_types SET type=?, price_per_year=?, size_sqm=?, furnishing=?, total_rooms=? WHERE id=?");
    $stmt->execute([$type, $price, $size, $furnishing, $totalRooms, $id]);
    set_flash('success', 'Room type updated.');
} else {
    $stmt = $pdo->prepare("INSERT INTO room_types (hostel_id, type, price_per_year, size_sqm, furnishing, total_rooms, booked_rooms)
        VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->execute([$hostelId, $type, $price, $size, $furnishing, $totalRooms]);
    set_flash('success', 'Room type added.');
}

redirect("/admin/rooms.php?hostel_id=$hostelId");
