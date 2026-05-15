document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#login-form');
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const submitButton = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    submitButton.disabled = true;
    UI.loading(true);

    try {
      const data = await Api.post('/api/auth.php?action=login', {
        identifier: formData.get('identifier'),
        password: formData.get('password'),
        csrf_token: token,
      });

      UI.toast('登入成功', 'success');
      window.location.href = data.redirect || '/work-log.php';
    } catch (error) {
      UI.toast(error.message || '登入失敗', 'danger');
    } finally {
      submitButton.disabled = false;
      UI.loading(false);
    }
  });
});
