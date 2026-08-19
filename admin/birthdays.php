<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Student Birthdays';

$students = get_students_with_birthdays($pdo);
$todayBirthdays = array_filter($students, fn($s) => $s['days_until'] === 0);
$upcoming = array_filter($students, fn($s) => $s['days_until'] > 0 && $s['days_until'] <= 30);

$recentWishes = $pdo->query("SELECT bw.*, u.full_name AS student_name, s.full_name AS sender_name
    FROM birthday_wishes bw
    JOIN users u ON u.id = bw.user_id
    LEFT JOIN users s ON s.id = bw.sent_by
    ORDER BY bw.sent_at DESC LIMIT 10")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2><i class="bi bi-gift-fill text-danger"></i> Student Birthdays</h2>
  <p class="text-muted">Wish students a happy birthday — messages appear on their dashboard and birthday page.</p>

  <?php if ($todayBirthdays): ?>
    <h4 class="mt-4">🎂 Celebrating Today</h4>
    <div class="row g-3 mb-4">
      <?php foreach ($todayBirthdays as $s): ?>
        <div class="col-md-6">
          <div class="card p-3 border-warning">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <strong><?= h($s['full_name']) ?></strong><br>
                <span class="small text-muted"><?= h($s['email']) ?> &middot; <?= h(format_birthday($s['date_of_birth'])) ?></span>
              </div>
              <span class="badge bg-warning text-dark">Today!</span>
            </div>
            <form method="post" action="<?= BASE_URL ?>/admin/birthday_send.php">
              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
              <div class="input-group">
                <input type="text" class="form-control" name="message" value="Happy Birthday, <?= h($s['full_name']) ?>! Wishing you a fantastic year ahead. 🎉🎂" required>
                <button class="btn btn-warning">Send Wish</button>
              </div>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h4 class="mt-4">Upcoming (next 30 days)</h4>
  <div class="table-responsive mb-4">
    <table class="table table-hover bg-white align-middle">
      <thead class="table-light">
        <tr><th>Student</th><th>Email</th><th>Birthday</th><th>Days Until</th><th>Send a Wish</th></tr>
      </thead>
      <tbody>
        <?php if (!$upcoming): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No student birthdays in the next 30 days.</td></tr>
        <?php endif; ?>
        <?php foreach ($upcoming as $s): ?>
          <tr>
            <td><?= h($s['full_name']) ?></td>
            <td><?= h($s['email']) ?></td>
            <td><?= h(format_birthday($s['date_of_birth'])) ?></td>
            <td><span class="badge bg-info text-dark"><?= (int)$s['days_until'] ?> day<?= $s['days_until'] == 1 ? '' : 's' ?></span></td>
            <td>
              <form method="post" action="<?= BASE_URL ?>/admin/birthday_send.php" class="d-flex gap-1">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
                <input type="text" class="form-control form-control-sm" name="message" placeholder="Early wishes message..." style="min-width:220px;">
                <button class="btn btn-sm btn-outline-primary">Send</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h4>All Students With a Birthday on File</h4>
  <div class="table-responsive mb-4">
    <table class="table table-hover bg-white align-middle">
      <thead class="table-light">
        <tr><th>Student</th><th>Email</th><th>Birthday</th><th>Days Until</th><th>Send a Wish</th></tr>
      </thead>
      <tbody>
        <?php if (!$students): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No students have added a date of birth yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($students as $s): ?>
          <tr>
            <td><?= h($s['full_name']) ?></td>
            <td><?= h($s['email']) ?></td>
            <td><?= h(format_birthday($s['date_of_birth'])) ?></td>
            <td><?= (int)$s['days_until'] ?> day<?= $s['days_until'] == 1 ? '' : 's' ?></td>
            <td>
              <form method="post" action="<?= BASE_URL ?>/admin/birthday_send.php" class="d-flex gap-1">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="user_id" value="<?= (int)$s['id'] ?>">
                <input type="text" class="form-control form-control-sm" name="message" placeholder="Custom message..." style="min-width:220px;">
                <button class="btn btn-sm btn-outline-primary">Send</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h4>Recently Sent Wishes</h4>
  <div class="table-responsive">
    <table class="table bg-white align-middle">
      <thead class="table-light"><tr><th>Student</th><th>Message</th><th>Sent By</th><th>Sent At</th></tr></thead>
      <tbody>
        <?php if (!$recentWishes): ?>
          <tr><td colspan="4" class="text-center text-muted py-4">No wishes sent yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentWishes as $w): ?>
          <tr>
            <td><?= h($w['student_name']) ?></td>
            <td><?= h($w['message']) ?></td>
            <td><?= h($w['sender_name'] ?? 'Admin') ?></td>
            <td><?= date('d M Y, g:ia', strtotime($w['sent_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
