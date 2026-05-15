document.addEventListener('DOMContentLoaded', () => {
  const title = document.querySelector('#leaderboard-month-title');
  const periodLabel = document.querySelector('#leaderboard-period-label');
  const winner = document.querySelector('#leaderboard-winner');
  const summary = document.querySelector('#leaderboard-summary');
  const list = document.querySelector('#leaderboard-list');
  const periodButtons = [...document.querySelectorAll('[data-period]')];
  const now = new Date();
  let currentYear = now.getFullYear();
  let currentMonth = now.getMonth() + 1;
  let currentPeriod = 'month';

  const periodNames = {
    month: '\u672c\u6708',
    first_half: '\u4e0a\u534a\u5e74',
    second_half: '\u4e0b\u534a\u5e74',
    year: '\u5168\u5e74',
  };

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function monthText() {
    return `${currentYear}-${String(currentMonth).padStart(2, '0')}`;
  }

  function setPeriodButtons() {
    periodButtons.forEach((button) => {
      const active = button.dataset.period === currentPeriod;
      button.classList.toggle('btn-primary', active);
      button.classList.toggle('btn-secondary', !active);
    });
  }

  function render(data) {
    const items = data.items || [];
    const maxDays = Math.max(...items.map((item) => Number(item.total_days)), 0);
    title.textContent = monthText();
    periodLabel.textContent = periodNames[currentPeriod];

    if (items.length === 0) {
      winner.textContent = '-';
      summary.textContent = '\u9084\u6c92\u6709\u8cc7\u6599';
      list.innerHTML = '<div class="card text-center text-muted">\u9084\u6c92\u6709\u4efb\u4f55\u4e0a\u73ed\u7d00\u9304</div>';
      return;
    }

    const top = items[0];
    winner.textContent = `${top.real_name} · ${Number(top.total_days).toFixed(1)} \u5929`;
    summary.textContent = `${data.label} · ${items.length} \u4eba\u53c3\u8207\u6392\u884c`;

    list.innerHTML = items.map((item) => {
      const days = Number(item.total_days);
      const width = maxDays > 0 ? Math.max(6, Math.round((days / maxDays) * 100)) : 0;
      const medalClass = item.rank === 1 ? 'is-gold' : item.rank === 2 ? 'is-silver' : item.rank === 3 ? 'is-bronze' : '';
      return `
        <article class="card leaderboard-row ${medalClass}">
          <div class="leaderboard-row-head">
            <span class="leaderboard-rank">#${item.rank}</span>
            <div>
              <h3 class="card-title">${escapeHtml(item.real_name)}</h3>
              <p class="text-muted">@${escapeHtml(item.username)} · ${item.record_count} \u7b46</p>
            </div>
            <strong class="number">${days.toFixed(1)} \u5929</strong>
          </div>
          <div class="leaderboard-bar-track">
            <div class="leaderboard-bar" style="width:${width}%"></div>
          </div>
        </article>
      `;
    }).join('');
  }

  async function loadLeaderboard() {
    try {
      const data = await Api.get(`/api/leaderboard.php?year=${currentYear}&month=${currentMonth}&period=${currentPeriod}`);
      render(data);
      setPeriodButtons();
    } catch (error) {
      UI.toast(error.message || '\u8f09\u5165\u5931\u6557', 'danger');
    }
  }

  document.querySelector('[data-leaderboard-prev]')?.addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 1) {
      currentMonth = 12;
      currentYear--;
    }
    loadLeaderboard();
  });

  document.querySelector('[data-leaderboard-next]')?.addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 12) {
      currentMonth = 1;
      currentYear++;
    }
    loadLeaderboard();
  });

  periodButtons.forEach((button) => {
    button.addEventListener('click', () => {
      currentPeriod = button.dataset.period;
      loadLeaderboard();
    });
  });

  setPeriodButtons();
  loadLeaderboard();
});
