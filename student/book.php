<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    set_flash('error', 'Invalid request.');
    redirect('/student/hostels.php');
}

$user = current_user();
$hostelId   = (int)($_POST['hostel_id'] ?? 0);
$roomTypeId = (int)($_POST['room_type_id'] ?? 0);
$year       = trim($_POST['academic_year'] ?? '2026/2027');
$plan       = ($_POST['payment_plan'] ?? 'full') === 'installment' ? 'installment' : 'full';

$stmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ? AND hostel_id = ?");
$stmt->execute([$roomTypeId, $hostelId]);
$room = $stmt->fetch();

if (!$room) {
    set_flash('error', 'Selected room type not found.');
    redirect("/student/hostel_detail.php?id=$hostelId");
}

if ($room['booked_rooms'] >= $room['total_rooms']) {
    set_flash('error', 'Sorry, that room type is fully booked.');
    redirect("/student/hostel_detail.php?id=$hostelId");
}

$pdo->beginTransaction();
try {
    $price = (float)$room['price_per_year'];

    $insert = $pdo->prepare("INSERT INTO bookings (user_id, hostel_id, room_type_id, academic_year, status, amount, payment_plan, payment_status, amount_paid)
        VALUES (?, ?, ?, ?, 'pending', ?, ?, 'unpaid', 0)");
    $insert->execute([$user['id'], $hostelId, $roomTypeId, $year, $price, $plan]);
    $bookingId = (int)$pdo->lastInsertId();

    $update = $pdo->prepare("UPDATE room_types SET booked_rooms = booked_rooms + 1 WHERE id = ?");
    $update->execute([$roomTypeId]);

    if ($plan === 'installment') {
        $amounts = [
            round($price * 0.40, 2),
            round($price * 0.30, 2),
        ];
        $amounts[] = round($price - $amounts[0] - $amounts[1], 2); // remainder to avoid rounding drift

        $dueDates = [
            date('Y-m-d'),
            date('Y-m-d', strtotime('+30 days')),
            date('Y-m-d', strtotime('+60 days')),
        ];

        $planStmt = $pdo->prepare("INSERT INTO installment_plans (booking_id, installment_number, amount_due, due_date, status) VALUES (?, ?, ?, ?, 'unpaid')");
        foreach ($amounts as $i => $amt) {
            $planStmt->execute([$bookingId, $i + 1, $amt, $dueDates[$i]]);
        }
    }

    $pdo->commit();
    set_flash('success', 'Room reserved! Complete your payment below to confirm your booking.');
} catch (Exception $e) {
    $pdo->rollBack();
    set_flash('error', 'Something went wrong. Please try again.');
    redirect('/student/hostels.php');
}

redirect('/student/payment/pay.php?booking_id=' . $bookingId);
