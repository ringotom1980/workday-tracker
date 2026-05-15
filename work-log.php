<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

require_login();

$pageTitle = 'Work Log';
$pageScript = '/assets/js/work-log.js';

include __DIR__ . '/includes/layout/app-shell-start.php';
?>
<section class="stack work-log-page" data-csrf-token="<?= e(csrf_token()) ?>">
  <div class="card card-glow">
    <div class="card-header">
      <div>
        <p class="text-muted">Today</p>
        <h2 class="card-title" id="current-status">Not recorded yet</h2>
      </div>
      <span class="badge badge-primary" id="today-date"></span>
    </div>
    <div class="card-body stack">
      <div class="work-type-grid" role="group" aria-label="Work type">
        <button class="btn btn-secondary btn-lg work-type-button" type="button" data-work-type="full_day">Full</button>
        <button class="btn btn-secondary btn-lg work-type-button" type="button" data-work-type="half_day">Half</button>
        <button class="btn btn-secondary btn-lg work-type-button" type="button" data-work-type="night">Night</button>
      </div>
      <p class="text-muted" id="status-hint">Tap a shift type to record today. Tap the same type again to cancel.</p>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted">This Month</p>
        <h2 class="card-title"><span class="number" id="month-total">0</span> days</h2>
      </div>
      <span class="badge badge-muted" id="month-label"></span>
    </div>
    <div class="card-body">
      <div class="summary-grid">
        <div class="summary-item">
          <span class="text-muted">Records</span>
          <strong class="number" id="month-records">0</strong>
        </div>
        <div class="summary-item">
          <span class="text-muted">Full</span>
          <strong class="number" id="month-full">0</strong>
        </div>
        <div class="summary-item">
          <span class="text-muted">Half</span>
          <strong class="number" id="month-half">0</strong>
        </div>
        <div class="summary-item">
          <span class="text-muted">Night</span>
          <strong class="number" id="month-night">0</strong>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/layout/app-shell-end.php'; ?>
