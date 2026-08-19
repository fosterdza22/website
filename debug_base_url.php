<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Diagnostics';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <h2>Connection Diagnostics</h2>
  <p class="text-muted">Use this page to confirm your links will resolve correctly. Delete this file once everything works.</p>
  <table class="table table-bordered bg-white">
    <tr><th>Detected BASE_URL</th><td><code><?= h(BASE_URL) ?: '(empty — app is at the web root)' ?></code></td></tr>
    <tr><th>Example login link this produces</th><td><code><?= h(u('/login.php')) ?></code></td></tr>
    <tr><th>SCRIPT_FILENAME</th><td><code><?= h($_SERVER['SCRIPT_FILENAME'] ?? '(not set)') ?></code></td></tr>
    <tr><th>SCRIPT_NAME</th><td><code><?= h($_SERVER['SCRIPT_NAME'] ?? '(not set)') ?></code></td></tr>
    <tr><th>DOCUMENT_ROOT</th><td><code><?= h($_SERVER['DOCUMENT_ROOT'] ?? '(not set)') ?></code></td></tr>
    <tr><th>Current full URL</th><td><code><?= h((!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')) ?></code></td></tr>
    <tr><th>cURL extension loaded (needed for Paystack)</th><td><code><?= extension_loaded('curl') ? 'Yes' : 'NO — enable php_curl in php.ini' ?></code></td></tr>
    <tr><th>fileinfo/GD extension loaded (needed for uploads)</th><td><code><?= extension_loaded('gd') ? 'Yes' : 'Not detected — image validation may be limited' ?></code></td></tr>
  </table>

  <p>Click this link — if it loads the login page (not a 404), routing is fixed:</p>
  <a class="btn btn-primary" href="<?= u('/login.php') ?>">Test: Go to Login</a>

  <hr>
  <p class="small text-muted">If <strong>Detected BASE_URL</strong> above does not match the folder name you see in your
  browser's address bar, open <code>config/app.php</code> and uncomment the manual override line near the top, setting
  it to match exactly, e.g. <code>define('BASE_URL', '/hostel-agency');</code></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
