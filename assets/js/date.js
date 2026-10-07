window.DateUtil = (() => {
  function pad(value) {
    return String(value).padStart(2, '0');
  }

  function today() {
    const now = new Date();
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
  }

  function monthDays(year, month) {
    return new Date(year, month, 0).getDate();
  }

  function formatDate(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
  }

  return { today, monthDays, formatDate };
})();
