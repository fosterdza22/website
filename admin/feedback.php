<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Feedback & Questions';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply']) && verify_csrf($_POST['csrf'] ?? '')) {
    $id = (int)($_POST['id'] ?? 0);
    $reply = trim($_POST['admin_reply'] ?? '');
    if ($reply !== '') {
        $pdo->prepare("UPDATE feedback_messages SET admin_reply = ?, status = 'resolved', replied_at = NOW() WHERE id = ?")
            ->execute([$reply, $id]);
        set_flash('success', 'Reply sent.');
    }
    redirect('/admin/feedback.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_in_progress']) && verify_csrf($_POST['csrf'] ?? '')) {
    $pdo->prepare("UPDATE feedback_messages SET status = 'in_progress' WHERE id = ?")->execute([(int)$_POST['id']]);
    redirect('/admin/feedback.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && verify_csrf($_POST['csrf'] ?? '')) {
    $pdo->prepare("DELETE FROM feedback_messages WHERE id = ?")->execute([(int)$_POST['id']]);
    set_flash('success', 'Message deleted.');
    redirect('/admin/feedback.php');
}

$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT * FROM feedback_messages WHERE 1=1";
$params = [];
if (in_array($typeFilter, ['feedback','question'], true)) {
    $sql .= " AND type = ?";
    $params[] = $typeFilter;
}
if (in_array($statusFilter, ['new','in_progress','resolved'], true)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

$newCount = $pdo->query("SELECT COUNT(*) c FROM feedback_messages WHERE status = 'new'")->fetch()['c'];

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2><i class="bi bi-chat-square-text"></i> Feedback &amp; Questions <?php if ($newCount): ?><span class="badge bg-warning"><?= (int)$newCount ?> new</span><?php endif; ?></h2>
    <form class="d-flex gap-2">
      <select class="form-select form-select-sm" name="type" onchange="this.form.submit()">
        <option value="">All Types</option>
        <option value="feedback" <?= $typeFilter==='feedback'?'selected':'' ?>>Feedback</option>
        <option value="question" <?= $typeFilter==='question'?'selected':'' ?>>Question</option>
      </select>
      <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="new" <?= $statusFilter==='new'?'selected':'' ?>>New</option>
        <option value="in_progress" <?= $statusFilter==='in_progress'?'selected':'' ?>>In Progress</option>
        <option value="resolved" <?= $statusFilter==='resolved'?'selected':'' ?>>Resolved</option>
      </select>
    </form>
  </div>

  <?php if (!$messages): ?>
    <p class="text-muted">No messages found.</p>
  <?php endif; ?>

  <?php foreach ($messages as $m): ?>
    <div class="card p-3 mb-3">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
        <div>
          <strong><?= h($m['subject']) ?></strong>
          <span class="badge <?= $m['type'] === 'question' ? 'bg-info' : 'bg-secondary' ?> text-capitalize ms-1"><?= h($m['type']) ?></span>
          <span class="badge bg-<?= $m['status'] === 'resolved' ? 'success' : ($m['status'] === 'in_progress' ? 'warning' : 'secondary') ?> ms-1"><?= h(ucfirst(str_replace('_',' ',$m['status']))) ?></span>
        </div>
        <span class="small text-muted"><?= date('d M Y, g:ia', strtotime($m['created_at'])) ?></span>
      </div>
      <p class="small text-muted mb-2"><?= h($m['name']) ?> &lt;<?= h($m['email']) ?>&gt;</p>
      <p class="mb-2"><?= nl2br(h($m['message'])) ?></p>

      <?php if ($m['admin_reply']): ?>
        <div class="wish-card p-2 mb-2">
          <p class="small fw-semibold mb-1"><i class="bi bi-reply-fill"></i> Your Reply (<?= date('d M Y', strtotime($m['replied_at'])) ?>)</p>
          <p class="mb-0"><?= nl2br(h($m['admin_reply'])) ?></p>
        </div>
      <?php endif; ?>

      <form method="post" class="d-flex gap-2 mt-2">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
        <input type="text" class="form-control form-control-sm" name="admin_reply" placeholder="Type a reply..." value="<?= h($m['admin_reply']) ?>">
        <button type="submit" name="reply" value="1" class="btn btn-sm btn-primary text-nowrap">Send Reply</button>
        <?php if ($m['status'] === 'new'): ?>
          <button type="submit" name="mark_in_progress" value="1" class="btn btn-sm btn-outline-secondary text-nowrap">Mark In Progress</button>
        <?php endif; ?>
        <button type="submit" name="delete" value="1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?');">Delete</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>