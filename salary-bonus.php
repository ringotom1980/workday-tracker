<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/helpers.php';

require_login();

$user = current_user();
$pageTitle = html_entity_decode('&#34218;&#36039;&#21450;&#32005;&#21033;&#27010;&#20272;', ENT_QUOTES, 'UTF-8');
$pageScript = '/assets/js/salary-bonus.js';

include __DIR__ . '/includes/layout/app-shell-start.php';
?>
<section class="stack salary-page" data-csrf-token="<?= e(csrf_token()) ?>">
  <div class="card card-glow">
    <div class="card-header">
      <div>
        <p class="text-muted"><?= e($user['real_name'] ?? '') ?></p>
        <h2 class="card-title">&#34218;&#36039;&#21450;&#32005;&#21033;&#27010;&#20272;</h2>
      </div>
      <span class="badge badge-primary" id="salary-month-badge"></span>
    </div>
    <div class="card-body">
      <div class="stats-toolbar">
        <button class="icon-button" type="button" data-salary-prev aria-label="&#19978;&#20491;&#26376;">&#8249;</button>
        <div class="text-center">
          <p class="text-muted">&#35336;&#31639;&#26376;&#20221;</p>
          <h2 class="card-title" id="salary-month-title"></h2>
        </div>
        <button class="icon-button" type="button" data-salary-next aria-label="&#19979;&#20491;&#26376;">&#8250;</button>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted">&#35373;&#23450;</p>
        <h2 class="card-title">&#37329;&#38989;&#22522;&#25976;</h2>
      </div>
      <span class="badge badge-muted" id="save-status">&#24050;&#36617;&#20837;</span>
    </div>
    <div class="card-body stack">
      <div class="form-group">
        <label class="form-label" for="daily-salary">&#26085;&#34218;</label>
        <input class="form-control" id="daily-salary" type="number" min="0" step="1" inputmode="decimal" placeholder="0">
      </div>
      <div class="form-group">
        <label class="form-label" for="first-half-bonus-base">&#19978;&#21322;&#24180;&#32005;&#21033;&#22522;&#25976;</label>
        <input class="form-control" id="first-half-bonus-base" type="number" min="0" step="1" inputmode="decimal" placeholder="0">
      </div>
      <div class="form-group">
        <label class="form-label" for="second-half-bonus-base">&#19979;&#21322;&#24180;&#32005;&#21033;&#22522;&#25976;</label>
        <input class="form-control" id="second-half-bonus-base" type="number" min="0" step="1" inputmode="decimal" placeholder="0">
      </div>
      <p class="text-muted">&#20572;&#27490;&#36664;&#20837; 1 &#31186;&#24460;&#33258;&#21205;&#20786;&#23384;&#12290;</p>
    </div>
  </div>

  <div class="salary-result-grid">
    <div class="card salary-result-card">
      <p class="text-muted">&#30070;&#26376;&#19978;&#29677;&#22825;&#25976;</p>
      <strong><span class="number" id="monthly-work-days">0</span> &#22825;</strong>
    </div>
    <div class="card salary-result-card">
      <p class="text-muted">&#30070;&#26376;&#34218;&#36039;&#27010;&#20272;</p>
      <strong>$<span class="number" id="monthly-salary">0</span></strong>
    </div>
    <div class="card salary-result-card">
      <p class="text-muted">&#19978;&#21322;&#24180;&#24037;&#25976;</p>
      <strong><span class="number" id="first-half-work-days">0</span> &#22825;</strong>
    </div>
    <div class="card salary-result-card">
      <p class="text-muted">&#19978;&#21322;&#24180;&#32005;&#21033;</p>
      <strong>$<span class="number" id="first-half-bonus">0</span></strong>
    </div>
    <div class="card salary-result-card">
      <p class="text-muted">&#19979;&#21322;&#24180;&#24037;&#25976;</p>
      <strong><span class="number" id="second-half-work-days">0</span> &#22825;</strong>
    </div>
    <div class="card salary-result-card">
      <p class="text-muted">&#19979;&#21322;&#24180;&#32005;&#21033;</p>
      <strong>$<span class="number" id="second-half-bonus">0</span></strong>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/layout/app-shell-end.php'; ?>
