document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#login-form');
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const identifierInput = document.querySelector('#identifier');
  const passwordInput = document.querySelector('#password');
  const rememberAccount = document.querySelector('#remember-account');
  const rememberPassword = document.querySelector('#remember-password');
  const accountKey = 'workday.login.identifier';
  const passwordKey = 'workday.login.password';

  const savedIdentifier = localStorage.getItem(accountKey);
  const savedPassword = localStorage.getItem(passwordKey);

  if (savedIdentifier) {
    identifierInput.value = savedIdentifier;
    rememberAccount.checked = true;
  }

  if (savedPassword) {
    passwordInput.value = savedPassword;
    rememberPassword.checked = true;
    rememberAccount.checked = true;
  }

  rememberPassword?.addEventListener('change', () => {
    if (rememberPassword.checked) {
      rememberAccount.checked = true;
    } else {
      localStorage.removeItem(passwordKey);
    }
  });

  rememberAccount?.addEventListener('change', () => {
    if (!rememberAccount.checked) {
      rememberPassword.checked = false;
      localStorage.removeItem(accountKey);
      localStorage.removeItem(passwordKey);
    }
  });

  function saveRememberedLogin(formData) {
    if (rememberAccount.checked || rememberPassword.checked) {
      localStorage.setItem(accountKey, String(formData.get('identifier') || ''));
    } else {
      localStorage.removeItem(accountKey);
    }

    if (rememberPassword.checked) {
      localStorage.setItem(passwordKey, String(formData.get('password') || ''));
    } else {
      localStorage.removeItem(passwordKey);
    }
  }

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

      saveRememberedLogin(formData);
      UI.toast('\u767b\u5165\u6210\u529f', 'success');
      window.location.href = data.redirect || '/work-log.php';
    } catch (error) {
      UI.toast(error.message || '\u767b\u5165\u5931\u6557', 'danger');
    } finally {
      submitButton.disabled = false;
      UI.loading(false);
    }
  });
});
