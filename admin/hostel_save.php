<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    set_flash('error', 'Invalid request.');
    redirect('/admin/hostels.php');
}

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$address = trim($_POST['address'] ?? '');
$lat = $_POST['latitude'] ?? null;
$lng = $_POST['longitude'] ?? null;
$distance = $_POST['distance_to_campus_km'] ?? 0;
$image = trim($_POST['main_image'] ?? '') ?: 'https://picsum.photos/seed/' . urlencode($name) . '/600/400';
$amenityIds = array_map('intval', $_POST['amenities'] ?? []);

if ($name === '' || $description === '') {
    set_flash('error', 'Name and description are required.');
    redirect($id ? "/admin/hostel_form.php?id=$id" : '/admin/hostel_form.php');
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE hostels SET name=?, description=?, address=?, latitude=?, longitude=?, distance_to_campus_km=?, main_image=? WHERE id=?");
    $stmt->execute([$name, $description, $address, $lat, $lng, $distance, $image, $id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO hostels (name, description, address, latitude, longitude, distance_to_campus_km, main_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $description, $address, $lat, $lng, $distance, $image]);
    $id = $pdo->lastInsertId();
}

$pdo->prepare("DELETE FROM hostel_amenities WHERE hostel_id = ?")->execute([$id]);
if ($amenityIds) {
    $insert = $pdo->prepare("INSERT INTO hostel_amenities (hostel_id, amenity_id) VALUES (?, ?)");
    foreach ($amenityIds as $aid) {
        $insert->execute([$id, $aid]);
    }
}

set_flash('success', 'Hostel saved successfully.');
redirect('/admin/hostels.php');
