<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Register';

if (is_logged_in()) {
    redirect($_SESSION['user']['role'] === 'admin' ? '/admin/dashboard.php' : '/student/dashboard.php');
}

$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
$maxBytes = 5 * 1024 * 1024; // 5 MB
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid form submission, please try again.';
    } else {
        $fullName  = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $studentId = trim($_POST['student_id'] ?? '');
        $dob       = trim($_POST['date_of_birth'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        if ($fullName === '' || $email === '' || $password === '') {
            $errors[] = 'Full name, email and password are required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }
        if ($dob !== '' && !DateTime::createFromFormat('Y-m-d', $dob)) {
            $errors[] = 'Please enter a valid date of birth.';
        }
        $dobValue = $dob !== '' ? $dob : null;

        // --- Profile picture is REQUIRED at sign-up ---
        $pictureFile = $_FILES['profile_picture'] ?? null;
        if (!$pictureFile || $pictureFile['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Please upload a profile picture to complete registration.';
        } elseif ($pictureFile['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Profile picture upload failed. Please try again.';
        } else {
            $ext = strtolower(pathinfo($pictureFile['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Profile picture must be a JPG, PNG, or WEBP image.';
            } elseif ($pictureFile['size'] > $maxBytes) {
                $errors[] = 'Profile picture must be smaller than 5 MB.';
            } elseif (!@getimagesize($pictureFile['tmp_name'])) {
                $errors[] = 'That file does not look like a valid image.';
            }
        }

        if (!$errors) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with that email already exists.';
            }
        }

        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, student_id, date_of_birth, password, role) VALUES (?, ?, ?, ?, ?, ?, 'student')");
            $stmt->execute([$fullName, $email, $phone, $studentId, $dobValue, $hash]);
            $newUserId = (int)$pdo->lastInsertId();

            // Move the profile picture now that we have a user ID for the filename
            $destDir = __DIR__ . '/assets/uploads/profiles';
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $safeName = 'user' . $newUserId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destPath = $destDir . '/' . $safeName;
            $publicPath = '/assets/uploads/profiles/' . $safeName;

            if (move_uploaded_file($pictureFile['tmp_name'], $destPath)) {
                $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?")->execute([$publicPath, $newUserId]);
            }

            set_flash('success', 'Account created successfully! Please log in.');
            redirect('/login.php');
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="container">
  <div class="auth-box" style="max-width:520px;">
    <h3 class="mb-4 text-center">Create Your Student Account</h3>
    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger"><?= h($e) ?></div>
    <?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

      <div class="text-center mb-3">
        <img id="avatarPreview" src="https://ui-avatars.com/api/?name=%3F&background=e3f2fd&color=0d47a1&size=128" alt="Profile picture preview" class="avatar-preview mb-2">
        <label class="form-label d-block fw-semibold" for="profile_picture">Profile Picture <span class="text-danger">*</span></label>
        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png,.webp" required>
        <div class="form-text">Required. JPG, PNG, or WEBP — up to 5 MB.</div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="full_name">Full Name</label>
        <input type="text" class="form-control" id="full_name" name="full_name" required value="<?= h($_POST['full_name'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="student_id">Student ID (optional)</label>
        <input type="text" class="form-control" id="student_id" name="student_id" value="<?= h($_POST['student_id'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="phone">Phone Number</label>
        <input type="tel" class="form-control" id="phone" name="phone" value="<?= h($_POST['phone'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="date_of_birth"><i class="bi bi-gift"></i> Date of Birth (optional)</label>
        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?= h($_POST['date_of_birth'] ?? '') ?>">
        <div class="form-text">So we can wish you happy birthday 🎉</div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" required minlength="6">
      </div>
      <div class="mb-3">
        <label class="form-label" for="confirm_password">Confirm Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
      </div>
      <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
    <p class="text-center mt-3 mb-0">Already have an account? <a href="<?= BASE_URL ?>/login.php">Log in</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
