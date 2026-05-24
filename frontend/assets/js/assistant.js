/* ============================================================
 * assistant.js – AI citizen assistant intent engine
 *
 *  - Tries the backend (/assistant/ask) first for full LLM answers.
 *  - Falls back to a local Nepali keyword/intent matcher over faq.json.
 *  - Also fuzzy-matches against department names, services and required
 *    documents from departments.json so any service question routes
 *    to the right desk.
 *  - Renders a chat UI with typing indicator.
 *  - Web Speech API (ne-NP) hooks for voice input.
 *  - Reads ?q=... from URL to allow deep-linking from the voice button
 *    or from the homepage hero.
 * ============================================================ */
(function () {
  'use strict';

  let FAQ = [];
  let DEPTS = [];

  // ---------- Boot ----------
  async function boot() {
    [FAQ, DEPTS] = await Promise.all([window.API.faq(), window.API.departments()]);
    FAQ = FAQ || []; DEPTS = DEPTS || [];

    document.getElementById('composer').addEventListener('submit', onSubmit);
    document.querySelectorAll('#suggested button').forEach(b =>
      b.addEventListener('click', () => ask(b.dataset.q || b.textContent)));

    document.getElementById('btn-mic').addEventListener('click', startMic);

    // URL ?q=...
    const q = new URLSearchParams(location.search).get('q');
    if (q) ask(q);
  }

  function onSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('q');
    const q = input.value.trim();
    if (!q) return;
    input.value = '';
    ask(q);
  }

  // ---------- Ask pipeline ----------
  async function ask(q) {
    appendUser(q);
    const typing = appendTyping();

    let answerHtml = null;
    // 1. backend
    try {
      const r = await window.API.askAssistant(q);
      if (r && (r.answer || r.answer_html)) {
        answerHtml = r.answer_html || escapeHtml(r.answer).replace(/\n/g, '<br>');
      }
    } catch (_) { /* fall through */ }

    // 2. local intent
    if (!answerHtml) answerHtml = localAnswer(q);

    typing.remove();
    appendBot(answerHtml);
    speak(stripHtml(answerHtml));
  }

  // ---------- Local intent matcher ----------
  function localAnswer(qRaw) {
    const q = normalize(qRaw);

    // Score every FAQ entry
    let best = null, bestScore = 0;
    for (const item of FAQ) {
      let score = 0;
      const kws = (item.keywords || []).concat([item.intent || '', item.question || '']);
      for (const k of kws) {
        const kk = normalize(k);
        if (!kk) continue;
        if (q.includes(kk)) score += kk.length;        // substring
        else if (containsAnyToken(q, kk)) score += 1;  // token
      }
      if (score > bestScore) { bestScore = score; best = item; }
    }

    if (best && bestScore >= 2) {
      const dept = DEPTS.find(d => d.slug === best.department);
      const tail = dept
        ? `<div class="msg__hint">सम्बन्धित शाखा: <a href="department.html?slug=${dept.slug}">${escapeHtml(dept.name)}</a></div>`
        : '';
      return (best.answer_ne || best.answer_en || '').replace(/\n/g, '<br>') + tail;
    }

    // Fallback: search department names / services
    const hits = [];
    for (const d of DEPTS) {
      const hay = normalize([d.name, d.name_en, ...(d.services || []), ...(d.required_documents || [])].join(' '));
      if (containsAnyToken(hay, q)) hits.push(d);
    }
    if (hits.length) {
      return 'सम्बन्धित हुन सक्ने शाखाहरू:<br><ul>' +
        hits.slice(0, 5).map(d => `<li><a href="department.html?slug=${d.slug}">${escapeHtml(d.name)}</a> — ${escapeHtml(d.summary || '')}</li>`).join('') +
        '</ul><div class="msg__hint">कुनै शाखा छनौट गरेर पूरा जानकारी हेर्नुहोस्।</div>';
    }

    return 'माफ गर्नुहोस्, यो प्रश्नको जवाफ अहिले मसँग छैन। 🙇<br>' +
           'कृपया अरू शब्दमा सोध्नुहोस् वा <a href="index.html">मुख्य पृष्ठ</a>बाट सम्बन्धित शाखा छनौट गर्नुहोस्।';
  }

  function normalize(s) {
    return (s || '')
      .toString()
      .toLowerCase()
      .replace(/[।,.?!\-_/()]+/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  }
  function containsAnyToken(haystack, needle) {
    return needle.split(' ').filter(t => t.length >= 2).some(t => haystack.includes(t));
  }

  // ---------- DOM helpers ----------
  function appendUser(text) {
    const chat = document.getElementById('chat');
    const div = document.createElement('div');
    div.className = 'msg msg--user fade-in';
    div.textContent = text;
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
  }
  function appendBot(html) {
    const chat = document.getElementById('chat');
    const div = document.createElement('div');
    div.className = 'msg msg--bot fade-in';
    div.innerHTML = html;
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
  }
  function appendTyping() {
    const chat = document.getElementById('chat');
    const div = document.createElement('div');
    div.className = 'msg msg--bot';
    div.innerHTML = '<div class="typing"><span></span><span></span><span></span></div>';
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
    return div;
  }
  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }
  function stripHtml(html) {
    const tmp = document.createElement('div'); tmp.innerHTML = html;
    return (tmp.textContent || tmp.innerText || '').slice(0, 400);
  }

  // ---------- Voice ----------
  function startMic() {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) { alert('यस यन्त्रमा आवाज पहिचान उपलब्ध छैन।'); return; }
    const rec = new SR();
    rec.lang = 'ne-NP';
    rec.onresult = (e) => {
      const t = e.results[0][0].transcript;
      document.getElementById('q').value = t;
      ask(t);
    };
    rec.onerror = () => { /* ignore */ };
    try { rec.start(); } catch (_) {}
  }

  function speak(text) {
    if (!('speechSynthesis' in window)) return;
    try {
      const u = new SpeechSynthesisUtterance(text);
      u.lang = 'ne-NP';
      u.rate = 0.95;
      window.speechSynthesis.cancel();
      window.speechSynthesis.speak(u);
    } catch (_) {}
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
