document.addEventListener('DOMContentLoaded', () => {
  const status = document.querySelector('#dashboard-status');

  function setText(selector, value) {
    const element = document.querySelector(selector);
    if (element) {
      element.textContent = value;
    }
  }

  function statusLabel(value) {
    if (value === 'success') {
      return '\u6210\u529f';
    }
    if (value === 'failed') {
      return '\u5931\u6557';
    }
    return '-';
  }

  async function loadDashboard() {
    try {
      const data = await Api.get('/api/admin/dashboard.php');
      setText('#total-users', data.users.total);
      setText('#active-users', data.users.active);
      setText('#disabled-users', data.users.disabled);
      setText('#today-logs', data.work_logs.today);
      setText('#month-logs', data.work_logs.month);

      const job = data.calendar_job;
      setText('#calendar-job-status', statusLabel(job?.status));
      setText('#calendar-job-time', job?.finished_at || job?.created_at || '-');
      setText('#calendar-job-records', job?.records_processed || 0);
      setText('#calendar-job-message', job?.message || '-');

      status.textContent = '\u5df2\u66f4\u65b0';
      status.className = 'badge badge-success';
      document.querySelector('#calendar-job-status').className = `badge badge-${job?.status === 'success' ? 'success' : 'danger'}`;
    } catch (error) {
      status.textContent = '\u8f09\u5165\u5931\u6557';
      status.className = 'badge badge-danger';
      UI.toast(error.message || '\u8f09\u5165\u5931\u6557', 'danger');
    }
  }

  loadDashboard();
});
