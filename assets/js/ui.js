window.UI = (() => {
  function toast(message, type = 'info') {
    const existing = document.querySelector('.toast');
    existing?.remove();

    const element = document.createElement('div');
    element.className = `toast toast-${type}`;
    element.textContent = message;
    document.body.appendChild(element);

    window.setTimeout(() => element.remove(), 2800);
  }

  function loading(show = true) {
    const existing = document.querySelector('[data-loading-overlay]');
    if (!show) {
      existing?.remove();
      return;
    }

    if (existing) {
      return;
    }

    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.dataset.loadingOverlay = 'true';
    overlay.innerHTML = '<div class="spinner" role="status" aria-label="載入中"></div>';
    document.body.appendChild(overlay);
  }

  async function confirm(message) {
    return window.confirm(message);
  }

  return { toast, loading, confirm };
})();
