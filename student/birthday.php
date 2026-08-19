<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'My Birthday';
$user = current_user();

$stmt = $pdo->prepare("SELECT date_of_birth FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$dob = $stmt->fetch()['date_of_birth'] ?? null;

$daysUntil = days_until_birthday($dob);
$isToday = $daysUntil === 0;
$wishes = get_birthday_wishes($pdo, $user['id']);

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2><i class="bi bi-gift-fill text-danger"></i> My Birthday</h2>

  <?php if (!$dob): ?>
    <div class="alert alert-info mt-3">
      You haven't added your date of birth yet. <a href="<?= BASE_URL ?>/student/profile.php">Add it to your profile</a>
      to unlock your birthday countdown — and so admin can wish you well on the day!
    </div>
  <?php elseif ($isToday): ?>
    <div class="card birthday-card text-center p-5 my-4">
      <h1 class="display-4">🎉🎂 Happy Birthday! 🎂🎉</h1>
      <p class="lead mb-0">Everyone at Hostel Agency wishes you a wonderful day, <?= h($user['full_name']) ?>!</p>
    </div>
  <?php else: ?>
    <div class="card birthday-countdown-card text-center p-5 my-4">
      <p class="mb-1 text-uppercase small fw-semibold" style="letter-spacing:1px;">Your birthday is <?= h(format_birthday($dob)) ?></p>
      <div class="display-2 fw-bold"><?= (int)$daysUntil ?></div>
      <p class="lead mb-0">day<?= $daysUntil == 1 ? '' : 's' ?> to go!</p>
    </div>
  <?php endif; ?>

  <h4 class="mt-5">Birthday Wishes You've Received</h4>
  <?php if (!$wishes): ?>
    <p class="text-muted">No birthday wishes yet — check back on your special day!</p>
  <?php endif; ?>
  <div class="row g-3">
    <?php foreach ($wishes as $w): ?>
      <div class="col-md-6">
        <div class="card p-3 wish-card">
          <p class="mb-2">"<?= nl2br(h($w['message'])) ?>"</p>
          <p class="small text-muted mb-0">— <?= h($w['sent_by_name'] ?? 'Hostel Agency Team') ?>, <?= date('d M Y', strtotime($w['sent_at'])) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
