window.FormUtil = (() => {
  function debounce(fn, delay = 300) {
    let timer = null;
    return (...args) => {
      window.clearTimeout(timer);
      timer = window.setTimeout(() => fn(...args), delay);
    };
  }

  function formatMoney(value) {
    const number = Number(value || 0);
    return new Intl.NumberFormat('zh-TW', {
      maximumFractionDigits: 0,
    }).format(number);
  }

  function setError(input, message) {
    const group = input.closest('.form-group');
    const error = group?.querySelector('.form-error');
    if (error) {
      error.textContent = message || '';
    }
  }

  return { debounce, formatMoney, setError };
})();
