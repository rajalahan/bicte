/* ============================================================
 * token.js – digital token / queue issuer
 *  - Lists all departments as touch buttons
 *  - Calls API.issueToken(); falls back to client-side counter via
 *    localStorage so the kiosk works even when offline
 *  - Reads ?dept=slug to pre-select from a department page
 *  - Calls window.print() on demand for paper printers (optional)
 * ============================================================ */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', async () => {
    const wrap = document.getElementById('dept-buttons');
    const list = (await window.API.departments()) || [];
    wrap.innerHTML = list.map(d =>
      `<button data-slug="${d.slug}" data-name="${escape(d.name)}">${escape(d.name)}</button>`).join('');

    wrap.addEventListener('click', e => {
      const btn = e.target.closest('button[data-slug]');
      if (!btn) return;
      [...wrap.children].forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      issue(btn.dataset.slug, btn.dataset.name);
    });

    // Pre-select from URL
    const slug = new URLSearchParams(location.search).get('dept');
    if (slug) {
      const btn = wrap.querySelector(`button[data-slug="${slug}"]`);
      if (btn) btn.click();
    }
  });

  async function issue(slug, name) {
    const display = document.getElementById('ticket-display');
    display.innerHTML = '<p class="muted">टोकन तयार हुँदैछ …</p>';
    let ticket;
    try {
      const r = await window.API.issueToken({ department: slug });
      ticket = r && (r.data || r);
    } catch (_) {
      ticket = offlineTicket(slug);
    }
    if (!ticket) ticket = offlineTicket(slug);

    const today = new Date();
    display.innerHTML = `
      <div class="muted">शाखा</div>
      <h3 style="color:var(--np-blue-700)">${escape(name)}</h3>
      <div class="ticket-no">${escape(ticket.code || ticket.number || '—')}</div>
      <div class="muted">कृपया रिसेप्सन/शाखामा यो नम्बर देखाउनुहोस्।</div>
      <div class="meta-grid">
        <div><strong>मिति</strong><br>${today.toLocaleDateString()}</div>
        <div><strong>समय</strong><br>${today.toLocaleTimeString()}</div>
        <div><strong>अनुमानित प्रतीक्षा</strong><br>${ticket.eta || '१०–१५ मिनेट'}</div>
        <div><strong>अघिल्तिर</strong><br>${ticket.ahead != null ? ticket.ahead : '—'}</div>
      </div>
      <div style="display:flex;gap:10px;justify-content:center;margin-top:20px;flex-wrap:wrap">
        <button class="btn-kiosk btn-kiosk--primary" onclick="window.print()"><i class="bi bi-printer-fill"></i> प्रिन्ट</button>
        <a class="btn-kiosk btn-kiosk--ghost" href="department.html?slug=${encodeURIComponent(slug)}"><i class="bi bi-info-circle"></i> शाखा विवरण</a>
        <a class="btn-kiosk btn-kiosk--ghost" href="index.html"><i class="bi bi-house-door-fill"></i> मुख्य पृष्ठ</a>
      </div>
    `;
  }

  function offlineTicket(slug) {
    const key = 'lhn.tok.' + slug + '.' + new Date().toISOString().slice(0,10);
    let n = parseInt(localStorage.getItem(key) || '0', 10) + 1;
    localStorage.setItem(key, String(n));
    const prefix = (slug || 'GEN').slice(0, 3).toUpperCase();
    return { code: `${prefix}-${String(n).padStart(3,'0')}`, ahead: n - 1, eta: `${(n-1) * 5}–${n * 5} मिनेट` };
  }

  function escape(s){return String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
})();
