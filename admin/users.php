<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

require_admin();

$pageTitle = html_entity_decode('&#20351;&#29992;&#32773;&#31649;&#29702;', ENT_QUOTES, 'UTF-8');
$pageScript = '/assets/js/admin-users.js';
$initialStatus = $_GET['status'] ?? '';

include __DIR__ . '/../includes/layout/app-shell-start.php';
?>
<section class="stack admin-users-page" data-csrf-token="<?= e(csrf_token()) ?>" data-initial-status="<?= e((string) $initialStatus) ?>">
  <div class="card card-glow">
    <div class="card-header">
      <div>
        <p class="text-muted">Admin</p>
        <h2 class="card-title">&#20351;&#29992;&#32773;&#31649;&#29702;</h2>
      </div>
      <span class="badge badge-primary" id="admin-users-count">0</span>
    </div>
    <div class="card-body stack">
      <div class="form-group">
        <label class="form-label" for="user-search">&#25628;&#23563;</label>
        <input class="form-control" id="user-search" type="search" placeholder="username / name / email">
      </div>
      <div class="admin-filter-row">
        <button class="btn btn-sm btn-primary" type="button" data-status-filter="">&#20840;&#37096;</button>
        <button class="btn btn-sm btn-secondary" type="button" data-status-filter="active">&#21855;&#29992;</button>
        <button class="btn btn-sm btn-secondary" type="button" data-status-filter="disabled">&#20572;&#29992;</button>
      </div>
    </div>
  </div>

  <div class="stack" id="admin-users-list"></div>

  <div class="card">
    <div class="card-header">
      <div>
        <p class="text-muted" id="user-form-mode">&#26032;&#22686;</p>
        <h2 class="card-title">&#20351;&#29992;&#32773;&#36039;&#26009;</h2>
      </div>
      <button class="btn btn-sm btn-ghost hidden" type="button" id="cancel-edit-button">&#21462;&#28040;</button>
    </div>
    <div class="card-body">
      <form class="stack" id="admin-user-form">
        <input type="hidden" id="edit-user-id" name="id">
        <div class="form-group">
          <label class="form-label" for="admin-username">&#24115;&#34399;</label>
          <input class="form-control" id="admin-username" name="username" type="text" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="admin-real-name">&#30495;&#23526;&#22995;&#21517;</label>
          <input class="form-control" id="admin-real-name" name="real_name" type="text" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="admin-email">Email</label>
          <input class="form-control" id="admin-email" name="email" type="email" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="admin-role">&#35282;&#33394;</label>
          <select class="form-control" id="admin-role" name="role">
            <option value="user">&#19968;&#33324;&#20351;&#29992;&#32773;</option>
            <option value="admin">&#31649;&#29702;&#32773;</option>
          </select>
        </div>
        <div class="form-group" id="password-create-group">
          <label class="form-label" for="admin-password">&#21021;&#22987;&#23494;&#30908;</label>
          <input class="form-control" id="admin-password" name="password" type="password" minlength="4">
        </div>
        <button class="btn btn-primary btn-lg btn-block" type="submit" id="save-user-button">&#20786;&#23384;</button>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/layout/app-shell-end.php'; ?>
