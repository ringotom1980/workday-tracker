<?php

require_once __DIR__ . '/includes/auth.php';

require_login();

$pageTitle = '上班紀錄';
$pageScript = '/assets/js/work-log.js';

include __DIR__ . '/includes/layout/app-shell-start.php';
?>
<section class="stack">
  <div class="card card-glow">
    <div class="card-header">
      <div>
        <p class="text-muted">今日狀態</p>
        <h2 class="card-title">準備開始紀錄</h2>
      </div>
      <span class="badge badge-primary" id="today-date"></span>
    </div>
    <div class="card-body stack">
      <div class="work-type-grid">
        <button class="btn btn-secondary btn-lg" type="button">整日班</button>
        <button class="btn btn-secondary btn-lg" type="button">半日班</button>
        <button class="btn btn-secondary btn-lg" type="button">夜班</button>
      </div>
      <p class="text-muted">登入流程已就緒，下一階段會接上每日班別新增、更新與取消。</p>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/layout/app-shell-end.php'; ?>
