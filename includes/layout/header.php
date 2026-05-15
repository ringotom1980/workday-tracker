<?php $user = current_user(); ?>
<header class="app-header">
  <div class="app-header-inner">
    <button class="icon-button" type="button" data-action="back" aria-label="返回">
      <span aria-hidden="true">‹</span>
    </button>
    <h1 class="app-header-title"><?= e($pageTitle ?? APP_NAME) ?></h1>
    <a class="avatar-button" href="/profile.php" aria-label="個人設定">
      <?= e(mb_substr($user['real_name'] ?? '我', 0, 1, 'UTF-8')) ?>
    </a>
  </div>
</header>
