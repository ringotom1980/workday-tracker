<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();

$pageTitle = html_entity_decode('&#19978;&#29677;&#32000;&#37636;', ENT_QUOTES, 'UTF-8');
$pageScript = '/assets/js/work-log.js';

include __DIR__ . '/includes/layout/app-shell-start.php';
?>
<section class="stack work-log-page" data-csrf-token="<?= e(csrf_token()) ?>">
  <div class="card card-glow">
    <div class="card-header">
      <div>
        <p class="text-muted" id="selected-date-label"></p>
        <h2 class="card-title" id="current-status">&#23578;&#26410;&#32000;&#37636;</h2>
      </div>
      <button class="badge badge-primary date-picker-trigger" id="selected-date-button" type="button" aria-expanded="false" aria-controls="date-picker-panel"></button>
    </div>
    <div class="card-body stack">
      <div class="date-picker-panel hidden" id="date-picker-panel">
        <div class="date-picker-header">
          <button class="icon-button" type="button" data-calendar-prev aria-label="&#19978;&#20491;&#26376;">&#8249;</button>
          <strong class="date-picker-title" id="calendar-title"></strong>
          <button class="icon-button" type="button" data-calendar-next aria-label="&#19979;&#20491;&#26376;">&#8250;</button>
        </div>
        <div class="date-picker-weekdays" aria-hidden="true">
          <span>&#26085;</span>
          <span>&#19968;</span>
          <span>&#20108;</span>
          <span>&#19977;</span>
          <span>&#22235;</span>
          <span>&#20116;</span>
          <span>&#20845;</span>
        </div>
        <div class="date-picker-grid" id="date-picker-grid"></div>
      </div>
      <div class="work-type-grid" role="group" aria-label="&#29677;&#21029;">
        <button class="btn btn-secondary btn-lg work-type-button" type="button" data-work-type="full_day">&#25972;&#26085;&#29677;</button>
        <button class="btn btn-secondary btn-lg work-type-button" type="button" data-work-type="half_day">&#21322;&#26085;&#29677;</button>
        <button class="btn btn-secondary btn-lg work-type-button" type="button" data-work-type="night">&#22812;&#29677;</button>
      </div>
      <p class="text-muted" id="status-hint">&#40670;&#36984;&#29677;&#21029;&#21363;&#21487;&#32000;&#37636;&#36984;&#23450;&#26085;&#26399;&#65292;&#20877;&#40670;&#19968;&#27425;&#30456;&#21516;&#29677;&#21029;&#21487;&#21462;&#28040;&#12290;</p>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted">&#26412;&#26376;&#32047;&#35336;</p>
        <h2 class="card-title"><span class="number" id="month-total">0</span> &#22825;</h2>
      </div>
      <span class="badge badge-muted" id="month-label"></span>
    </div>
    <div class="card-body">
      <div class="summary-grid">
        <div class="summary-item">
          <span class="text-muted">&#32000;&#37636;&#31558;&#25976;</span>
          <strong class="number" id="month-records">0</strong>
        </div>
        <div class="summary-item">
          <span class="text-muted">&#25972;&#26085;</span>
          <strong class="number" id="month-full">0</strong>
        </div>
        <div class="summary-item">
          <span class="text-muted">&#21322;&#26085;</span>
          <strong class="number" id="month-half">0</strong>
        </div>
        <div class="summary-item">
          <span class="text-muted">&#22812;&#29677;</span>
          <strong class="number" id="month-night">0</strong>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/layout/app-shell-end.php'; ?>
