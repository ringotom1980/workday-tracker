document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#register-form');
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    const submitButton = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    submitButton.disabled = true;
    UI.loading(true);

    try {
      const data = await Api.post('/api/register.php', {
        username: formData.get('username'),
        real_name: formData.get('real_name'),
        email: formData.get('email'),
        password: formData.get('password'),
        confirm_password: formData.get('confirm_password'),
        csrf_token: token,
      });

      UI.toast('\u8a3b\u518a\u6210\u529f', 'success');
      window.location.href = data.redirect || '/work-log.php';
    } catch (error) {
      UI.toast(error.message || '\u8a3b\u518a\u5931\u6557', 'danger');
    } finally {
      submitButton.disabled = false;
      UI.loading(false);
    }
  });
});
