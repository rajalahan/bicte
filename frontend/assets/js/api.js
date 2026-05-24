/* ============================================================
 * api.js – thin API client with offline JSON fallback
 *
 *  - Tries the Laravel API first (configurable base URL).
 *  - On any error (offline kiosk, LAN down, server restart) falls back
 *    to bundled assets/data/*.json so the kiosk keeps working.
 *  - Caches successful responses in memory to avoid re-fetching during
 *    a session.
 * Exposes:  window.API
 * ============================================================ */
(function (global) {
  'use strict';

  // Allow runtime override via <meta name="api-base" content="https://kiosk.lahanmun.gov.np/api"/>
  const meta = document.querySelector('meta[name="api-base"]');
  const API_BASE = (meta && meta.content) || (location.hostname === 'localhost' || location.hostname === '127.0.0.1'
    ? 'http://127.0.0.1:8000/api'
    : '/api');

  const TIMEOUT_MS = 4000;
  const cache = new Map();

  async function fetchJson(url, opts = {}) {
    const ctrl = new AbortController();
    const t = setTimeout(() => ctrl.abort(), TIMEOUT_MS);
    try {
      const res = await fetch(url, { ...opts, signal: ctrl.signal, headers: {
        'Accept': 'application/json', ...(opts.headers || {})
      }});
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return await res.json();
    } finally { clearTimeout(t); }
  }

  async function fromApiOrFallback(apiPath, fallbackFile, transform) {
    if (cache.has(apiPath)) return cache.get(apiPath);
    let data = null;
    try {
      data = await fetchJson(API_BASE + apiPath);
      // Laravel API resources usually wrap in { data: ... }
      if (data && typeof data === 'object' && 'data' in data) data = data.data;
    } catch (e) {
      try {
        data = await fetchJson('assets/data/' + fallbackFile);
      } catch (e2) {
        console.error('[api] both API and fallback failed', e, e2);
        data = null;
      }
    }
    if (transform && data) data = transform(data);
    if (data) cache.set(apiPath, data);
    return data;
  }

  const API = {
    BASE: API_BASE,

    officials:    () => fromApiOrFallback('/officials',    'officials.json'),
    departments:  () => fromApiOrFallback('/departments',  'departments.json'),
    department:   (slug) => fromApiOrFallback('/departments/' + encodeURIComponent(slug), 'departments.json',
                    list => Array.isArray(list) ? list.find(d => d.slug === slug) : list),
    notices:      () => fromApiOrFallback('/notices',      'notices.json'),
    faq:          () => fromApiOrFallback('/faq',          'faq.json'),
    achievements: () => fromApiOrFallback('/achievements', 'achievements.json'),

    // Write endpoints – best-effort; show error if API unreachable.
    submitFeedback: (payload) =>
      fetchJson(API_BASE + '/feedback', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }),
    issueToken: (payload) =>
      fetchJson(API_BASE + '/tokens', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }),
    askAssistant: (q) =>
      fetchJson(API_BASE + '/assistant/ask', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ q }) }),
  };

  global.API = API;
})(window);
