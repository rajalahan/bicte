/* ============================================================
 * app.js – page-agnostic kiosk bootstrap
 *  - Populates officials (mayor / deputy / CAO)
 *  - Renders achievements slider
 *  - Loads weather widget (Open-Meteo, free, no API key)
 *  - Idle detection → hides cursor on kiosk
 *  - Lang toggle, voice button hook
 *  - Inactivity → auto-return to home after 3 minutes
 *  - Discreet CMS admin link (bottom-right corner)
 * ============================================================ */
(function () {
  'use strict';

  const IDLE_AFTER_MS = 60_000;        // hide cursor after 1 min of no input
  const HOME_AFTER_MS = 3 * 60_000;    // auto-return to home after 3 min

  // ---------- Officials ----------
  async function loadOfficials() {
    const data = await window.API.officials();
    if (!data) return;
    bind('mayor.name',     data.mayor && data.mayor.name);
    bind('mayor.message',  data.mayor && data.mayor.message);
    bind('deputy.name',    data.deputy && data.deputy.name);
    bind('deputy.message', data.deputy && data.deputy.message);
    bind('cao.name',       data.cao && data.cao.name);
    bind('cao.message',    data.cao && data.cao.message);
    bind('office.phone',   data.office && data.office.phone);

    setPhoto('card-mayor',  data.mayor && data.mayor.photo);
    setPhoto('card-deputy', data.deputy && data.deputy.photo);
    setPhoto('card-cao',    data.cao && data.cao.photo);
  }

  function bind(key, value) {
    document.querySelectorAll(`[data-bind="${key}"]`).forEach(el => {
      if (value == null || value === '') return;
      if (el.tagName === 'A' && key === 'office.phone') {
        el.href = 'tel:' + String(value).replace(/[^+\d]/g, '');
      }
      el.textContent = value;
    });
  }
  function setPhoto(cardId, url) {
    if (!url) return;
    const card = document.getElementById(cardId);
    if (!card) return;
    const img = card.querySelector('img');
    if (img) img.src = url;
  }

  // ---------- Achievements ----------
  async function loadAchievements() {
    const wrap = document.getElementById('achievements');
    if (!wrap) return;
    const items = await window.API.achievements();
    if (!items || !items.length) { wrap.innerHTML = '<div class="achievement"><div class="achievement__title">शीघ्र अद्यावधिक हुनेछ।</div></div>'; return; }
    wrap.innerHTML = items.map(a => `
      <article class="achievement">
        <div class="achievement__year">${escapeHtml(a.year)}</div>
        <h4 class="achievement__title">${escapeHtml(a.title)}</h4>
        <p class="achievement__desc">${escapeHtml(a.description || '')}</p>
      </article>
    `).join('');
  }

  // ---------- Weather (Open-Meteo, no key, falls back gracefully) ----------
  async function loadWeather() {
    const root = document.getElementById('weather');
    if (!root) return;
    // Lahan, Siraha approx coords
    const url = 'https://api.open-meteo.com/v1/forecast?latitude=26.7244&longitude=86.4978' +
                '&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code';
    try {
      const ctrl = new AbortController();
      const t = setTimeout(() => ctrl.abort(), 5000);
      const res = await fetch(url, { signal: ctrl.signal });
      clearTimeout(t);
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const j = await res.json();
      const c = j.current || {};
      root.querySelector('.weather__temp').textContent = Math.round(c.temperature_2m) + '°C';
      root.querySelector('.weather__desc').textContent = weatherText(c.weather_code);
      root.querySelector('.weather__meta').innerHTML =
        `<i class="bi bi-droplet"></i> ${Math.round(c.relative_humidity_2m)}% &nbsp; ` +
        `<i class="bi bi-wind"></i> ${Math.round(c.wind_speed_10m)} km/h`;
    } catch (e) {
      root.querySelector('.weather__desc').textContent = 'मौसम जानकारी हाल उपलब्ध छैन।';
    }
  }
  function weatherText(code) {
    if (code == null) return '—';
    if (code === 0)  return 'सफा आकाश';
    if (code <= 3)   return 'आंशिक बादल';
    if (code <= 48)  return 'कुहिरो';
    if (code <= 67)  return 'झरी / पानी';
    if (code <= 77)  return 'हिउँ';
    if (code <= 82)  return 'भारी वर्षा';
    if (code <= 99)  return 'तूफान';
    return '—';
  }

  // ---------- Lang toggle ----------
  function setupLang() {
    const btn = document.getElementById('btn-lang');
    if (!btn) return;
    function paint() { btn.querySelector('span').textContent = window.I18N.lang === 'ne' ? 'EN' : 'ने'; }
    paint();
    btn.addEventListener('click', () => { window.I18N.toggleLang(); paint(); });
  }

  // ---------- Voice (Web Speech API – best effort, kiosk Chrome on Win) ----------
  function setupVoice() {
    const btn = document.getElementById('btn-voice');
    if (!btn) return;
    btn.addEventListener('click', () => {
      const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SR) { alert('यस यन्त्रमा आवाज पहिचान उपलब्ध छैन।'); return; }
      const rec = new SR();
      rec.lang = 'ne-NP';
      rec.onresult = (e) => {
        const q = e.results[0][0].transcript;
        location.href = 'assistant.html?q=' + encodeURIComponent(q);
      };
      rec.onerror = () => alert('आवाज पहिचान असफल भयो।');
      try { rec.start(); } catch (_) {}
    });
  }

  // ---------- Idle / auto-home ----------
  function setupIdle() {
    let idleTimer, homeTimer;
    function reset() {
      document.body.classList.remove('idle');
      clearTimeout(idleTimer); clearTimeout(homeTimer);
      idleTimer = setTimeout(() => document.body.classList.add('idle'), IDLE_AFTER_MS);
      const isHome = document.body.dataset.page === 'home';
      if (!isHome) {
        homeTimer = setTimeout(() => { location.href = 'index.html'; }, HOME_AFTER_MS);
      }
    }
    ['mousemove','touchstart','keydown','click','scroll'].forEach(ev =>
      window.addEventListener(ev, reset, { passive: true }));
    reset();
  }

  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }

  function start() {
    loadOfficials();
    loadAchievements();
    loadWeather();
    setupLang();
    setupVoice();
    setupIdle();
    injectAdminLink();
  }

  // ---------- Discreet admin / CMS link ----------
  // A tiny gear icon in the bottom-right corner – visible to staff,
  // unobtrusive for citizens. Override the URL by adding
  //   <meta name="admin-url" content="https://admin.lahanmun.gov.np/admin">
  // to any page (or to a shared template).
  function injectAdminLink() {
    if (document.querySelector('.admin-link')) return;
    const meta = document.querySelector('meta[name="admin-url"]');
    const adminUrl = (meta && meta.content) || 'http://127.0.0.1:8000/admin';
    const a = document.createElement('a');
    a.className = 'admin-link';
    a.href = adminUrl;
    a.target = '_blank';
    a.rel = 'noopener';
    a.setAttribute('aria-label', 'CMS Admin Login');
    a.title = 'प्रशासन (Admin CMS)';
    a.innerHTML = '<i class="bi bi-shield-lock-fill"></i><span class="admin-link__label">Admin</span>';
    document.body.appendChild(a);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
