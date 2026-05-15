<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navItems = [
    ['/work-log.php', html_entity_decode('&#32000;&#37636;', ENT_QUOTES, 'UTF-8'), 'log'],
    ['/work-stats.php', html_entity_decode('&#32113;&#35336;', ENT_QUOTES, 'UTF-8'), 'stats'],
    ['/salary-bonus.php', html_entity_decode('&#34218;&#36039;', ENT_QUOTES, 'UTF-8'), 'salary'],
    ['/leaderboard.php', html_entity_decode('&#25490;&#34892;', ENT_QUOTES, 'UTF-8'), 'leaderboard'],
    ['/profile.php', html_entity_decode('&#35373;&#23450;', ENT_QUOTES, 'UTF-8'), 'settings'],
];

if (is_admin()) {
    array_splice($navItems, 4, 0, [['/admin/index.php', html_entity_decode('&#24460;&#21488;', ENT_QUOTES, 'UTF-8'), 'admin']]);
}

function nav_icon(string $name): string
{
    $icons = [
        'log' => '<path d="M8 4.5h8"/><path d="M8 9h8"/><path d="M8 13.5h5"/><rect x="5" y="2.5" width="14" height="19" rx="3"/>',
        'stats' => '<path d="M5 19V9"/><path d="M12 19V5"/><path d="M19 19v-7"/><path d="M3.5 19.5h17"/>',
        'leaderboard' => '<path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v4a5 5 0 0 1-10 0V4z"/><path d="M7 6H4a2 2 0 0 0 2 4h1"/><path d="M17 6h3a2 2 0 0 1-2 4h-1"/><path d="M10.5 8.5l1.5-1 1.5 1-.5-1.7 1.4-1h-1.7L12 4.2l-.7 1.6H9.6l1.4 1-.5 1.7z"/>',
        'salary' => '<path d="M12 2v20"/><path d="M17 6.5c-.9-1-2.3-1.5-4.1-1.5-2.3 0-3.9 1.1-3.9 2.9 0 4.2 8 2.1 8 6.5 0 1.8-1.7 3.1-4.3 3.1-2 0-3.7-.7-4.8-2"/>',
        'admin' => '<path d="M12 3l7 3v5c0 4.6-2.9 8.6-7 10-4.1-1.4-7-5.4-7-10V6l7-3z"/><path d="M9.5 12l1.7 1.7 3.5-3.7"/>',
        'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/><path d="M19.4 15a1.8 1.8 0 0 0 .3 2l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.8 1.8 0 0 0-2-.3 1.8 1.8 0 0 0-1.1 1.6V21a2 2 0 1 1-4 0v-.1a1.8 1.8 0 0 0-1.1-1.6 1.8 1.8 0 0 0-2 .3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.8 1.8 0 0 0 .3-2 1.8 1.8 0 0 0-1.6-1.1H2.5a2 2 0 1 1 0-4h.1a1.8 1.8 0 0 0 1.6-1.1 1.8 1.8 0 0 0-.3-2l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.8 1.8 0 0 0 2 .3 1.8 1.8 0 0 0 1.1-1.6V3a2 2 0 1 1 4 0v.1a1.8 1.8 0 0 0 1.1 1.6 1.8 1.8 0 0 0 2-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.8 1.8 0 0 0-.3 2 1.8 1.8 0 0 0 1.6 1.1h.1a2 2 0 1 1 0 4h-.1a1.8 1.8 0 0 0-1.6 1.1z"/>',
    ];

    return '<svg class="bottom-nav-svg" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">' . ($icons[$name] ?? $icons['log']) . '</svg>';
}
?>
<nav class="bottom-nav" aria-label="<?= e(html_entity_decode('&#20027;&#35201;&#23566;&#35261;', ENT_QUOTES, 'UTF-8')) ?>">
  <div class="bottom-nav-inner">
    <?php foreach ($navItems as [$href, $label, $icon]): ?>
      <a class="bottom-nav-item <?= $currentPath === $href ? 'is-active' : '' ?>" href="<?= e($href) ?>">
        <span class="bottom-nav-icon"><?= nav_icon($icon) ?></span>
        <span class="bottom-nav-label"><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
