/* ============================================================
 * feedback.js – populate department dropdown + submit handler
 * Falls back to localStorage queue when API unreachable so the
 * citizen never loses their submission.
 * ============================================================ */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', async () => {
    const sel = document.getElementById('dept');
    const depts = (await window.API.departments()) || [];
    sel.innerHTML += depts.map(d => `<option value="${d.slug}">${escape(d.name)}</option>`).join('');

    const form = document.getElementById('feedback-form');
    const ok   = document.getElementById('alert-ok');
    const err  = document.getElementById('alert-err');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      ok.style.display = err.style.display = 'none';

      const fd = new FormData(form);
      const payload = Object.fromEntries(fd.entries());
      payload.submitted_at = new Date().toISOString();

      try {
        const r = await window.API.submitFeedback(payload);
        document.getElementById('ticket-id').textContent = (r && (r.ticket || r.data?.ticket)) || generateTicket();
        ok.style.display = 'block';
        form.reset();
      } catch (e2) {
        // Queue offline so we never lose citizen input
        try {
          const q = JSON.parse(localStorage.getItem('lhn.fb.queue') || '[]');
          payload.ticket = generateTicket();
          q.push(payload);
          localStorage.setItem('lhn.fb.queue', JSON.stringify(q));
          document.getElementById('ticket-id').textContent = payload.ticket + ' (offline)';
          ok.style.display = 'block';
          form.reset();
        } catch (_) {
          err.style.display = 'block';
        }
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  function generateTicket() {
    const d = new Date();
    const r = Math.floor(1000 + Math.random() * 9000);
    return `LHN-${d.getFullYear()}${pad(d.getMonth()+1)}${pad(d.getDate())}-${r}`;
  }
  function pad(n) { return n < 10 ? '0'+n : ''+n; }
  function escape(s){return String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
})();
