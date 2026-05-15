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
        <p class="text-muted">&#20170;&#26085;&#29376;&#24907;</p>
        <h2 class="card-title" id="current-status">&#23578;&#26410;&#32000;&#37636;</h2>
      </div>
      <span class="badge badge-primary" id="today-date"></span>
    </div>
    <div class="card-body stack">
      <div class="work-type-grid" role="group" aria-label="&#29677;&#21029;">
        <button class="btn btn-secondary btn-lg work-type-button" type="button" data-work-type="full_day">&#25972;&#26085;&#29677;</button>
        <button class="btn btn-secondary btn-lg work-type-button" type="button" data-work-type="half_day">&#21322;&#26085;&#29677;</button>
        <button class="btn btn-secondary btn-lg work-type-button" type="button" data-work-type="night">&#22812;&#29677;</button>
      </div>
      <p class="text-muted" id="status-hint">&#40670;&#36984;&#29677;&#21029;&#21363;&#21487;&#32000;&#37636;&#20170;&#22825;&#65292;&#20877;&#40670;&#19968;&#27425;&#30456;&#21516;&#29677;&#21029;&#21487;&#21462;&#28040;&#12290;</p>
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
