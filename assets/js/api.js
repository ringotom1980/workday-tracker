window.Api = (() => {
  async function request(url, options = {}) {
    const headers = {
      Accept: 'application/json',
      ...(options.headers || {}),
    };

    if (options.body && !(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(options.body);
    }

    const response = await fetch(url, {
      credentials: 'same-origin',
      ...options,
      headers,
    });

    const payload = await response.json().catch(() => ({
      success: false,
      data: null,
      message: '伺服器回傳格式錯誤',
    }));

    if (!response.ok || payload.success === false) {
      throw new Error(payload.message || '請求失敗');
    }

    return payload.data;
  }

  return {
    get: (url) => request(url),
    post: (url, body) => request(url, { method: 'POST', body }),
    put: (url, body) => request(url, { method: 'PUT', body }),
    patch: (url, body) => request(url, { method: 'PATCH', body }),
    delete: (url) => request(url, { method: 'DELETE' }),
  };
})();
