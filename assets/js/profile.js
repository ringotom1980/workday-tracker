document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('.profile-page');
  const token = root?.dataset.csrfToken || '';
  const profileForm = document.querySelector('#profile-form');
  const passwordForm = document.querySelector('#password-form');
  const logoutButton = document.querySelector('#logout-button');
  const profileStatus = document.querySelector('#profile-status');

  function setStatus(text, type = 'muted') {
    profileStatus.textContent = text;
    profileStatus.className = `badge badge-${type}`;
  }

  profileForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(profileForm);
    setStatus('\u5132\u5b58\u4e2d', 'warning');

    try {
      await Api.put('/api/profile.php?action=profile', {
        real_name: formData.get('real_name'),
        username: formData.get('username'),
        csrf_token: token,
      });
      setStatus('\u5df2\u5132\u5b58', 'success');
      UI.toast('\u5df2\u5132\u5b58\u500b\u4eba\u8cc7\u6599', 'success');
    } catch (error) {
      setStatus('\u5132\u5b58\u5931\u6557', 'danger');
      UI.toast(error.message || '\u5132\u5b58\u5931\u6557', 'danger');
    }
  });

  passwordForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(passwordForm);

    try {
      await Api.put('/api/profile.php?action=password', {
        current_password: formData.get('current_password'),
        new_password: formData.get('new_password'),
        confirm_password: formData.get('confirm_password'),
        csrf_token: token,
      });
      passwordForm.reset();
      UI.toast('\u5bc6\u78bc\u5df2\u66f4\u65b0', 'success');
    } catch (error) {
      UI.toast(error.message || '\u5bc6\u78bc\u66f4\u65b0\u5931\u6557', 'danger');
    }
  });

  logoutButton?.addEventListener('click', async () => {
    try {
      await Api.post('/api/auth.php?action=logout', { csrf_token: token });
      window.location.href = '/login.php';
    } catch (error) {
      UI.toast(error.message || '\u767b\u51fa\u5931\u6557', 'danger');
    }
  });
});
