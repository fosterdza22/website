<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Home';

$stmt = $pdo->query("SELECT h.*, MIN(rt.price_per_year) AS min_price
    FROM hostels h LEFT JOIN room_types rt ON rt.hostel_id = h.id
    GROUP BY h.id ORDER BY h.id ASC");
$hostels = $stmt->fetchAll();

// Pull a pool of hostel/room photos for the rotating hero background
$slideStmt = $pdo->query("SELECT photo_path AS img FROM hostel_photos
    UNION ALL
    SELECT main_image AS img FROM hostels
    ORDER BY RAND() LIMIT 10");
$slideImages = array_column($slideStmt->fetchAll(), 'img');
if (!$slideImages) {
    $slideImages = ['https://picsum.photos/seed/hostelfallback/1600/900'];
}

$testimonials = get_approved_testimonials($pdo, 6);
$newsPosts = get_published_news($pdo, 3);
$totalStudents = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='student'")->fetch()['c'];
$totalHostelsCount = count($hostels);

require __DIR__ . '/includes/header.php';
?>
<a href="#hostels" class="skip-link">Skip to hostel listings</a>

<section class="hero position-relative overflow-hidden">
  <div class="hero-bg-slideshow" aria-hidden="true">
    <?php foreach ($slideImages as $i => $img): ?>
      <div class="hero-bg-slide<?= $i === 0 ? ' active' : '' ?>" style="background-image:url('<?= h(media_url($img)) ?>');"></div>
    <?php endforeach; ?>
  </div>
  <div class="hero-overlay"></div>
  <div class="container text-center position-relative" style="z-index:2;">
    <h1 class="display-5">Find Your Perfect Hostel — and Everything Else You Need</h1>
    <p class="lead mb-4">Browse, compare and book verified student hostels near campus, order food and essentials for delivery, all in one place.</p>
    <?php if (!is_logged_in()): ?>
      <a href="<?= BASE_URL ?>/register.php" class="btn btn-warning btn-lg fw-semibold me-2">Get Started</a>
      <a href="#hostels" class="btn btn-outline-light btn-lg">Browse Hostels</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/student/hostels.php" class="btn btn-warning btn-lg fw-semibold me-2">Browse Hostels</a>
      <a href="<?= BASE_URL ?>/student/shop.php" class="btn btn-outline-light btn-lg">Order from Shop</a>
    <?php endif; ?>
  </div>
</section>

<script>
  (function () {
    const slides = document.querySelectorAll('.hero-bg-slide');
    if (slides.length < 2) return;
    let idx = 0;
    setInterval(function () {
      slides[idx].classList.remove('active');
      idx = (idx + 1) % slides.length;
      slides[idx].classList.add('active');
    }, 3000);
  })();
</script>

<section class="stats-bar py-3">
  <div class="container">
    <div class="row text-center g-3">
      <div class="col-6 col-md-3">
        <div class="fs-3 fw-bold"><?= (int)$totalHostelsCount ?>+</div>
        <div class="small text-uppercase" style="letter-spacing:.5px;">Partner Hostels</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="fs-3 fw-bold"><?= (int)$totalStudents ?>+</div>
        <div class="small text-uppercase" style="letter-spacing:.5px;">Students Registered</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="fs-3 fw-bold">100%</div>
        <div class="small text-uppercase" style="letter-spacing:.5px;">Secure Payments</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="fs-3 fw-bold">24/7</div>
        <div class="small text-uppercase" style="letter-spacing:.5px;">Support</div>
      </div>
    </div>
  </div>
</section>

<section class="container py-5" id="hostels">
  <h2 class="mb-4 text-center">Featured Hostels</h2>
  <div class="row g-4">
    <?php foreach ($hostels as $hostel): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card hostel-card">
          <img src="<?= h(media_url($hostel['main_image'])) ?>" alt="Photo of <?= h($hostel['name']) ?>">
          <div class="card-body">
            <h5 class="card-title"><?= h($hostel['name']) ?></h5>
            <p class="card-text text-muted small"><?= h(mb_strimwidth($hostel['description'], 0, 100, '...')) ?></p>
            <p class="mb-2"><i class="bi bi-geo-alt text-primary"></i> <?= h($hostel['distance_to_campus_km']) ?> km from campus</p>
            <span class="price-badge">From <?= money($hostel['min_price']) ?>/yr</span>
          </div>
          <div class="card-footer bg-white border-0 pb-3">
            <a href="<?= BASE_URL ?>/student/hostel_detail.php?id=<?= (int)$hostel['id'] ?>" class="btn btn-primary w-100">View Details</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-white py-5">
  <div class="container">
    <div class="row text-center g-4">
      <div class="col-md-3">
        <i class="bi bi-search fs-1 text-primary"></i>
        <h5 class="mt-2">Browse & Filter</h5>
        <p class="text-muted small">Filter by price, amenities, and distance to campus.</p>
      </div>
      <div class="col-md-3">
        <i class="bi bi-map fs-1 text-primary"></i>
        <h5 class="mt-2">Interactive Map</h5>
        <p class="text-muted small">See exactly where each hostel sits relative to campus.</p>
      </div>
      <div class="col-md-3">
        <i class="bi bi-bag fs-1 text-primary"></i>
        <h5 class="mt-2">Order Essentials</h5>
        <p class="text-muted small">Order food and everyday items, delivered to your hostel.</p>
      </div>
      <div class="col-md-3">
        <i class="bi bi-calendar-check fs-1 text-primary"></i>
        <h5 class="mt-2">Book & Pay Securely</h5>
        <p class="text-muted small">Reserve rooms and pay in full or by installment via Paystack.</p>
      </div>
    </div>
  </div>
</section>

<section class="py-5" id="testimonials">
  <div class="container">
    <h2 class="mb-2 text-center">What Our Students Say</h2>
    <p class="text-muted text-center mb-4">Real feedback from students who've used Hostel Agency.</p>
    <?php if (!$testimonials): ?>
      <p class="text-muted text-center">No testimonials yet — be the first to share your experience!</p>
    <?php endif; ?>
    <div class="row g-4">
      <?php foreach ($testimonials as $t): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card testimonial-card h-100 p-4">
            <div class="text-warning mb-2"><?= str_repeat('★', (int)$t['rating']) . str_repeat('☆', 5 - (int)$t['rating']) ?></div>
            <p class="flex-grow-1">"<?= nl2br(h($t['message'])) ?>"</p>
            <p class="fw-semibold mb-0 mt-2"><?= h($t['display_name']) ?></p>
            <?php if ($t['hostel_name']): ?><p class="small text-muted">Stayed at <?= h($t['hostel_name']) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (is_logged_in() && current_user()['role'] === 'student'): ?>
      <p class="text-center mt-4"><a href="<?= BASE_URL ?>/student/testimonial.php" class="btn btn-outline-primary">Share Your Own Experience</a></p>
    <?php endif; ?>
  </div>
</section>

<section class="bg-white py-5" id="news">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">News of the Week</h2>
      <a href="<?= BASE_URL ?>/news.php" class="btn btn-sm btn-outline-primary">View All News</a>
    </div>
    <?php if (!$newsPosts): ?>
      <p class="text-muted">No news posted yet — check back soon.</p>
    <?php endif; ?>
    <div class="row g-4">
      <?php foreach ($newsPosts as $p): ?>
        <div class="col-md-4">
          <div class="card news-card h-100">
            <?php if ($p['image']): ?>
              <img src="<?= h(media_url($p['image'])) ?>" class="news-card-img" alt="<?= h($p['title']) ?>">
            <?php endif; ?>
            <div class="card-body">
              <p class="small text-muted mb-1"><?= date('d M Y', strtotime($p['published_at'])) ?></p>
              <h5 class="card-title"><?= h($p['title']) ?></h5>
              <p class="card-text"><?= h(mb_strimwidth($p['body'], 0, 140, '...')) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
