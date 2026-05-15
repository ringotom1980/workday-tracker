<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navItems = [
    ['/work-log.php', '紀錄', '●'],
    ['/work-stats.php', '統計', '▦'],
    ['/salary-bonus.php', '薪資', '$'],
    ['/profile.php', '設定', '◎'],
];

if (is_admin()) {
    array_splice($navItems, 3, 0, [['/admin/index.php', '後台', '◆']]);
}
?>
<nav class="bottom-nav" aria-label="主要導覽">
  <div class="bottom-nav-inner">
    <?php foreach ($navItems as [$href, $label, $icon]): ?>
      <a class="bottom-nav-item <?= $currentPath === $href ? 'is-active' : '' ?>" href="<?= e($href) ?>">
        <span class="bottom-nav-icon" aria-hidden="true"><?= e($icon) ?></span>
        <span class="bottom-nav-label"><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
