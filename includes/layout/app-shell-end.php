    </main>

    <?php include __DIR__ . '/bottom-nav.php'; ?>
  </div>

  <script src="<?= js('/assets/js/api.js') ?>"></script>
  <script src="<?= js('/assets/js/ui.js') ?>"></script>
  <script src="<?= js('/assets/js/app.js') ?>"></script>
  <script src="<?= js('/assets/js/form.js') ?>"></script>
  <script src="<?= js('/assets/js/date.js') ?>"></script>
  <script src="<?= js('/assets/js/storage.js') ?>"></script>

  <?php if (!empty($pageScript)): ?>
    <script src="<?= js($pageScript) ?>"></script>
  <?php endif; ?>
</body>
</html>
