<?php

require_once __DIR__ . '/includes/auth.php';

require_login();

$pageTitle = html_entity_decode('&#24037;&#25976;&#25490;&#34892;', ENT_QUOTES, 'UTF-8');
$pageScript = '/assets/js/leaderboard.js';

include __DIR__ . '/includes/layout/app-shell-start.php';
?>
<section class="stack leaderboard-page">
  <div class="card card-glow">
    <div class="card-header">
      <div>
        <p class="text-muted">&#20840;&#21729;&#24037;&#25976;</p>
        <h2 class="card-title">&#24037;&#25976;&#25490;&#34892;</h2>
      </div>
      <span class="badge badge-primary" id="leaderboard-period-label"></span>
    </div>
    <div class="card-body stack">
      <div class="stats-toolbar">
        <button class="icon-button" type="button" data-leaderboard-prev aria-label="&#19978;&#20491;&#26376;">&#8249;</button>
        <div class="text-center">
          <p class="text-muted">&#22522;&#28310;&#26376;&#20221;</p>
          <h2 class="card-title" id="leaderboard-month-title"></h2>
        </div>
        <button class="icon-button" type="button" data-leaderboard-next aria-label="&#19979;&#20491;&#26376;">&#8250;</button>
      </div>
      <div class="leaderboard-tabs" role="group" aria-label="&#25490;&#34892;&#21312;&#38291;">
        <button class="btn btn-sm btn-primary" type="button" data-period="month">&#26412;&#26376;</button>
        <button class="btn btn-sm btn-secondary" type="button" data-period="first_half">&#19978;&#21322;&#24180;</button>
        <button class="btn btn-sm btn-secondary" type="button" data-period="second_half">&#19979;&#21322;&#24180;</button>
        <button class="btn btn-sm btn-secondary" type="button" data-period="year">&#20840;&#24180;</button>
      </div>
    </div>
  </div>

  <div class="card leaderboard-top-card">
    <p class="text-muted">&#30446;&#21069;&#31532;&#19968;</p>
    <h2 class="card-title" id="leaderboard-winner">-</h2>
    <p class="text-muted" id="leaderboard-summary">-</p>
  </div>

  <div class="stack" id="leaderboard-list"></div>
</section>
<?php include __DIR__ . '/includes/layout/app-shell-end.php'; ?>
