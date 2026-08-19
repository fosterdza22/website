<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Manage News';
$admin = current_user();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_news'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $image = trim($_POST['image'] ?? '') ?: null;
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if ($title === '' || $body === '') {
            $errors[] = 'Title and body are required.';
        }

        if (!$errors) {
            if ($id) {
                $pdo->prepare("UPDATE news_posts SET title=?, body=?, image=?, is_published=? WHERE id=?")
                    ->execute([$title, $body, $image, $isPublished, $id]);
                set_flash('success', 'News post updated.');
            } else {
                $pdo->prepare("INSERT INTO news_posts (title, body, image, is_published, created_by) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$title, $body, $image, $isPublished, $admin['id']]);
                set_flash('success', 'News post published.');
            }
            redirect('/admin/news.php');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_news']) && verify_csrf($_POST['csrf'] ?? '')) {
    $pdo->prepare("DELETE FROM news_posts WHERE id = ?")->execute([(int)$_POST['id']]);
    set_flash('success', 'News post deleted.');
    redirect('/admin/news.php');
}

$editId = (int)($_GET['edit'] ?? 0);
$editing = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM news_posts WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
}

$posts = $pdo->query("SELECT * FROM news_posts ORDER BY published_at DESC")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>Manage News <span class="text-muted small">(News of the Week)</span></h2>

  <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>

  <div class="card p-4 mb-4">
    <h5><?= $editing ? 'Edit Post' : 'Add New Post' ?></h5>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="save_news" value="1">
      <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" class="form-control" name="title" required value="<?= h($editing['title'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Body</label>
        <textarea class="form-control" name="body" rows="4" required><?= h($editing['body'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Image URL (optional)</label>
        <input type="text" class="form-control" name="image" value="<?= h($editing['image'] ?? '') ?>" placeholder="https://...">
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_published" id="is_published" <?= (!$editing || $editing['is_published']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_published">Published (visible on homepage)</label>
      </div>
      <button class="btn btn-primary"><?= $editing ? 'Update' : 'Publish' ?> Post</button>
      <?php if ($editing): ?><a href="<?= BASE_URL ?>/admin/news.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
    </form>
  </div>

  <h5>All Posts</h5>
  <div class="table-responsive">
    <table class="table table-hover bg-white align-middle">
      <thead class="table-light"><tr><th>Title</th><th>Status</th><th>Published</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (!$posts): ?>
          <tr><td colspan="4" class="text-center text-muted py-4">No news posts yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($posts as $p): ?>
          <tr>
            <td><?= h($p['title']) ?></td>
            <td><span class="badge bg-<?= $p['is_published'] ? 'success' : 'secondary' ?>"><?= $p['is_published'] ? 'Published' : 'Draft' ?></span></td>
            <td><?= date('d M Y', strtotime($p['published_at'])) ?></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/news.php?edit=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
              <form method="post" class="d-inline" onsubmit="return confirm('Delete this post?');">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="delete_news" value="1">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
