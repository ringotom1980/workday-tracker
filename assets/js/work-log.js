document.addEventListener('DOMContentLoaded', () => {
  const todayDate = document.querySelector('#today-date');
  if (todayDate) {
    todayDate.textContent = DateUtil.today();
  }
});
