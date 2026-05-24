/* ============================================================
 * ticker.js – populates the scrolling notice ticker
 * Pulls notices from API.notices() (with offline JSON fallback).
 * ============================================================ */
(function () {
  'use strict';

  async function init() {
    const track = document.getElementById('ticker-track');
    if (!track) return;

    try {
      const notices = await window.API.notices();
      if (!notices || !notices.length) {
        track.textContent = 'हाल कुनै सूचना उपलब्ध छैन।';
        return;
      }
      track.innerHTML = notices.map((n, i) =>
        `<span class="ticker__item"><strong>${escapeHtml(n.title)}</strong>` +
        (n.summary ? ` — ${escapeHtml(n.summary)}` : '') +
        `</span>` + (i < notices.length - 1 ? `<span class="dot">●</span>` : '')
      ).join('');
    } catch (e) {
      console.warn('[ticker] fallback', e);
      track.textContent = 'सूचना लोड गर्न सकिएन।';
    }
  }

  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else { init(); }
})();
