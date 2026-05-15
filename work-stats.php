<?php

require_once __DIR__ . '/includes/auth.php';

require_login();

$pageTitle = html_entity_decode('&#19978;&#29677;&#22825;&#25976;&#32113;&#35336;', ENT_QUOTES, 'UTF-8');
$pageScript = '/assets/js/work-stats.js';

include __DIR__ . '/includes/layout/app-shell-start.php';
?>
<section class="stack work-stats-page">
  <div class="card card-glow">
    <div class="stats-toolbar">
      <button class="icon-button" type="button" data-month-prev aria-label="&#19978;&#20491;&#26376;">&#8249;</button>
      <div class="text-center">
        <p class="text-muted">&#32113;&#35336;&#26376;&#20221;</p>
        <h2 class="card-title" id="stats-month-title"></h2>
      </div>
      <button class="icon-button" type="button" data-month-next aria-label="&#19979;&#20491;&#26376;">&#8250;</button>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted">&#30070;&#26376;&#25563;&#31639;</p>
        <h2 class="card-title"><span class="number" id="stats-total">0</span> &#22825;</h2>
      </div>
      <span class="badge badge-muted" id="stats-record-count">0 &#31558;</span>
    </div>
    <div class="card-body">
      <div class="summary-grid">
        <div class="summary-item">
          <span class="text-muted">&#25972;&#26085;</span>
          <strong class="number" id="stats-full">0</strong>
        </div>
        <div class="summary-item">
          <span class="text-muted">&#21322;&#26085;</span>
          <strong class="number" id="stats-half">0</strong>
        </div>
        <div class="summary-item">
          <span class="text-muted">&#22812;&#29677;</span>
          <strong class="number" id="stats-night">0</strong>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted">&#26376;&#26310;</p>
        <h2 class="card-title">&#32000;&#37636;&#33287;&#34892;&#20107;&#26310;</h2>
      </div>
    </div>
    <div class="card-body">
      <div class="stats-weekdays" aria-hidden="true">
        <span>&#26085;</span>
        <span>&#19968;</span>
        <span>&#20108;</span>
        <span>&#19977;</span>
        <span>&#22235;</span>
        <span>&#20116;</span>
        <span>&#20845;</span>
      </div>
      <div class="stats-calendar-grid" id="stats-calendar-grid"></div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/layout/app-shell-end.php'; ?>
