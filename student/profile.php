<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
$pageTitle = 'My Profile';
$user = current_user();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
$maxBytes = 5 * 1024 * 1024;
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $fullName  = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $dob       = trim($_POST['date_of_birth'] ?? '');
    $newPass   = $_POST['new_password'] ?? '';

    if ($fullName === '') {
        $errors[] = 'Full name cannot be empty.';
    }
    if ($dob !== '' && !DateTime::createFromFormat('Y-m-d', $dob)) {
        $errors[] = 'Please enter a valid date of birth.';
    }
    $dobValue = $dob !== '' ? $dob : null;

    // Optional new profile picture
    $newPicturePath = null;
    if (!empty($_FILES['profile_picture']['name'])) {
        $file = $_FILES['profile_picture'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Profile picture upload failed. Please try again.';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Profile picture must be a JPG, PNG, or WEBP image.';
            } elseif ($file['size'] > $maxBytes) {
                $errors[] = 'Profile picture must be smaller than 5 MB.';
            } elseif (!@getimagesize($file['tmp_name'])) {
                $errors[] = 'That file does not look like a valid image.';
            } else {
                $destDir = __DIR__ . '/../assets/uploads/profiles';
                if (!is_dir($destDir)) mkdir($destDir, 0755, true);
                $safeName = 'user' . $user['id'] . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $destPath = $destDir . '/' . $safeName;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $newPicturePath = '/assets/uploads/profiles/' . $safeName;
                } else {
                    $errors[] = 'Could not save the uploaded picture.';
                }
            }
        }
    }

    if (!$errors) {
        $picToSave = $newPicturePath ?? $profile['profile_picture'];

        if ($newPass !== '') {
            if (strlen($newPass) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } else {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET full_name=?, phone=?, student_id=?, date_of_birth=?, profile_picture=?, password=? WHERE id=?")
                    ->execute([$fullName, $phone, $studentId, $dobValue, $picToSave, $hash, $user['id']]);
            }
        } else {
            $pdo->prepare("UPDATE users SET full_name=?, phone=?, student_id=?, date_of_birth=?, profile_picture=? WHERE id=?")
                ->execute([$fullName, $phone, $studentId, $dobValue, $picToSave, $user['id']]);
        }

        if (!$errors) {
            $_SESSION['user']['full_name'] = $fullName;
            $_SESSION['user']['profile_picture'] = $picToSave;
            $success = true;
            $profile['full_name'] = $fullName;
            $profile['phone'] = $phone;
            $profile['student_id'] = $studentId;
            $profile['date_of_birth'] = $dobValue;
            $profile['profile_picture'] = $picToSave;
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="auth-box">
    <h3 class="mb-4">My Profile</h3>
    <?php if ($success): ?><div class="alert alert-success">Profile updated successfully.</div><?php endif; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= h($e) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

      <div class="text-center mb-4">
        <img id="avatarPreview" src="<?= h(avatar_url($profile['profile_picture'], $profile['full_name'])) ?>" alt="Your profile picture" class="avatar-preview mb-2">
        <label class="form-label d-block fw-semibold" for="profile_picture">Change Profile Picture</label>
        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png,.webp">
        <div class="form-text">Leave blank to keep your current picture.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Email (cannot be changed)</label>
        <input type="email" class="form-control" value="<?= h($profile['email']) ?>" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label" for="full_name">Full Name</label>
        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= h($profile['full_name']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="student_id">Student ID</label>
        <input type="text" class="form-control" id="student_id" name="student_id" value="<?= h($profile['student_id']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="phone">Phone</label>
        <input type="tel" class="form-control" id="phone" name="phone" value="<?= h($profile['phone']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="date_of_birth"><i class="bi bi-gift"></i> Date of Birth</label>
        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?= h($profile['date_of_birth']) ?>">
        <div class="form-text">See your <a href="<?= BASE_URL ?>/student/birthday.php">birthday countdown</a>.</div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="new_password">New Password (leave blank to keep current)</label>
        <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
      </div>
      <button type="submit" class="btn btn-primary w-100">Save Changes</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
