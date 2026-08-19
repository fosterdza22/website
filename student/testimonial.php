<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'Share Your Experience';
$user = current_user();

$stmt = $pdo->prepare("SELECT DISTINCT h.id, h.name FROM bookings b
    JOIN hostels h ON h.id = b.hostel_id
    WHERE b.user_id = ? ORDER BY h.name");
$stmt->execute([$user['id']]);
$bookedHostels = $stmt->fetchAll();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $hostelId = (int)($_POST['hostel_id'] ?? 0) ?: null;
    $rating   = (int)($_POST['rating'] ?? 5);
    $message  = trim($_POST['message'] ?? '');

    if ($message === '' || strlen($message) < 10) {
        $errors[] = 'Please share a few words about your experience (at least 10 characters).';
    }
    if ($rating < 1 || $rating > 5) {
        $rating = 5;
    }

    if (!$errors) {
        $pdo->prepare("INSERT INTO testimonials (user_id, hostel_id, display_name, rating, message, is_approved)
            VALUES (?, ?, ?, ?, ?, 0)")
            ->execute([$user['id'], $hostelId, $user['full_name'], $rating, $message]);
        $success = true;
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="auth-box" style="max-width:600px;">
    <h3 class="mb-2">Share Your Experience</h3>
    <p class="text-muted">Your testimonial helps other students choose the right hostel. It'll appear on our homepage after a quick review.</p>

    <?php if ($success): ?>
      <div class="alert alert-success">Thank you! Your testimonial has been submitted and is pending approval.</div>
    <?php endif; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label">Which hostel? (optional)</label>
        <select class="form-select" name="hostel_id">
          <option value="">General feedback</option>
          <?php foreach ($bookedHostels as $h): ?>
            <option value="<?= (int)$h['id'] ?>"><?= h($h['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Rating</label>
        <select class="form-select" name="rating">
          <option value="5">★★★★★ Excellent</option>
          <option value="4">★★★★☆ Good</option>
          <option value="3">★★★☆☆ Average</option>
          <option value="2">★★☆☆☆ Poor</option>
          <option value="1">★☆☆☆☆ Very Poor</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Your Testimonial</label>
        <textarea class="form-control" name="message" rows="4" required placeholder="Tell other students what your experience was like..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary w-100">Submit Testimonial</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
