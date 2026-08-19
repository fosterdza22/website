<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Manage Testimonials';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['approve'])) {
        $pdo->prepare("UPDATE testimonials SET is_approved = 1 WHERE id = ?")->execute([$id]);
        set_flash('success', 'Testimonial approved and now visible on the homepage.');
    } elseif (isset($_POST['unapprove'])) {
        $pdo->prepare("UPDATE testimonials SET is_approved = 0 WHERE id = ?")->execute([$id]);
        set_flash('success', 'Testimonial hidden from the homepage.');
    } elseif (isset($_POST['delete'])) {
        $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$id]);
        set_flash('success', 'Testimonial deleted.');
    }
    redirect('/admin/testimonials.php');
}

$testimonials = $pdo->query("SELECT t.*, h.name AS hostel_name FROM testimonials t
    LEFT JOIN hostels h ON h.id = t.hostel_id
    ORDER BY t.is_approved ASC, t.created_at DESC")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>Manage Testimonials</h2>
  <p class="text-muted">Approve student testimonials to feature them on the homepage.</p>

  <div class="row g-3">
    <?php if (!$testimonials): ?>
      <p class="text-muted">No testimonials submitted yet.</p>
    <?php endif; ?>
    <?php foreach ($testimonials as $t): ?>
      <div class="col-md-6">
        <div class="card p-3 h-100">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <strong><?= h($t['display_name']) ?></strong>
              <?php if ($t['hostel_name']): ?><span class="small text-muted"> &middot; <?= h($t['hostel_name']) ?></span><?php endif; ?>
            </div>
            <span class="badge bg-<?= $t['is_approved'] ? 'success' : 'warning' ?>"><?= $t['is_approved'] ? 'Approved' : 'Pending' ?></span>
          </div>
          <div class="mb-2 text-warning"><?= str_repeat('★', (int)$t['rating']) . str_repeat('☆', 5 - (int)$t['rating']) ?></div>
          <p class="flex-grow-1">"<?= nl2br(h($t['message'])) ?>"</p>
          <p class="small text-muted"><?= date('d M Y', strtotime($t['created_at'])) ?></p>
          <div class="d-flex gap-2">
            <?php if (!$t['is_approved']): ?>
              <form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn btn-sm btn-success" name="approve" value="1">Approve</button></form>
            <?php else: ?>
              <form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn btn-sm btn-outline-secondary" name="unapprove" value="1">Unpublish</button></form>
            <?php endif; ?>
            <form method="post" onsubmit="return confirm('Delete this testimonial?');"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn btn-sm btn-outline-danger" name="delete" value="1">Delete</button></form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
