<?php $user = current_user(); ?>
<header class="app-header">
  <div class="app-header-inner">
    <button class="icon-button" type="button" data-action="back" aria-label="<?= e(html_entity_decode('&#36820;&#22238;', ENT_QUOTES, 'UTF-8')) ?>">
      <span aria-hidden="true">&#8249;</span>
    </button>
    <h1 class="app-header-title"><?= e($pageTitle ?? APP_NAME) ?></h1>
    <a class="avatar-button" href="/profile.php" aria-label="<?= e(html_entity_decode('&#20491;&#20154;&#35373;&#23450;', ENT_QUOTES, 'UTF-8')) ?>">
      <?= e(first_character($user['real_name'] ?? 'Me')) ?>
    </a>
  </div>
</header>
