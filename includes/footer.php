</main>

<?php
// Built-in defaults so the icons always show even if config/social_links.php
// hasn't been added yet. If that file DOES exist, it overrides these.
$socialLinks = [
    'facebook'  => ['url' => '#', 'icon' => 'bi-facebook',   'label' => 'Facebook'],
    'instagram' => ['url' => '#', 'icon' => 'bi-instagram',  'label' => 'Instagram'],
    'twitter'   => ['url' => '#', 'icon' => 'bi-twitter-x',  'label' => 'X (Twitter)'],
    'whatsapp'  => ['url' => '#', 'icon' => 'bi-whatsapp',   'label' => 'WhatsApp'],
    'linkedin'  => ['url' => '#', 'icon' => 'bi-linkedin',   'label' => 'LinkedIn'],
    'tiktok'    => ['url' => '#', 'icon' => 'bi-tiktok',     'label' => 'TikTok'],
];
$socialLinksPath = __DIR__ . '/../config/social_links.php';
if (file_exists($socialLinksPath)) {
    $custom = require $socialLinksPath;
    if (is_array($custom)) {
        $socialLinks = $custom;
    }
}
?>
<footer class="text-light mt-5 py-4" style="background:var(--navy-dark);">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <div class="text-center text-md-start">
        <p class="mb-1">&copy; <?= date('Y') ?> Hostel Agency. All rights reserved.</p>
        <p class="mb-0 small text-secondary">Helping admitted students find their perfect hostel — and everything else they need.</p>
      </div>
      <div class="d-flex gap-3 social-links">
        <?php foreach ($socialLinks as $key => $s): ?>
          <?php if (!empty($s['url'])): ?>
            <a href="<?= h($s['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= h($s['label']) ?>" title="<?= h($s['label']) ?>">
              <i class="bi <?= h($s['icon']) ?>"></i>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>