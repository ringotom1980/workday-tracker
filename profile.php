<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/helpers.php';

require_login();

$user = current_user();
$pageTitle = html_entity_decode('&#20491;&#20154;&#35373;&#23450;', ENT_QUOTES, 'UTF-8');
$pageScript = '/assets/js/profile.js';

include __DIR__ . '/includes/layout/app-shell-start.php';
?>
<section class="stack profile-page" data-csrf-token="<?= e(csrf_token()) ?>">
  <div class="card card-glow profile-hero">
    <div class="profile-avatar"><?= e(first_character($user['real_name'] ?? 'Me')) ?></div>
    <div>
      <p class="text-muted"><?= e($user['email'] ?? '') ?></p>
      <h2 class="card-title"><?= e($user['real_name'] ?? '') ?></h2>
      <p class="text-muted">@<?= e($user['username'] ?? '') ?></p>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted">&#22522;&#26412;&#36039;&#26009;</p>
        <h2 class="card-title">&#20462;&#25913;&#22995;&#21517;&#33287;&#24115;&#34399;</h2>
      </div>
      <span class="badge badge-muted" id="profile-status">&#24050;&#36617;&#20837;</span>
    </div>
    <div class="card-body">
      <form class="stack" id="profile-form">
        <div class="form-group">
          <label class="form-label" for="real-name">&#30495;&#23526;&#22995;&#21517;</label>
          <input class="form-control" id="real-name" name="real_name" type="text" value="<?= e($user['real_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="username">&#24115;&#34399;</label>
          <input class="form-control" id="username" name="username" type="text" value="<?= e($user['username'] ?? '') ?>" required>
        </div>
        <button class="btn btn-primary btn-lg btn-block" type="submit">&#20786;&#23384;&#22522;&#26412;&#36039;&#26009;</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted">&#23494;&#30908;</p>
        <h2 class="card-title">&#20462;&#25913;&#23494;&#30908;</h2>
      </div>
    </div>
    <div class="card-body">
      <form class="stack" id="password-form">
        <div class="form-group">
          <label class="form-label" for="current-password">&#30446;&#21069;&#23494;&#30908;</label>
          <input class="form-control" id="current-password" name="current_password" type="password" autocomplete="current-password" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="new-password">&#26032;&#23494;&#30908;</label>
          <input class="form-control" id="new-password" name="new_password" type="password" autocomplete="new-password" minlength="4" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="confirm-password">&#30906;&#35469;&#26032;&#23494;&#30908;</label>
          <input class="form-control" id="confirm-password" name="confirm_password" type="password" autocomplete="new-password" minlength="4" required>
        </div>
        <button class="btn btn-secondary btn-lg btn-block" type="submit">&#26356;&#26032;&#23494;&#30908;</button>
      </form>
    </div>
  </div>

  <button class="btn btn-danger btn-lg btn-block" type="button" id="logout-button">&#30331;&#20986;</button>
</section>
<?php include __DIR__ . '/includes/layout/app-shell-end.php'; ?>
