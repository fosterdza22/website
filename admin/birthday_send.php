<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    set_flash('error', 'Invalid request.');
    redirect('/admin/birthdays.php');
}

$admin = current_user();
$userId = (int)($_POST['user_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($message === '') {
    $message = 'Happy Birthday! Wishing you a fantastic year ahead. 🎉🎂';
}

$stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$userId]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('error', 'Student not found.');
    redirect('/admin/birthdays.php');
}

$pdo->prepare("INSERT INTO birthday_wishes (user_id, sent_by, message) VALUES (?, ?, ?)")
    ->execute([$userId, $admin['id'], $message]);

set_flash('success', 'Birthday wish sent to ' . $student['full_name'] . '!');
redirect('/admin/birthdays.php');
