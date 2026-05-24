/* ============================================================
 * i18n.js – Nepali / English helpers and strings
 * Exposes:  window.I18N
 * ============================================================ */
(function (global) {
  'use strict';

  const NE_DIGITS = ['०','१','२','३','४','५','६','७','८','९'];
  const EN_DIGITS = ['0','1','2','3','4','5','6','7','8','9'];

  const NE_MONTHS = [
    'बैशाख','जेठ','असार','साउन','भदौ','असोज',
    'कार्तिक','मंसिर','पुष','माघ','फागुन','चैत'
  ];
  const NE_WEEKDAYS = ['आइतबार','सोमबार','मङ्गलबार','बुधबार','बिहीबार','शुक्रबार','शनिबार'];
  const EN_MONTHS = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December'
  ];

  const STRINGS = {
    ne: {
      welcome:        'नमस्कार',
      home:           'मुख्य पृष्ठ',
      assistant:      'AI सहायक',
      notices:        'सूचना',
      token:          'टोकन',
      directions:     'दिशा-निर्देश',
      feedback:       'गुनासो',
      voice:          'आवाज',
      services:       'उपलब्ध सेवाहरू',
      documents:      'आवश्यक कागजातहरू',
      process:        'सेवा प्रक्रिया',
      fees:           'सेवा शुल्क',
      timings:        'कार्यालय समय',
      contact:        'सम्पर्क व्यक्ति',
      room:           'कोठा / तला',
      forms:          'डाउनलोड फारम',
      charter:        'नागरिक बडापत्र',
      backHome:       'मुख्य पृष्ठमा फर्कनुहोस्',
    },
    en: {
      welcome:        'Welcome',
      home:           'Home',
      assistant:      'AI Assistant',
      notices:        'Notices',
      token:          'Token',
      directions:     'Directory',
      feedback:       'Feedback',
      voice:          'Voice',
      services:       'Available services',
      documents:      'Required documents',
      process:        'Service process',
      fees:           'Service fees',
      timings:        'Office timings',
      contact:        'Contact person',
      room:           'Room / Floor',
      forms:          'Downloadable forms',
      charter:        "Citizen's charter",
      backHome:       'Back to home',
    }
  };

  /** Convert any string/number containing 0-9 digits to Devanagari ०-९. */
  function toNepaliNumeral(input) {
    return String(input).replace(/[0-9]/g, d => NE_DIGITS[+d]);
  }
  /** Convert Devanagari ०-९ digits in a string back to ASCII. */
  function toEnglishNumeral(input) {
    return String(input).replace(/[०-९]/g, d => EN_DIGITS[NE_DIGITS.indexOf(d)]);
  }

  /** Get a localized string by key. */
  function t(key) {
    const lang = global.I18N.lang || 'ne';
    return (STRINGS[lang] && STRINGS[lang][key]) || STRINGS.ne[key] || key;
  }

  /** Toggle ne/en, persists in localStorage, dispatches `i18n:change`. */
  function setLang(lang) {
    if (!STRINGS[lang]) return;
    global.I18N.lang = lang;
    try { localStorage.setItem('lhn.lang', lang); } catch (_) {}
    document.documentElement.setAttribute('lang', lang);
    document.dispatchEvent(new CustomEvent('i18n:change', { detail: { lang } }));
  }
  function toggleLang() { setLang(global.I18N.lang === 'ne' ? 'en' : 'ne'); }

  global.I18N = {
    lang: (function () {
      try { return localStorage.getItem('lhn.lang') || 'ne'; } catch (_) { return 'ne'; }
    })(),
    NE_DIGITS, EN_DIGITS, NE_MONTHS, NE_WEEKDAYS, EN_MONTHS,
    t, setLang, toggleLang,
    toNepaliNumeral, toEnglishNumeral,
  };
})(window);
