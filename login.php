<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Login';

if (is_logged_in()) {
    redirect($_SESSION['user']['role'] === 'admin' ? '/admin/dashboard.php' : '/student/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid form submission, please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'profile_picture' => $user['profile_picture'],
            ];
            set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect($user['role'] === 'admin' ? '/admin/dashboard.php' : '/student/dashboard.php');
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="container">
  <div class="auth-box">
    <h3 class="mb-4 text-center">Log In</h3>
    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><?= h($e) ?></div>
    <?php endforeach; ?>
    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Log In</button>
    </form>
    <p class="text-center mt-3 mb-0">Don't have an account? <a href="<?= BASE_URL ?>/register.php">Register</a></p>
    <p class="text-center small text-muted mt-2">Admin demo: admin@hostelagency.com</p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
