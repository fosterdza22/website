<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'News';

$posts = $pdo->query("SELECT * FROM news_posts WHERE is_published = 1 ORDER BY published_at DESC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <h2 class="mb-1">News &amp; Announcements</h2>
  <p class="text-muted mb-4">The latest updates from Hostel Agency and our partner hostels.</p>

  <div class="row g-4">
    <?php if (!$posts): ?>
      <p class="text-muted">No news posted yet — check back soon.</p>
    <?php endif; ?>
    <?php foreach ($posts as $p): ?>
      <div class="col-md-6">
        <div class="card news-card h-100">
          <?php if ($p['image']): ?>
            <img src="<?= h(media_url($p['image'])) ?>" class="news-card-img" alt="<?= h($p['title']) ?>">
          <?php endif; ?>
          <div class="card-body">
            <p class="small text-muted mb-1"><?= date('d M Y', strtotime($p['published_at'])) ?></p>
            <h5 class="card-title"><?= h($p['title']) ?></h5>
            <p class="card-text"><?= nl2br(h($p['body'])) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
