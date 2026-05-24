/* ============================================================
 * nepali-date.js – AD ⇄ BS converter for the kiosk clock
 *
 * Days-per-month table: BS 2070 (≈ AD 2013) → BS 2099 (≈ AD 2042)
 * (covers the full kiosk lifetime). If you extend the kiosk life,
 * append rows from a verified Patro/Almanac source.
 *
 * Algorithm: count days from a known anchor (BS 2070-01-01 ↔ AD 2013-04-14)
 * and walk forwards or backwards.
 * Exposes:  window.NepaliDate
 * ============================================================ */
(function (global) {
  'use strict';

  // 12 columns per row = days in each Nepali month for that year.
  const BS_DAYS = {
    2070: [31,31,32,32,31,31,30,30,29,30,29,31],
    2071: [31,31,32,31,31,31,30,30,29,30,29,31],
    2072: [31,32,31,32,31,31,30,30,29,30,29,31],
    2073: [31,31,32,32,31,30,30,30,29,30,29,31],
    2074: [31,31,32,32,31,31,30,30,29,30,29,31],
    2075: [31,32,31,32,31,31,30,30,29,30,29,31],
    2076: [31,31,32,32,31,30,30,30,29,30,29,31],
    2077: [31,31,32,31,31,31,30,30,29,30,29,31],
    2078: [31,32,31,32,31,31,30,30,29,30,29,30],
    2079: [31,31,32,32,31,30,30,30,29,30,29,31],
    2080: [31,32,31,32,31,30,30,30,29,30,29,31],
    2081: [31,31,32,31,31,31,30,30,29,30,29,31],
    2082: [31,32,31,32,31,31,30,30,29,30,29,30],
    2083: [31,31,32,32,31,30,30,30,29,30,29,31],
    2084: [31,32,31,32,31,30,30,30,29,30,29,31],
    2085: [31,31,32,31,31,31,30,30,29,30,29,31],
    2086: [31,32,31,32,31,31,30,30,29,30,29,30],
    2087: [31,31,32,32,31,30,30,30,29,30,29,31],
    2088: [30,31,32,32,31,30,30,30,29,30,29,31],
    2089: [30,31,32,31,31,31,30,30,29,30,29,31],
    2090: [30,31,32,32,31,31,30,30,29,30,29,30],
    2091: [31,31,32,32,31,30,30,30,29,30,29,31],
    2092: [30,31,32,32,32,30,30,30,29,30,29,31],
    2093: [30,31,32,31,31,31,30,30,29,30,29,31],
    2094: [31,31,32,32,31,31,30,30,29,30,29,30],
    2095: [31,31,32,32,31,30,30,30,29,30,29,31],
    2096: [30,31,32,32,31,30,30,30,29,30,29,31],
    2097: [31,32,31,32,31,31,30,30,29,30,29,31],
    2098: [31,31,32,32,31,31,30,30,29,30,29,30],
    2099: [31,31,32,32,31,30,30,30,29,30,30,30],
  };

  // Anchor: BS 2070-01-01 == AD 2013-04-14 (Sunday)
  const ANCHOR_BS = { y: 2070, m: 1, d: 1 };
  const ANCHOR_AD = new Date(Date.UTC(2013, 3, 14)); // month index 3 == April

  const MS_PER_DAY = 86400000;

  function daysInBSYear(y) {
    const row = BS_DAYS[y];
    return row ? row.reduce((a, b) => a + b, 0) : 365;
  }

  /** Convert a JS Date (any time zone, treated as Asia/Kathmandu local date) → {y,m,d} BS. */
  function fromAD(date) {
    // Use local Y/M/D so the kiosk shows the user's wall-clock date.
    const local = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const anchorLocal = new Date(2013, 3, 14);
    let diff = Math.round((local - anchorLocal) / MS_PER_DAY);

    let y = ANCHOR_BS.y, m = ANCHOR_BS.m, d = ANCHOR_BS.d;

    if (diff >= 0) {
      while (diff > 0) {
        const dim = (BS_DAYS[y] || [30,30,30,30,30,30,30,30,30,30,30,30])[m - 1];
        if (d + diff <= dim) { d += diff; diff = 0; }
        else { diff -= (dim - d + 1); d = 1; m++; if (m > 12) { m = 1; y++; } }
      }
    } else {
      diff = -diff;
      while (diff > 0) {
        if (d - diff >= 1) { d -= diff; diff = 0; }
        else { diff -= d; m--; if (m < 1) { m = 12; y--; } d = (BS_DAYS[y] || [30,30,30,30,30,30,30,30,30,30,30,30])[m - 1]; }
      }
    }
    return { y, m, d };
  }

  /** Convert BS {y,m,d} → AD JS Date (local midnight). */
  function toAD(bs) {
    let total = 0;
    if (bs.y >= ANCHOR_BS.y) {
      for (let y = ANCHOR_BS.y; y < bs.y; y++) total += daysInBSYear(y);
      for (let m = 1; m < bs.m; m++) total += (BS_DAYS[bs.y] || [])[m - 1] || 30;
      total += (bs.d - 1);
    } else {
      for (let y = bs.y; y < ANCHOR_BS.y; y++) total -= daysInBSYear(y);
      for (let m = 1; m < bs.m; m++) total += (BS_DAYS[bs.y] || [])[m - 1] || 30;
      total += (bs.d - 1);
    }
    const out = new Date(2013, 3, 14);
    out.setDate(out.getDate() + total);
    return out;
  }

  /** Format a BS object in Nepali, e.g. "२०८१ मंसिर १२ गते, बुधबार". */
  function formatNepali(bs, weekdayIndex) {
    const I18N = global.I18N;
    const ne = I18N.toNepaliNumeral;
    const month = I18N.NE_MONTHS[bs.m - 1] || '';
    const wd = (weekdayIndex != null) ? I18N.NE_WEEKDAYS[weekdayIndex] : '';
    return `${ne(bs.y)} ${month} ${ne(bs.d)} गते${wd ? ', ' + wd : ''}`;
  }
  /** Format a JS Date in English short form, e.g. "24 May 2026". */
  function formatEnglish(d) {
    const I18N = global.I18N;
    return `${d.getDate()} ${I18N.EN_MONTHS[d.getMonth()]} ${d.getFullYear()}`;
  }

  global.NepaliDate = { BS_DAYS, fromAD, toAD, formatNepali, formatEnglish };
})(window);
