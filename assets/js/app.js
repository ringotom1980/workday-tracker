document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('[data-action="back"]')?.addEventListener('click', () => {
    if (window.history.length > 1) {
      window.history.back();
      return;
    }

    window.location.href = '/work-log.php';
  });
});
