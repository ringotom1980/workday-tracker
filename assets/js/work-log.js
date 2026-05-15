document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('.work-log-page');
  const selectedDateLabel = document.querySelector('#selected-date-label');
  const selectedDateButton = document.querySelector('#selected-date-button');
  const panel = document.querySelector('#date-picker-panel');
  const calendarTitle = document.querySelector('#calendar-title');
  const calendarGrid = document.querySelector('#date-picker-grid');
  const monthLabel = document.querySelector('#month-label');
  const status = document.querySelector('#current-status');
  const statusHint = document.querySelector('#status-hint');
  const buttons = [...document.querySelectorAll('.work-type-button')];
  const token = root?.dataset.csrfToken || '';
  let selectedDate = DateUtil.today();
  let calendarDate = parseDate(selectedDate);
  let currentLog = null;

  const labels = {
    full_day: '\u6574\u65e5\u73ed',
    half_day: '\u534a\u65e5\u73ed',
    night: '\u591c\u73ed',
  };

  function parseDate(value) {
    const [year, month, day] = value.split('-').map(Number);
    return new Date(year, month - 1, day);
  }

  function formatDate(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
  }

  function setBusy(isBusy) {
    buttons.forEach((button) => {
      button.disabled = isBusy;
    });
  }

  function selectedParts() {
    const date = parseDate(selectedDate);
    return {
      year: date.getFullYear(),
      month: date.getMonth() + 1,
    };
  }

  function toggleCalendar(show = panel.classList.contains('hidden')) {
    panel.classList.toggle('hidden', !show);
    selectedDateButton.setAttribute('aria-expanded', String(show));
  }

  function updateSelectedDateLabel() {
    selectedDateLabel.textContent = `\u9078\u5b9a\u65e5\u671f\uff1a${selectedDate}`;
    selectedDateButton.textContent = selectedDate;
  }

  function renderCalendar() {
    const year = calendarDate.getFullYear();
    const month = calendarDate.getMonth();
    const firstDay = new Date(year, month, 1);
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const offset = firstDay.getDay();
    const today = DateUtil.today();

    calendarTitle.textContent = `${year}-${String(month + 1).padStart(2, '0')}`;
    calendarGrid.innerHTML = '';

    for (let index = 0; index < offset; index++) {
      const empty = document.createElement('span');
      empty.className = 'date-picker-empty';
      calendarGrid.appendChild(empty);
    }

    for (let day = 1; day <= daysInMonth; day++) {
      const dateValue = formatDate(new Date(year, month, day));
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'date-picker-day';
      button.textContent = String(day);
      button.dataset.date = dateValue;
      button.classList.toggle('is-selected', dateValue === selectedDate);
      button.classList.toggle('is-today', dateValue === today);
      button.addEventListener('click', async () => {
        selectedDate = dateValue;
        calendarDate = parseDate(selectedDate);
        updateSelectedDateLabel();
        renderCalendar();
        toggleCalendar(false);
        await loadData();
      });
      calendarGrid.appendChild(button);
    }
  }

  function updateSelectedLog(log) {
    currentLog = log;

    buttons.forEach((button) => {
      const isActive = log?.work_type === button.dataset.workType;
      button.classList.toggle('btn-primary', isActive);
      button.classList.toggle('btn-secondary', !isActive);
      button.classList.toggle('is-active', isActive);
    });

    if (!log) {
      status.textContent = '\u5c1a\u672a\u7d00\u9304';
      statusHint.textContent = '\u9ede\u9078\u73ed\u5225\u5373\u53ef\u7d00\u9304\u9078\u5b9a\u65e5\u671f\uff0c\u518d\u9ede\u4e00\u6b21\u76f8\u540c\u73ed\u5225\u53ef\u53d6\u6d88\u3002';
      return;
    }

    status.textContent = labels[log.work_type] || '\u5df2\u7d00\u9304';
    statusHint.textContent = `\u9078\u5b9a\u65e5\u671f\u63db\u7b97 ${Number(log.work_value).toFixed(1)} \u5929\u3002`;
  }

  function updateSummary(data) {
    document.querySelector('#month-total').textContent = Number(data.summary.total_value).toFixed(1);
    document.querySelector('#month-records').textContent = data.summary.record_count;
    document.querySelector('#month-full').textContent = data.summary.full_day;
    document.querySelector('#month-half').textContent = data.summary.half_day;
    document.querySelector('#month-night').textContent = data.summary.night;
  }

  async function loadData() {
    const { year, month } = selectedParts();
    updateSelectedDateLabel();
    monthLabel.textContent = `${year}-${String(month).padStart(2, '0')}`;

    const [dayData, monthData] = await Promise.all([
      Api.get(`/api/work-logs.php?date=${selectedDate}`),
      Api.get(`/api/work-logs.php?year=${year}&month=${month}`),
    ]);

    updateSelectedLog(dayData.log);
    updateSummary(monthData);
    renderCalendar();
  }

  async function saveWorkType(workType) {
    setBusy(true);

    try {
      if (currentLog?.work_type === workType) {
        await Api.delete(`/api/work-logs.php?date=${selectedDate}&csrf_token=${encodeURIComponent(token)}`);
        UI.toast('\u5df2\u53d6\u6d88\u7d00\u9304', 'info');
      } else {
        await Api.put('/api/work-logs.php', {
          date: selectedDate,
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

  selectedDateButton.addEventListener('click', () => {
    toggleCalendar();
  });

  document.querySelector('[data-calendar-prev]')?.addEventListener('click', () => {
    calendarDate = new Date(calendarDate.getFullYear(), calendarDate.getMonth() - 1, 1);
    renderCalendar();
  });

  document.querySelector('[data-calendar-next]')?.addEventListener('click', () => {
    calendarDate = new Date(calendarDate.getFullYear(), calendarDate.getMonth() + 1, 1);
    renderCalendar();
  });

  buttons.forEach((button) => {
    button.addEventListener('click', () => saveWorkType(button.dataset.workType));
  });

  loadData().catch((error) => {
    UI.toast(error.message || '\u8f09\u5165\u5931\u6557', 'danger');
  });
});
