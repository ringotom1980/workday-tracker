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
  <title><?= e(html_entity_decode('&#30331;&#20837;', ENT_QUOTES, 'UTF-8')) ?>｜<?= e(APP_NAME) ?></title>
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
          <h1 class="page-title"><?= e(APP_NAME) ?></h1>
        </div>

        <form class="stack" id="login-form" autocomplete="on">
          <div class="form-group">
            <label class="form-label" for="identifier">&#24115;&#34399;&#25110;&#20449;&#31665;</label>
            <input class="form-control" id="identifier" name="identifier" type="text" autocomplete="username" required>
            <p class="form-error" data-error-for="identifier"></p>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">&#23494;&#30908;</label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
            <p class="form-error" data-error-for="password"></p>
          </div>

          <div class="auth-options">
            <label class="check-option" for="remember-account">
              <input id="remember-account" name="remember_account" type="checkbox">
              <span>&#35352;&#20303;&#24115;&#34399;</span>
            </label>
            <label class="check-option" for="remember-password">
              <input id="remember-password" name="remember_password" type="checkbox">
              <span>&#35352;&#20303;&#23494;&#30908;</span>
            </label>
          </div>

          <button class="btn btn-primary btn-lg btn-block" type="submit">&#30331;&#20837;</button>
        </form>

        <div class="auth-link-row">
          <span class="text-muted">&#36996;&#27794;&#26377;&#24115;&#34399;&#65311;</span>
          <a class="btn btn-outline btn-sm" href="/register.php">&#35387;&#20874;&#26032;&#24115;&#34399;</a>
        </div>
      </div>
    </section>
  </main>

  <script src="<?= js('/assets/js/api.js') ?>"></script>
  <script src="<?= js('/assets/js/ui.js') ?>"></script>
  <script src="<?= js('/assets/js/form.js') ?>"></script>
  <script src="<?= js('/assets/js/login.js') ?>"></script>
</body>
</html>
