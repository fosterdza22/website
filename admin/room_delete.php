<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$hostelId = (int)($_POST['hostel_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM room_types WHERE id = ?")->execute([$id]);
    set_flash('success', 'Room type deleted.');
}

redirect("/admin/rooms.php?hostel_id=$hostelId");
