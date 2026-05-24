/* ============================================================
 * clock.js – live clock + Nepali/English date renderer
 * Looks for #clock-time, #date-bs, #date-ad in the DOM.
 * ============================================================ */
(function () {
  'use strict';

  function pad(n) { return n < 10 ? '0' + n : '' + n; }

  function tick() {
    const now    = new Date();
    const time   = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    const bs     = window.NepaliDate.fromAD(now);
    const wd     = now.getDay();
    const bsTxt  = window.NepaliDate.formatNepali(bs, wd);
    const adTxt  = window.NepaliDate.formatEnglish(now);

    setText('clock-time', window.I18N.lang === 'ne' ? window.I18N.toNepaliNumeral(time) : time);
    setText('date-bs',   bsTxt);
    setText('date-ad',   adTxt + ' AD');
  }

  function setText(id, value) {
    const el = document.getElementById(id);
    if (el && el.textContent !== value) el.textContent = value;
  }

  function start() {
    if (!document.getElementById('clock-time')) return;
    tick();
    setInterval(tick, 1000);
    document.addEventListener('i18n:change', tick);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
