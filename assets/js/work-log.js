document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('.work-log-page');
  const todayDate = document.querySelector('#today-date');
  const monthLabel = document.querySelector('#month-label');
  const status = document.querySelector('#current-status');
  const statusHint = document.querySelector('#status-hint');
  const buttons = [...document.querySelectorAll('.work-type-button')];
  const token = root?.dataset.csrfToken || '';
  const today = DateUtil.today();
  const now = new Date();
  let currentLog = null;

  const labels = {
    full_day: 'Full day',
    half_day: 'Half day',
    night: 'Night shift',
  };

  function setBusy(isBusy) {
    buttons.forEach((button) => {
      button.disabled = isBusy;
    });
  }

  function updateToday(log) {
    currentLog = log;

    buttons.forEach((button) => {
      const isActive = log?.work_type === button.dataset.workType;
      button.classList.toggle('btn-primary', isActive);
      button.classList.toggle('btn-secondary', !isActive);
      button.classList.toggle('is-active', isActive);
    });

    if (!log) {
      status.textContent = 'Not recorded yet';
      statusHint.textContent = 'Tap a shift type to record today. Tap the same type again to cancel.';
      return;
    }

    status.textContent = labels[log.work_type] || 'Recorded';
    statusHint.textContent = `${Number(log.work_value).toFixed(1)} day counted for today.`;
  }

  function updateSummary(data) {
    document.querySelector('#month-total').textContent = Number(data.summary.total_value).toFixed(1);
    document.querySelector('#month-records').textContent = data.summary.record_count;
    document.querySelector('#month-full').textContent = data.summary.full_day;
    document.querySelector('#month-half').textContent = data.summary.half_day;
    document.querySelector('#month-night').textContent = data.summary.night;
  }

  async function loadData() {
    const year = now.getFullYear();
    const month = now.getMonth() + 1;

    todayDate.textContent = today;
    monthLabel.textContent = `${year}-${String(month).padStart(2, '0')}`;

    const [dayData, monthData] = await Promise.all([
      Api.get(`/api/work-logs.php?date=${today}`),
      Api.get(`/api/work-logs.php?year=${year}&month=${month}`),
    ]);

    updateToday(dayData.log);
    updateSummary(monthData);
  }

  async function saveWorkType(workType) {
    setBusy(true);

    try {
      if (currentLog?.work_type === workType) {
        await Api.delete(`/api/work-logs.php?date=${today}&csrf_token=${encodeURIComponent(token)}`);
        UI.toast('Record cancelled', 'info');
      } else {
        await Api.put('/api/work-logs.php', {
          date: today,
          work_type: workType,
          csrf_token: token,
        });
        UI.toast('Record saved', 'success');
      }

      await loadData();
    } catch (error) {
      UI.toast(error.message || 'Update failed', 'danger');
    } finally {
      setBusy(false);
    }
  }

  buttons.forEach((button) => {
    button.addEventListener('click', () => saveWorkType(button.dataset.workType));
  });

  loadData().catch((error) => {
    UI.toast(error.message || 'Load failed', 'danger');
  });
});
