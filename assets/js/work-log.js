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
    full_day: '\u6574\u65e5\u73ed',
    half_day: '\u534a\u65e5\u73ed',
    night: '\u591c\u73ed',
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
      status.textContent = '\u5c1a\u672a\u7d00\u9304';
      statusHint.textContent = '\u9ede\u9078\u73ed\u5225\u5373\u53ef\u7d00\u9304\u4eca\u5929\uff0c\u518d\u9ede\u4e00\u6b21\u76f8\u540c\u73ed\u5225\u53ef\u53d6\u6d88\u3002';
      return;
    }

    status.textContent = labels[log.work_type] || '\u5df2\u7d00\u9304';
    statusHint.textContent = `\u4eca\u5929\u63db\u7b97 ${Number(log.work_value).toFixed(1)} \u5929\u3002`;
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
        UI.toast('\u5df2\u53d6\u6d88\u7d00\u9304', 'info');
      } else {
        await Api.put('/api/work-logs.php', {
          date: today,
          work_type: workType,
          csrf_token: token,
        });
        UI.toast('\u5df2\u5132\u5b58\u7d00\u9304', 'success');
      }

      await loadData();
    } catch (error) {
      UI.toast(error.message || '\u66f4\u65b0\u5931\u6557', 'danger');
    } finally {
      setBusy(false);
    }
  }

  buttons.forEach((button) => {
    button.addEventListener('click', () => saveWorkType(button.dataset.workType));
  });

  loadData().catch((error) => {
    UI.toast(error.message || '\u8f09\u5165\u5931\u6557', 'danger');
  });
});
