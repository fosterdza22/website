<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Feedback & Questions';
$user = current_user();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $type = ($_POST['type'] ?? 'feedback') === 'question' ? 'question' : 'feedback';
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '') {
        $errors[] = 'Please provide your name and email.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($subject === '') {
        $errors[] = 'Please add a short subject.';
    }
    if ($message === '' || strlen($message) < 10) {
        $errors[] = 'Please write a message of at least 10 characters.';
    }

    if (!$errors) {
        $userId = $user['id'] ?? null;
        $pdo->prepare("INSERT INTO feedback_messages (user_id, name, email, type, subject, message) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$userId, $name, $email, $type, $subject, $message]);
        $success = true;
    }
}

// If logged in, show their past submissions and any admin replies
$myMessages = [];
if ($user) {
    $stmt = $pdo->prepare("SELECT * FROM feedback_messages WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $myMessages = $stmt->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <h2 class="mb-1">Feedback &amp; Questions</h2>
      <p class="text-muted mb-4">Tell us what's working, what isn't, or ask us anything — we read every message.</p>

      <div class="auth-box" style="max-width:100%;">
        <?php if ($success): ?>
          <div class="alert alert-success">Thanks! Your message has been sent. <?= $user ? 'You can see our reply below once we respond.' : "We'll reply to the email you provided." ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>

        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">I'd like to...</label>
            <div class="btn-group w-100" role="group">
              <input type="radio" class="btn-check" name="type" id="typeFeedback" value="feedback" checked>
              <label class="btn btn-outline-primary" for="typeFeedback"><i class="bi bi-chat-heart"></i> Give Feedback</label>
              <input type="radio" class="btn-check" name="type" id="typeQuestion" value="question">
              <label class="btn btn-outline-primary" for="typeQuestion"><i class="bi bi-question-circle"></i> Ask a Question</label>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Your Name</label>
              <input type="text" class="form-control" name="name" required value="<?= h($user['full_name'] ?? ($_POST['name'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Your Email</label>
              <input type="email" class="form-control" name="email" required value="<?= h($user['email'] ?? ($_POST['email'] ?? '')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Subject</label>
              <input type="text" class="form-control" name="subject" required placeholder="e.g. Payment didn't go through" value="<?= h($_POST['subject'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Message</label>
              <textarea class="form-control" name="message" rows="5" required placeholder="Share the details here..."><?= h($_POST['message'] ?? '') ?></textarea>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100 mt-3">Send Message</button>
        </form>
      </div>

      <?php if ($myMessages): ?>
        <h4 class="mt-5">Your Previous Messages</h4>
        <?php foreach ($myMessages as $m): ?>
          <div class="card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <strong><?= h($m['subject']) ?></strong>
              <span class="badge bg-<?= $m['status'] === 'resolved' ? 'success' : ($m['status'] === 'in_progress' ? 'warning' : 'secondary') ?>"><?= h(ucfirst(str_replace('_',' ',$m['status']))) ?></span>
            </div>
            <p class="small text-muted mb-2 text-capitalize"><?= h($m['type']) ?> &middot; <?= date('d M Y', strtotime($m['created_at'])) ?></p>
            <p class="mb-2"><?= nl2br(h($m['message'])) ?></p>
            <?php if ($m['admin_reply']): ?>
              <div class="wish-card p-2 mt-2">
                <p class="small fw-semibold mb-1"><i class="bi bi-reply-fill"></i> Our Reply</p>
                <p class="mb-0"><?= nl2br(h($m['admin_reply'])) ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>