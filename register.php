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
  <title><?= e(html_entity_decode('&#35387;&#20874;', ENT_QUOTES, 'UTF-8')) ?>｜<?= e(APP_NAME) ?></title>
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
          <h1 class="page-title">&#35387;&#20874;&#26032;&#24115;&#34399;</h1>
        </div>

        <form class="stack" id="register-form" autocomplete="on">
          <div class="form-group">
            <label class="form-label" for="username">&#24115;&#34399;</label>
            <input class="form-control" id="username" name="username" type="text" autocomplete="username" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="real-name">&#30495;&#23526;&#22995;&#21517;</label>
            <input class="form-control" id="real-name" name="real_name" type="text" autocomplete="name" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="email">&#38651;&#23376;&#20449;&#31665;</label>
            <input class="form-control" id="email" name="email" type="email" autocomplete="email" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="password">&#23494;&#30908;</label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" minlength="4" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="confirm-password">&#30906;&#35469;&#23494;&#30908;</label>
            <input class="form-control" id="confirm-password" name="confirm_password" type="password" autocomplete="new-password" minlength="4" required>
          </div>

          <button class="btn btn-primary btn-lg btn-block" type="submit">&#23436;&#25104;&#35387;&#20874;</button>
        </form>

        <div class="auth-link-row">
          <span class="text-muted">&#24050;&#32147;&#26377;&#24115;&#34399;&#65311;</span>
          <a class="btn btn-outline btn-sm" href="/login.php">&#22238;&#21040;&#30331;&#20837;</a>
        </div>
      </div>
    </section>
  </main>

  <script src="<?= js('/assets/js/api.js') ?>"></script>
  <script src="<?= js('/assets/js/ui.js') ?>"></script>
  <script src="<?= js('/assets/js/form.js') ?>"></script>
  <script src="<?= js('/assets/js/register.js') ?>"></script>
</body>
</html>
