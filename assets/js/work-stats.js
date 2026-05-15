document.addEventListener('DOMContentLoaded', () => {
  const title = document.querySelector('#stats-month-title');
  const grid = document.querySelector('#stats-calendar-grid');
  const selected = new Date();
  let currentYear = selected.getFullYear();
  let currentMonth = selected.getMonth() + 1;

  const workLabels = {
    full_day: '\u6574',
    half_day: '\u534a',
    night: '\u591c',
  };

  function dateValue(year, month, day) {
    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  }

  function escapeHtml(value) {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function renderSummary(stats) {
    document.querySelector('#stats-total').textContent = Number(stats.summary.total_value).toFixed(1);
    document.querySelector('#stats-record-count').textContent = `${stats.summary.record_count} \u7b46`;
    document.querySelector('#stats-full').textContent = stats.summary.full_day;
    document.querySelector('#stats-half').textContent = stats.summary.half_day;
    document.querySelector('#stats-night').textContent = stats.summary.night;
  }

  function renderCalendar(stats, calendar) {
    const logs = new Map(stats.logs.map((log) => [log.work_date, log]));
    const calendarDays = new Map(calendar.days.map((day) => [day.calendar_date, day]));
    const firstDay = new Date(currentYear, currentMonth - 1, 1);
    const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
    const offset = firstDay.getDay();
    const today = DateUtil.today();

    title.textContent = `${currentYear}-${String(currentMonth).padStart(2, '0')}`;
    grid.innerHTML = '';

    for (let index = 0; index < offset; index++) {
      const empty = document.createElement('span');
      empty.className = 'stats-calendar-empty';
      grid.appendChild(empty);
    }

    for (let day = 1; day <= daysInMonth; day++) {
      const value = dateValue(currentYear, currentMonth, day);
      const log = logs.get(value);
      const calendarDay = calendarDays.get(value);
      const cell = document.createElement('div');
      cell.className = 'stats-calendar-day';
      cell.classList.toggle('is-today', value === today);
      cell.classList.toggle('has-record', Boolean(log));
      cell.classList.toggle('is-holiday', Number(calendarDay?.is_holiday) === 1);
      cell.classList.toggle('is-makeup', Number(calendarDay?.is_makeup_workday) === 1);

      const badges = [];
      if (log) {
        badges.push(`<span class="calendar-chip calendar-chip-work">${workLabels[log.work_type] || ''}</span>`);
      }
      if (Number(calendarDay?.is_holiday) === 1) {
        badges.push('<span class="calendar-chip calendar-chip-holiday">\u4f11</span>');
      }
      if (Number(calendarDay?.is_makeup_workday) === 1) {
        badges.push('<span class="calendar-chip calendar-chip-makeup">\u88dc</span>');
      }

      cell.innerHTML = `
        <strong>${day}</strong>
        <div class="calendar-chip-row">${badges.join('')}</div>
        ${calendarDay?.title ? `<small>${escapeHtml(calendarDay.title)}</small>` : ''}
      `;
      grid.appendChild(cell);
    }
  }

  async function loadStats() {
    UI.loading(true);

    try {
      const [stats, calendar] = await Promise.all([
        Api.get(`/api/work-stats.php?year=${currentYear}&month=${currentMonth}`),
        Api.get(`/api/calendar.php?year=${currentYear}&month=${currentMonth}`),
      ]);

      renderSummary(stats);
      renderCalendar(stats, calendar);
    } catch (error) {
      UI.toast(error.message || '\u8f09\u5165\u5931\u6557', 'danger');
    } finally {
      UI.loading(false);
    }
  }

  document.querySelector('[data-month-prev]')?.addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 1) {
      currentMonth = 12;
      currentYear--;
    }
    loadStats();
  });

  document.querySelector('[data-month-next]')?.addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 12) {
      currentMonth = 1;
      currentYear++;
    }
    loadStats();
  });

  loadStats();
});
