<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

/** Sanitize output */
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Build a URL to another page in this app, respecting the install folder (BASE_URL) */
function u($path = '') {
    return BASE_URL . $path;
}

/**
 * Resolve a stored image/video path for display.
 * Seed data uses full external URLs (https://picsum.photos/...) which are
 * returned as-is; uploaded files are stored as root-relative app paths
 * (e.g. /assets/uploads/hostels/3/xxx.jpg) and need BASE_URL prefixed.
 */
function media_url($path) {
    if (!$path) return '';
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return BASE_URL . $path;
}

/** Redirect helper (path-only, e.g. '/login.php' — BASE_URL is added automatically) */
function redirect($path) {
    header('Location: ' . u($path));
    exit;
}

/** Flash messages */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Auth guards */
function current_user() {
    return $_SESSION['user'] ?? null;
}
function is_logged_in() {
    return isset($_SESSION['user']);
}
function is_admin() {
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('/login.php');
    }
}
function require_admin() {
    if (!is_admin()) {
        set_flash('error', 'Admin access required.');
        redirect('/login.php');
    }
}
function require_student() {
    require_login();
    if ($_SESSION['user']['role'] !== 'student') {
        redirect('/admin/dashboard.php');
    }
}

/** CSRF token */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/** Format currency */
function money($amount) {
    return 'GH₵ ' . number_format((float)$amount, 2);
}

/** Get all amenities for a hostel */
function get_hostel_amenities($pdo, $hostelId) {
    $stmt = $pdo->prepare("SELECT a.id, a.name, a.icon FROM amenities a
        JOIN hostel_amenities ha ON ha.amenity_id = a.id
        WHERE ha.hostel_id = ?");
    $stmt->execute([$hostelId]);
    return $stmt->fetchAll();
}

/** Get room types for a hostel */
function get_room_types($pdo, $hostelId) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE hostel_id = ? ORDER BY price_per_year ASC");
    $stmt->execute([$hostelId]);
    return $stmt->fetchAll();
}

/** Get gallery photos for a hostel */
function get_hostel_photos($pdo, $hostelId) {
    $stmt = $pdo->prepare("SELECT * FROM hostel_photos WHERE hostel_id = ?");
    $stmt->execute([$hostelId]);
    return $stmt->fetchAll();
}

/** Get gallery videos for a hostel */
function get_hostel_videos($pdo, $hostelId) {
    $stmt = $pdo->prepare("SELECT * FROM hostel_videos WHERE hostel_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$hostelId]);
    return $stmt->fetchAll();
}

/** Get lowest price for a hostel (used for sorting/display) */
function get_min_price($pdo, $hostelId) {
    $stmt = $pdo->prepare("SELECT MIN(price_per_year) AS min_price FROM room_types WHERE hostel_id = ?");
    $stmt->execute([$hostelId]);
    return $stmt->fetch()['min_price'] ?? 0;
}

/** Recalculate a booking's amount_paid / payment_status from its successful payments */
function recalc_booking_payment_status($pdo, $bookingId) {
    $paidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS paid FROM payments WHERE booking_id = ? AND status = 'success'");
    $paidStmt->execute([$bookingId]);
    $paid = (float)$paidStmt->fetch()['paid'];

    $bookingStmt = $pdo->prepare("SELECT amount FROM bookings WHERE id = ?");
    $bookingStmt->execute([$bookingId]);
    $total = (float)($bookingStmt->fetch()['amount'] ?? 0);

    $status = 'unpaid';
    if ($paid >= $total && $total > 0) {
        $status = 'paid';
    } elseif ($paid > 0) {
        $status = 'partial';
    }

    $pdo->prepare("UPDATE bookings SET amount_paid = ?, payment_status = ? WHERE id = ?")
        ->execute([$paid, $status, $bookingId]);

    if ($status === 'paid') {
        $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ? AND status = 'pending'")->execute([$bookingId]);
    }

    return ['paid' => $paid, 'total' => $total, 'status' => $status];
}

/** Get approved testimonials for public display (homepage) */
function get_approved_testimonials($pdo, $limit = 6) {
    $limit = (int)$limit;
    $stmt = $pdo->query("SELECT t.*, h.name AS hostel_name FROM testimonials t
        LEFT JOIN hostels h ON h.id = t.hostel_id
        WHERE t.is_approved = 1
        ORDER BY t.created_at DESC LIMIT {$limit}");
    return $stmt->fetchAll();
}

/** Get published news posts, most recent first */
function get_published_news($pdo, $limit = 5) {
    $limit = (int)$limit;
    $stmt = $pdo->query("SELECT * FROM news_posts WHERE is_published = 1 ORDER BY published_at DESC LIMIT {$limit}");
    return $stmt->fetchAll();
}

/**
 * Days remaining until a person's next birthday, based on a 'Y-m-d' date of birth.
 * Returns 0 if today is their birthday, or null if no DOB is set / invalid.
 */
function days_until_birthday($dob) {
    if (!$dob) return null;
    try {
        $today = new DateTime('today');
        $bday = new DateTime($dob);
        $next = new DateTime($today->format('Y') . '-' . $bday->format('m') . '-' . $bday->format('d'));
        if ($next < $today) {
            $next->modify('+1 year');
        }
        return (int)$today->diff($next)->days;
    } catch (Exception $e) {
        return null;
    }
}

function is_birthday_today($dob) {
    return days_until_birthday($dob) === 0;
}

function format_birthday($dob) {
    if (!$dob) return '';
    try {
        return (new DateTime($dob))->format('F j');
    } catch (Exception $e) {
        return '';
    }
}

/** Get birthday wishes received by a student, most recent first */
function get_birthday_wishes($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT bw.*, u.full_name AS sent_by_name FROM birthday_wishes bw
        LEFT JOIN users u ON u.id = bw.sent_by
        WHERE bw.user_id = ? ORDER BY bw.sent_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/** Get all students with a DOB set, annotated with days_until (for the admin birthdays page) */
function get_students_with_birthdays($pdo) {
    $stmt = $pdo->query("SELECT id, full_name, email, date_of_birth FROM users
        WHERE role = 'student' AND date_of_birth IS NOT NULL");
    $students = $stmt->fetchAll();
    foreach ($students as &$s) {
        $s['days_until'] = days_until_birthday($s['date_of_birth']);
    }
    unset($s);
    usort($students, fn($a, $b) => $a['days_until'] <=> $b['days_until']);
    return $students;
}

/** Resolve a user's avatar URL, falling back to a generated initials avatar */
function avatar_url($profilePicture, $fullName = '?') {
    if ($profilePicture) {
        return media_url($profilePicture);
    }
    $initials = urlencode(mb_substr(trim($fullName), 0, 1) ?: '?');
    return "https://ui-avatars.com/api/?name={$initials}&background=0d47a1&color=fff&size=128";
}

/** ---------------- Shop / Orders helpers ---------------- */

/** Current session cart as [product_id => quantity] */
function cart_items() {
    return $_SESSION['cart'] ?? [];
}
function cart_count() {
    return array_sum(cart_items());
}
function cart_add($productId, $qty = 1) {
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
}
function cart_set($productId, $qty) {
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
}
function cart_clear() {
    unset($_SESSION['cart']);
}

/** Build cart line items (with live product data) and totals */
function cart_details($pdo) {
    $items = cart_items();
    $lines = [];
    $subtotal = 0;
    if ($items) {
        $ids = array_map('intval', array_keys($items));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();
        foreach ($products as $p) {
            $qty = (int)($items[$p['id']] ?? 0);
            if ($qty <= 0) continue;
            $lineTotal = $qty * (float)$p['price'];
            $subtotal += $lineTotal;
            $lines[] = ['product' => $p, 'quantity' => $qty, 'line_total' => $lineTotal];
        }
    }
    return ['lines' => $lines, 'subtotal' => $subtotal];
}

/** Recalculate an order's amount_paid / payment_status from its successful payments */
function recalc_order_payment_status($pdo, $orderId) {
    $paidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS paid FROM order_payments WHERE order_id = ? AND status = 'success'");
    $paidStmt->execute([$orderId]);
    $paid = (float)$paidStmt->fetch()['paid'];

    $orderStmt = $pdo->prepare("SELECT total_amount FROM orders WHERE id = ?");
    $orderStmt->execute([$orderId]);
    $total = (float)($orderStmt->fetch()['total_amount'] ?? 0);

    $status = $paid >= $total && $total > 0 ? 'paid' : 'unpaid';

    $pdo->prepare("UPDATE orders SET amount_paid = ?, payment_status = ? WHERE id = ?")
        ->execute([$paid, $status, $orderId]);

    if ($status === 'paid') {
        $pdo->prepare("UPDATE orders SET status = 'processing' WHERE id = ? AND status = 'pending'")->execute([$orderId]);
    }

    return ['paid' => $paid, 'total' => $total, 'status' => $status];
}
