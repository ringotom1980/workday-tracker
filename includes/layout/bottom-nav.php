<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navItems = [
    ['/work-log.php', html_entity_decode('&#32000;&#37636;', ENT_QUOTES, 'UTF-8'), '*'],
    ['/work-stats.php', html_entity_decode('&#32113;&#35336;', ENT_QUOTES, 'UTF-8'), '#'],
    ['/salary-bonus.php', html_entity_decode('&#34218;&#36039;', ENT_QUOTES, 'UTF-8'), '$'],
    ['/profile.php', html_entity_decode('&#35373;&#23450;', ENT_QUOTES, 'UTF-8'), '@'],
];

if (is_admin()) {
    array_splice($navItems, 3, 0, [['/admin/index.php', html_entity_decode('&#24460;&#21488;', ENT_QUOTES, 'UTF-8'), '+']]);
}
?>
<nav class="bottom-nav" aria-label="<?= e(html_entity_decode('&#20027;&#35201;&#23566;&#35261;', ENT_QUOTES, 'UTF-8')) ?>">
  <div class="bottom-nav-inner">
    <?php foreach ($navItems as [$href, $label, $icon]): ?>
      <a class="bottom-nav-item <?= $currentPath === $href ? 'is-active' : '' ?>" href="<?= e($href) ?>">
        <span class="bottom-nav-icon" aria-hidden="true"><?= e($icon) ?></span>
        <span class="bottom-nav-label"><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
