document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('.admin-users-page');
  const token = root?.dataset.csrfToken || '';
  const list = document.querySelector('#admin-users-list');
  const count = document.querySelector('#admin-users-count');
  const search = document.querySelector('#user-search');
  const filterButtons = [...document.querySelectorAll('[data-status-filter]')];
  const form = document.querySelector('#admin-user-form');
  const editId = document.querySelector('#edit-user-id');
  const mode = document.querySelector('#user-form-mode');
  const cancelEdit = document.querySelector('#cancel-edit-button');
  const passwordGroup = document.querySelector('#password-create-group');
  let statusFilter = root?.dataset.initialStatus || '';
  let users = [];

  function label(value) {
    const labels = {
      admin: '\u7ba1\u7406\u8005',
      user: '\u4e00\u822c\u4f7f\u7528\u8005',
      active: '\u555f\u7528',
      disabled: '\u505c\u7528',
    };
    return labels[value] || value;
  }

  function statusClass(status) {
    return status === 'active' ? 'badge-success' : 'badge-danger';
  }

  function escapeHtml(value) {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function setFilterButtonState() {
    filterButtons.forEach((button) => {
      const active = button.dataset.statusFilter === statusFilter;
      button.classList.toggle('btn-primary', active);
      button.classList.toggle('btn-secondary', !active);
    });
  }

  function resetForm() {
    form.reset();
    editId.value = '';
    mode.textContent = '\u65b0\u589e';
    cancelEdit.classList.add('hidden');
    passwordGroup.classList.remove('hidden');
  }

  function editUser(user) {
    editId.value = user.id;
    document.querySelector('#admin-username').value = user.username;
    document.querySelector('#admin-real-name').value = user.real_name;
    document.querySelector('#admin-email').value = user.email;
    document.querySelector('#admin-role').value = user.role;
    document.querySelector('#admin-password').value = '';
    mode.textContent = '\u7de8\u8f2f';
    cancelEdit.classList.remove('hidden');
    passwordGroup.classList.add('hidden');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function renderUsers() {
    count.textContent = users.length;
    if (users.length === 0) {
      list.innerHTML = '<div class="card text-center text-muted">\u6c92\u6709\u7b26\u5408\u689d\u4ef6\u7684\u4f7f\u7528\u8005</div>';
      return;
    }

    list.innerHTML = users.map((user) => `
      <article class="card admin-user-card" data-user-id="${user.id}">
        <div class="admin-user-main">
          <div>
            <h3 class="card-title">${escapeHtml(user.real_name)}</h3>
            <p class="text-muted">@${escapeHtml(user.username)} · ${escapeHtml(user.email)}</p>
          </div>
          <div class="cluster">
            <span class="badge badge-muted">${label(user.role)}</span>
            <span class="badge ${statusClass(user.status)}">${label(user.status)}</span>
          </div>
        </div>
        <div class="admin-user-actions">
          <button class="btn btn-sm btn-secondary" type="button" data-user-action="edit">\u7de8\u8f2f</button>
          <button class="btn btn-sm btn-outline" type="button" data-user-action="reset">\u91cd\u8a2d\u5bc6\u78bc</button>
          <button class="btn btn-sm ${user.status === 'active' ? 'btn-danger' : 'btn-primary'}" type="button" data-user-action="status">
            ${user.status === 'active' ? '\u505c\u7528' : '\u555f\u7528'}
          </button>
        </div>
      </article>
    `).join('');
  }

  async function loadUsers() {
    const params = new URLSearchParams();
    if (statusFilter) {
      params.set('status', statusFilter);
    }
    if (search.value.trim() !== '') {
      params.set('q', search.value.trim());
    }

    try {
      const data = await Api.get(`/api/admin/users.php?${params.toString()}`);
      users = data.users;
      renderUsers();
      setFilterButtonState();
    } catch (error) {
      UI.toast(error.message || '\u8f09\u5165\u5931\u6557', 'danger');
    }
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(form);
    const id = editId.value;
    const payload = {
      id,
      username: formData.get('username'),
      real_name: formData.get('real_name'),
      email: formData.get('email'),
      role: formData.get('role'),
      password: formData.get('password'),
      csrf_token: token,
    };

    try {
      if (id) {
        await Api.put('/api/admin/users.php', payload);
        UI.toast('\u5df2\u5132\u5b58\u4f7f\u7528\u8005', 'success');
      } else {
        await Api.post('/api/admin/users.php', payload);
        UI.toast('\u5df2\u65b0\u589e\u4f7f\u7528\u8005', 'success');
      }
      resetForm();
      await loadUsers();
    } catch (error) {
      UI.toast(error.message || '\u5132\u5b58\u5931\u6557', 'danger');
    }
  });

  list.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-user-action]');
    const card = event.target.closest('[data-user-id]');
    if (!button || !card) {
      return;
    }

    const user = users.find((item) => String(item.id) === card.dataset.userId);
    if (!user) {
      return;
    }

    if (button.dataset.userAction === 'edit') {
      editUser(user);
      return;
    }

    if (button.dataset.userAction === 'status') {
      const nextStatus = user.status === 'active' ? 'disabled' : 'active';
      try {
        await Api.patch('/api/admin/users.php?action=status', {
          id: user.id,
          status: nextStatus,
          csrf_token: token,
        });
        UI.toast('\u72c0\u614b\u5df2\u66f4\u65b0', 'success');
        await loadUsers();
      } catch (error) {
        UI.toast(error.message || '\u72c0\u614b\u66f4\u65b0\u5931\u6557', 'danger');
      }
      return;
    }

    if (button.dataset.userAction === 'reset') {
      const password = window.prompt('\u8acb\u8f38\u5165\u65b0\u5bc6\u78bc\uff08\u81f3\u5c11 4 \u78bc\uff09');
      if (!password) {
        return;
      }
      try {
        await Api.patch('/api/admin/users.php?action=reset_password', {
          id: user.id,
          password,
          csrf_token: token,
        });
        UI.toast('\u5bc6\u78bc\u5df2\u91cd\u8a2d', 'success');
      } catch (error) {
        UI.toast(error.message || '\u91cd\u8a2d\u5931\u6557', 'danger');
      }
    }
  });

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      statusFilter = button.dataset.statusFilter;
      loadUsers();
    });
  });

  search.addEventListener('input', FormUtil.debounce(loadUsers, 350));
  cancelEdit.addEventListener('click', resetForm);

  setFilterButtonState();
  loadUsers();
});
