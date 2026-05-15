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
    <a class="card admin-metric-card" href="/admin/users.php">
      <p class="text-muted">&#20351;&#29992;&#32773;&#32317;&#25976;</p>
      <strong class="number" id="total-users">0</strong>
    </a>
    <a class="card admin-metric-card" href="/admin/users.php?status=disabled">
      <p class="text-muted">&#20572;&#29992;&#20351;&#29992;&#32773;</p>
      <strong class="number" id="disabled-users">0</strong>
    </a>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted">&#25919;&#24220;&#34892;&#20107;&#26310;</p>
        <h2 class="card-title">&#26368;&#36817;&#26356;&#26032;&#25104;&#21151;</h2>
      </div>
    </div>
    <div class="card-body">
      <div class="admin-info-row">
        <span class="text-muted">&#26356;&#26032;&#26178;&#38291;</span>
        <strong id="calendar-job-time">-</strong>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/layout/app-shell-end.php'; ?>
