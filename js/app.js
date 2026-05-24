const API = {
  getToken() { return localStorage.getItem('kz_token'); },
  setToken(t) { localStorage.setItem('kz_token', t); },
  clearAuth() { localStorage.removeItem('kz_token'); localStorage.removeItem('kz_user'); },
  getUser() { const u = localStorage.getItem('kz_user'); return u ? JSON.parse(u) : null; },
  setUser(u) { localStorage.setItem('kz_user', JSON.stringify(u)); },
  isLoggedIn() { return !!this.getToken(); },

  async request(path, options = {}) {
    const token = this.getToken();
    const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
    if (token) headers['Authorization'] = 'Bearer ' + token;
    try {
      const res = await fetch('/api' + path, { ...options, headers });
      const data = await res.json().catch(() => ({}));
      if (res.status === 401) { this.clearAuth(); window.location.href = '/login'; return null; }
      return { ok: res.ok, status: res.status, data };
    } catch (e) {
      return { ok: false, status: 0, data: { error: 'خطأ في الاتصال' } };
    }
  },
  get(path) { return this.request(path, { method: 'GET' }); },
  post(path, body) { return this.request(path, { method: 'POST', body: JSON.stringify(body) }); },
  put(path, body) { return this.request(path, { method: 'PUT', body: JSON.stringify(body) }); },
  del(path) { return this.request(path, { method: 'DELETE' }); },
};

function requireAuth() {
  if (!API.isLoggedIn()) { window.location.href = '/login'; return false; }
  return true;
}

function requireGuest() {
  if (API.isLoggedIn()) { window.location.href = '/dashboard'; return false; }
  return true;
}

function showToast(msg, type = 'success') {
  const t = document.createElement('div');
  t.className = 'toast toast-' + type;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
}

const TOAST_CSS = `
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(20px);
background:#1a1a1a;color:#fff;padding:12px 22px;border-radius:50px;font-size:14px;
font-weight:600;opacity:0;transition:all .3s;z-index:9999;white-space:nowrap;
border:1px solid #333;font-family:'GraphikArabic',sans-serif}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
.toast-error{background:#1a0001;border-color:#3a0002;color:#f87171}
.toast-success{background:#001a0a;border-color:#00401a;color:#4ade80}
`;
const style = document.createElement('style');
style.textContent = TOAST_CSS;
document.head.appendChild(style);
