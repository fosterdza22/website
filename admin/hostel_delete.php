<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM hostels WHERE id = ?")->execute([$id]);
    set_flash('success', 'Hostel deleted.');
}
redirect('/admin/hostels.php');
