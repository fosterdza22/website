<?php
require_once __DIR__ . '/functions.php';
$user = current_user();
$flash = get_flash();
$cartCount = is_logged_in() && $user['role'] === 'student' ? cart_count() : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' | ' : '' ?>Hostel Agency</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<script>window.BASE_URL = "<?= BASE_URL ?>";</script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top" aria-label="Main navigation">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php"><i class="bi bi-building"></i> Hostel Agency</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php if ($user && $user['role'] === 'student'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/student/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/student/hostels.php"><i class="bi bi-search"></i> Hostels</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/student/shop.php"><i class="bi bi-bag"></i> Shop</a></li>
          <li class="nav-item">
            <a class="nav-link position-relative" href="<?= BASE_URL ?>/student/cart.php">
              <i class="bi bi-cart3"></i> Cart
              <?php if ($cartCount > 0): ?>
                <span class="badge rounded-pill bg-warning text-dark cart-badge"><?= (int)$cartCount ?></span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">More</a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/compare.php"><i class="bi bi-bar-chart"></i> Compare Hostels</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/bookings.php"><i class="bi bi-clock-history"></i> My Bookings</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/orders.php"><i class="bi bi-box-seam"></i> My Orders</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/birthday.php"><i class="bi bi-gift"></i> My Birthday</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/testimonial.php"><i class="bi bi-chat-quote"></i> Share Experience</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/news.php"><i class="bi bi-newspaper"></i> News</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
            </ul>
          </li>
          <li class="nav-item ms-lg-2">
            <a href="<?= BASE_URL ?>/student/profile.php" class="d-flex align-items-center text-decoration-none">
              <img src="<?= h(avatar_url($user['profile_picture'] ?? null, $user['full_name'])) ?>" alt="Your profile picture" class="nav-avatar">
            </a>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        <?php elseif ($user && $user['role'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="bi bi-speedometer2"></i> Analytics</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/hostels.php"><i class="bi bi-houses"></i> Hostels</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/bookings.php"><i class="bi bi-journal-check"></i> Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/products.php"><i class="bi bi-bag"></i> Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/orders.php"><i class="bi bi-box-seam"></i> Orders</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">More</a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/birthdays.php"><i class="bi bi-gift"></i> Student Birthdays</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/testimonials.php"><i class="bi bi-chat-quote"></i> Testimonials</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/news.php"><i class="bi bi-newspaper"></i> Manage News</a></li>
            </ul>
          </li>
          <li class="nav-item ms-lg-2">
            <img src="<?= h(avatar_url($user['profile_picture'] ?? null, $user['full_name'])) ?>" alt="Admin profile picture" class="nav-avatar">
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php#hostels">Hostels</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php#testimonials">Testimonials</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/news.php">News</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-light btn-sm ms-lg-2 text-primary fw-semibold" href="<?= BASE_URL ?>/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php if ($flash): ?>
  <div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : h($flash['type']) ?> alert-dismissible fade show" role="alert">
      <?= h($flash['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
<?php endif; ?>
<main>