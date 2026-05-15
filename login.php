<?php

require_once __DIR__ . '/includes/assets.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    header('Location: /work-log.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#070B1A">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title>登入｜<?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= css('/assets/css/01-tokens.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/02-reset.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/03-typography.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/04-layout.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/05-components.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/06-animations.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/07-utilities.css') ?>">
  <link rel="stylesheet" href="<?= css('/assets/css/08-pages.css') ?>">
</head>
<body>
  <main class="app-shell auth-page">
    <section class="auth-card card card-glow">
      <div class="stack">
        <div>
          <p class="text-muted">Workday Tracker</p>
          <h1 class="page-title">紀錄上班天數系統</h1>
        </div>

        <form class="stack" id="login-form" autocomplete="on">
          <div class="form-group">
            <label class="form-label" for="identifier">帳號或信箱</label>
            <input class="form-control" id="identifier" name="identifier" type="text" autocomplete="username" required>
            <p class="form-error" data-error-for="identifier"></p>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">密碼</label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
            <p class="form-error" data-error-for="password"></p>
          </div>

          <button class="btn btn-primary btn-lg btn-block" type="submit">登入</button>
        </form>
      </div>
    </section>
  </main>

  <script src="<?= js('/assets/js/api.js') ?>"></script>
  <script src="<?= js('/assets/js/ui.js') ?>"></script>
  <script src="<?= js('/assets/js/form.js') ?>"></script>
  <script src="<?= js('/assets/js/login.js') ?>"></script>
</body>
</html>
