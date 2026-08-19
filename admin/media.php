<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$hostelId = (int)($_GET['hostel_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM hostels WHERE id = ?");
$stmt->execute([$hostelId]);
$hostel = $stmt->fetch();
if (!$hostel) {
    set_flash('error', 'Hostel not found.');
    redirect('/admin/hostels.php');
}

$pageTitle = 'Media — ' . $hostel['name'];

$allowedImageExt = ['jpg','jpeg','png','webp','gif'];
$allowedVideoExt = ['mp4','webm','mov'];
$maxImageBytes = 8 * 1024 * 1024;
$maxVideoBytes = 60 * 1024 * 1024;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_media'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid form submission, please try again.';
    } elseif (empty($_FILES['media_file']['name'])) {
        $errors[] = 'Please choose a file to upload.';
    } else {
        $file = $_FILES['media_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload failed (error code ' . (int)$file['error'] . ').';
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $isImage = in_array($ext, $allowedImageExt, true);
            $isVideo = in_array($ext, $allowedVideoExt, true);

            if (!$isImage && !$isVideo) {
                $errors[] = 'Unsupported file type. Allowed: ' . implode(', ', array_merge($allowedImageExt, $allowedVideoExt));
            } elseif ($isImage && $file['size'] > $maxImageBytes) {
                $errors[] = 'Image is too large (max 8 MB).';
            } elseif ($isVideo && $file['size'] > $maxVideoBytes) {
                $errors[] = 'Video is too large (max 60 MB).';
            } else {
                $destDir = __DIR__ . '/../assets/uploads/hostels/' . $hostelId;
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
                $destPath = $destDir . '/' . $safeName;
                $publicPath = '/assets/uploads/hostels/' . $hostelId . '/' . $safeName;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    if ($isImage) {
                        $pdo->prepare("INSERT INTO hostel_photos (hostel_id, photo_path) VALUES (?, ?)")
                            ->execute([$hostelId, $publicPath]);
                    } else {
                        $pdo->prepare("INSERT INTO hostel_videos (hostel_id, video_path) VALUES (?, ?)")
                            ->execute([$hostelId, $publicPath]);
                    }
                    set_flash('success', ucfirst($isImage ? 'Photo' : 'Video') . ' uploaded successfully.');
                    redirect('/admin/media.php?hostel_id=' . $hostelId);
                } else {
                    $errors[] = 'Could not save the uploaded file. Check folder permissions.';
                }
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_media']) && verify_csrf($_POST['csrf'] ?? '')) {
    $type = $_POST['media_type'] ?? '';
    $mediaId = (int)($_POST['media_id'] ?? 0);

    if ($type === 'photo') {
        $s = $pdo->prepare("SELECT * FROM hostel_photos WHERE id = ? AND hostel_id = ?");
        $s->execute([$mediaId, $hostelId]);
        if ($row = $s->fetch()) {
            @unlink(__DIR__ . '/../' . ltrim($row['photo_path'], '/'));
            $pdo->prepare("DELETE FROM hostel_photos WHERE id = ?")->execute([$mediaId]);
        }
    } elseif ($type === 'video') {
        $s = $pdo->prepare("SELECT * FROM hostel_videos WHERE id = ? AND hostel_id = ?");
        $s->execute([$mediaId, $hostelId]);
        if ($row = $s->fetch()) {
            @unlink(__DIR__ . '/../' . ltrim($row['video_path'], '/'));
            $pdo->prepare("DELETE FROM hostel_videos WHERE id = ?")->execute([$mediaId]);
        }
    }
    set_flash('success', 'Media deleted.');
    redirect('/admin/media.php?hostel_id=' . $hostelId);
}

$photos = get_hostel_photos($pdo, $hostelId);
$videos = get_hostel_videos($pdo, $hostelId);

require __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h2>Media — <?= h($hostel['name']) ?></h2>
  <a href="<?= BASE_URL ?>/admin/hostels.php" class="btn btn-outline-secondary btn-sm mb-3">&larr; Back to Hostels</a>

  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= h($e) ?></div>
  <?php endforeach; ?>

  <div class="card p-4 mb-4">
    <h5>Upload New Photo or Video</h5>
    <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="upload_media" value="1">
      <div class="col-md-8">
        <label class="form-label">Choose File</label>
        <input type="file" class="form-control" name="media_file" accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.mov" required>
        <div class="form-text">Images up to 8 MB (jpg, png, webp, gif). Videos up to 60 MB (mp4, webm, mov).</div>
      </div>
      <div class="col-md-4">
        <button class="btn btn-primary w-100"><i class="bi bi-cloud-upload"></i> Upload</button>
      </div>
    </form>
  </div>

  <h5>Photo Gallery (<?= count($photos) ?>)</h5>
  <div class="row g-3 mb-4">
    <?php if (!$photos): ?>
      <p class="text-muted">No photos uploaded yet.</p>
    <?php endif; ?>
    <?php foreach ($photos as $p): ?>
      <div class="col-md-3 col-6">
        <div class="card p-2">
          <img src="<?= h(media_url($p['photo_path'])) ?>" class="gallery-thumb mb-2" alt="Hostel photo">
          <form method="post" onsubmit="return confirm('Delete this photo?');">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="delete_media" value="1">
            <input type="hidden" name="media_type" value="photo">
            <input type="hidden" name="media_id" value="<?= (int)$p['id'] ?>">
            <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash"></i> Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <h5>Video Gallery (<?= count($videos) ?>)</h5>
  <div class="row g-3">
    <?php if (!$videos): ?>
      <p class="text-muted">No videos uploaded yet.</p>
    <?php endif; ?>
    <?php foreach ($videos as $v): ?>
      <div class="col-md-4">
        <div class="card p-2">
          <video controls class="w-100 rounded mb-2" style="max-height:180px;background:#000;">
            <source src="<?= h(media_url($v['video_path'])) ?>">
          </video>
          <form method="post" onsubmit="return confirm('Delete this video?');">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="delete_media" value="1">
            <input type="hidden" name="media_type" value="video">
            <input type="hidden" name="media_id" value="<?= (int)$v['id'] ?>">
            <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash"></i> Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
