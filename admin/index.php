<?php

require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pageTitle = html_entity_decode('&#24460;&#21488;&#31649;&#29702;', ENT_QUOTES, 'UTF-8');
$pageScript = '/assets/js/admin-dashboard.js';

include __DIR__ . '/../includes/layout/app-shell-start.php';
?>
<section class="stack admin-dashboard-page">
  <div class="card card-glow">
    <div class="card-header">
      <div>
        <p class="text-muted">Admin</p>
        <h2 class="card-title">&#24460;&#21488;&#31649;&#29702;</h2>
      </div>
      <span class="badge badge-primary" id="dashboard-status">&#36617;&#20837;&#20013;</span>
    </div>
  </div>

  <div class="admin-metric-grid">
    <div class="card admin-metric-card">
      <p class="text-muted">&#20351;&#29992;&#32773;&#32317;&#25976;</p>
      <strong class="number" id="total-users">0</strong>
    </div>
    <div class="card admin-metric-card">
      <p class="text-muted">&#21855;&#29992;&#20351;&#29992;&#32773;</p>
      <strong class="number" id="active-users">0</strong>
    </div>
    <div class="card admin-metric-card">
      <p class="text-muted">&#20572;&#29992;&#20351;&#29992;&#32773;</p>
      <strong class="number" id="disabled-users">0</strong>
    </div>
    <div class="card admin-metric-card">
      <p class="text-muted">&#20170;&#26085;&#32000;&#37636;</p>
      <strong class="number" id="today-logs">0</strong>
    </div>
    <div class="card admin-metric-card">
      <p class="text-muted">&#26412;&#26376;&#32000;&#37636;</p>
      <strong class="number" id="month-logs">0</strong>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted">&#25919;&#24220;&#34892;&#20107;&#26310;</p>
        <h2 class="card-title">&#26368;&#36817;&#21516;&#27493;&#29376;&#24907;</h2>
      </div>
      <span class="badge badge-muted" id="calendar-job-status">-</span>
    </div>
    <div class="card-body stack">
      <div class="admin-info-row">
        <span class="text-muted">&#26368;&#36817;&#21516;&#27493;&#26178;&#38291;</span>
        <strong id="calendar-job-time">-</strong>
      </div>
      <div class="admin-info-row">
        <span class="text-muted">&#21295;&#20837;&#31558;&#25976;</span>
        <strong class="number" id="calendar-job-records">0</strong>
      </div>
      <div class="admin-info-row">
        <span class="text-muted">&#35338;&#24687;</span>
        <strong id="calendar-job-message">-</strong>
      </div>
    </div>
  </div>

  <div class="admin-link-grid">
    <a class="btn btn-secondary btn-lg" href="/admin/users.php">&#20351;&#29992;&#32773;&#31649;&#29702;</a>
    <a class="btn btn-secondary btn-lg" href="/admin/work-logs.php">&#32000;&#37636;&#31649;&#29702;</a>
    <a class="btn btn-secondary btn-lg" href="/admin/calendar.php">&#34892;&#20107;&#26310;&#29376;&#24907;</a>
    <a class="btn btn-secondary btn-lg" href="/admin/logs.php">&#31995;&#32113;&#32000;&#37636;</a>
  </div>
</section>
<?php include __DIR__ . '/../includes/layout/app-shell-end.php'; ?>
