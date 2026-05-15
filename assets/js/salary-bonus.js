document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('.salary-page');
  const token = root?.dataset.csrfToken || '';
  const monthBadge = document.querySelector('#salary-month-badge');
  const monthTitle = document.querySelector('#salary-month-title');
  const saveStatus = document.querySelector('#save-status');
  const dailySalaryInput = document.querySelector('#daily-salary');
  const firstHalfBonusBaseInput = document.querySelector('#first-half-bonus-base');
  const secondHalfBonusBaseInput = document.querySelector('#second-half-bonus-base');
  const now = new Date();
  let currentYear = now.getFullYear();
  let currentMonth = now.getMonth() + 1;
  let isLoading = false;

  function monthText() {
    return `${currentYear}-${String(currentMonth).padStart(2, '0')}`;
  }

  function money(value) {
    return FormUtil.formatMoney(value);
  }

  function setStatus(text, type = 'muted') {
    saveStatus.textContent = text;
    saveStatus.className = `badge badge-${type}`;
  }

  function render(data, updateInputs = true) {
    monthBadge.textContent = monthText();
    monthTitle.textContent = monthText();

    if (updateInputs) {
      dailySalaryInput.value = Number(data.settings.daily_salary || 0) || '';
      firstHalfBonusBaseInput.value = Number(data.settings.first_half_bonus_base || 0) || '';
      secondHalfBonusBaseInput.value = Number(data.settings.second_half_bonus_base || 0) || '';
    }

    document.querySelector('#monthly-work-days').textContent = Number(data.monthly_work_days).toFixed(1);
    document.querySelector('#first-half-work-days').textContent = Number(data.first_half_work_days).toFixed(1);
    document.querySelector('#second-half-work-days').textContent = Number(data.second_half_work_days).toFixed(1);
    document.querySelector('#monthly-salary').textContent = money(data.monthly_salary);
    document.querySelector('#first-half-bonus').textContent = money(data.first_half_bonus);
    document.querySelector('#second-half-bonus').textContent = money(data.second_half_bonus);
  }

  async function loadData() {
    isLoading = true;
    setStatus('\u8f09\u5165\u4e2d', 'muted');

    try {
      const data = await Api.get(`/api/salary.php?action=settings&year=${currentYear}&month=${currentMonth}`);
      render(data, true);
      setStatus('\u5df2\u8f09\u5165', 'muted');
    } catch (error) {
      UI.toast(error.message || '\u8f09\u5165\u5931\u6557', 'danger');
      setStatus('\u8f09\u5165\u5931\u6557', 'danger');
    } finally {
      isLoading = false;
    }
  }

  async function saveSettings() {
    if (isLoading) {
      return;
    }

    setStatus('\u5132\u5b58\u4e2d', 'warning');

    try {
      const data = await Api.put('/api/salary.php?action=settings', {
        year: currentYear,
        month: currentMonth,
        daily_salary: Number(dailySalaryInput.value || 0),
        first_half_bonus_base: Number(firstHalfBonusBaseInput.value || 0),
        second_half_bonus_base: Number(secondHalfBonusBaseInput.value || 0),
        csrf_token: token,
      });

      render(data, false);
      setStatus('\u5df2\u5132\u5b58', 'success');
    } catch (error) {
      UI.toast(error.message || '\u5132\u5b58\u5931\u6557', 'danger');
      setStatus('\u5132\u5b58\u5931\u6557', 'danger');
    }
  }

  const debouncedSave = FormUtil.debounce(saveSettings, 1000);

  [dailySalaryInput, firstHalfBonusBaseInput, secondHalfBonusBaseInput].forEach((input) => {
    input.addEventListener('input', () => {
      setStatus('\u31561\u79d2\u81ea\u52d5\u5132\u5b58', 'warning');
      debouncedSave();
    });
  });

  document.querySelector('[data-salary-prev]')?.addEventListener('click', () => {
    currentMonth--;
    if (currentMonth < 1) {
      currentMonth = 12;
      currentYear--;
    }
    loadData();
  });

  document.querySelector('[data-salary-next]')?.addEventListener('click', () => {
    currentMonth++;
    if (currentMonth > 12) {
      currentMonth = 1;
      currentYear++;
    }
    loadData();
  });

  loadData();
});
