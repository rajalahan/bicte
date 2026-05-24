/* ============================================================
 * department.js – renders the department grid (homepage) and the
 * full department detail page (department.html?slug=...).
 * ============================================================ */
(function () {
  'use strict';

  // Bootstrap-icon glyphs for known department slugs.
  const DEPT_ICONS = {
    'planning':            'bi-graph-up-arrow',
    'it':                  'bi-cpu',
    'sanitation':          'bi-trash3',
    'health':              'bi-heart-pulse',
    'education':           'bi-mortarboard',
    'wash':                'bi-droplet-half',
    'building-permit':     'bi-building-gear',
    'revenue':             'bi-coin',
    'women-children':      'bi-people',
    'employment':          'bi-briefcase',
    'registration':        'bi-journal-text',
    'town-police':         'bi-shield-check',
    'agriculture':         'bi-tree',
    'livestock':           'bi-piggy-bank',
    'administration':      'bi-bank2',
    'social-security':     'bi-life-preserver',
    'disaster':            'bi-exclamation-triangle',
    'judicial':            'bi-hammer',
    'wards':               'bi-geo-alt',
  };

  function iconFor(slug) { return DEPT_ICONS[slug] || 'bi-building'; }

  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }

  // ---------- Homepage grid ----------
  async function renderGrid() {
    const grid = document.getElementById('dept-grid');
    if (!grid) return;
    grid.innerHTML = Array.from({ length: 12 }).map(() =>
      `<div class="dept-tile skeleton" aria-hidden="true"><div style="height:64px;width:64px;border-radius:16px;background:#e2e8f0"></div><div class="skeleton" style="width:70%;height:18px"></div></div>`
    ).join('');

    const list = await window.API.departments();
    if (!list || !list.length) { grid.innerHTML = '<p>शाखा सूची लोड गर्न सकिएन।</p>'; return; }

    grid.innerHTML = list.map(d => `
      <a class="dept-tile fade-in" href="department.html?slug=${encodeURIComponent(d.slug)}" aria-label="${escapeHtml(d.name)}">
        <div class="dept-tile__icon"><i class="bi ${iconFor(d.slug)}"></i></div>
        <div class="dept-tile__name">${escapeHtml(d.name)}</div>
        <div class="dept-tile__meta">${escapeHtml(d.room || '')}${d.room && d.floor ? ' · ' : ''}${escapeHtml(d.floor || '')}</div>
      </a>
    `).join('');
  }

  // ---------- Detail page ----------
  async function renderDetail() {
    const root = document.getElementById('dept-detail');
    if (!root) return;
    const slug = new URLSearchParams(location.search).get('slug');
    if (!slug) { root.innerHTML = '<p>शाखा छनौट गरिएको छैन।</p>'; return; }

    const d = await window.API.department(slug);
    if (!d) { root.innerHTML = '<p>शाखा भेटिएन।</p>'; return; }

    document.title = d.name + ' · लहान नगरपालिका';
    const t = window.I18N.t;

    root.innerHTML = `
      <header class="dept-hero">
        <div class="dept-hero__icon"><i class="bi ${iconFor(d.slug)}"></i></div>
        <div>
          <div class="dept-hero__eyebrow">शाखा / Section</div>
          <h2 class="dept-hero__title">${escapeHtml(d.name)}</h2>
          ${d.name_en ? `<div class="dept-hero__alt">${escapeHtml(d.name_en)}</div>` : ''}
          ${d.summary ? `<p class="dept-hero__summary">${escapeHtml(d.summary)}</p>` : ''}
        </div>
      </header>

      <div class="dept-grid-2">
        ${block(t('services'),  'bi-list-check',     listOrEmpty(d.services))}
        ${block(t('documents'), 'bi-folder-check',   listOrEmpty(d.required_documents))}
        ${block(t('process'),   'bi-diagram-3',      orderedListOrEmpty(d.process))}
        ${block(t('fees'),      'bi-cash-coin',      feeTable(d.fees))}
        ${block(t('contact'),   'bi-person-badge',   contactBlock(d))}
        ${block(t('charter'),   'bi-file-earmark-text', escapeHtml(d.charter || '—'))}
        ${block(t('forms'),     'bi-download',       formsList(d.forms))}
        ${block('QR',           'bi-qr-code',        qrBlock(d))}
      </div>

      <div class="dept-actions">
        <a class="btn-kiosk btn-kiosk--primary" href="token.html?dept=${encodeURIComponent(d.slug)}"><i class="bi bi-ticket-detailed"></i> टोकन लिनुहोस्</a>
        <a class="btn-kiosk btn-kiosk--ghost" href="assistant.html?q=${encodeURIComponent(d.name)}"><i class="bi bi-robot"></i> AI सहायकलाई सोध्नुहोस्</a>
        <a class="btn-kiosk btn-kiosk--ghost" href="index.html"><i class="bi bi-arrow-left"></i> ${t('backHome')}</a>
      </div>
    `;
  }

  function block(title, icon, body) {
    return `
      <section class="card dept-card fade-in">
        <h3 class="card__title"><i class="bi ${icon}"></i> ${escapeHtml(title)}</h3>
        <div class="dept-card__body">${body}</div>
      </section>
    `;
  }

  function listOrEmpty(arr) {
    if (!arr || !arr.length) return '<p class="muted">—</p>';
    return '<ul class="dept-list">' + arr.map(x => `<li>${escapeHtml(x)}</li>`).join('') + '</ul>';
  }
  function orderedListOrEmpty(arr) {
    if (!arr || !arr.length) return '<p class="muted">—</p>';
    return '<ol class="dept-list dept-list--ordered">' + arr.map(x => `<li>${escapeHtml(x)}</li>`).join('') + '</ol>';
  }
  function feeTable(fees) {
    if (!fees || !fees.length) return '<p class="muted">निःशुल्क / —</p>';
    return `<table class="dept-table">
      <thead><tr><th>सेवा</th><th>शुल्क (रु.)</th></tr></thead>
      <tbody>${fees.map(f => `<tr><td>${escapeHtml(f.service)}</td><td>${escapeHtml(f.amount)}</td></tr>`).join('')}</tbody>
    </table>`;
  }
  function contactBlock(d) {
    return `
      <div class="contact">
        <div><strong>${escapeHtml(d.contact_person || '—')}</strong><br><small>${escapeHtml(d.contact_designation || '')}</small></div>
        <div><i class="bi bi-telephone"></i> ${escapeHtml(d.phone || '—')}</div>
        <div><i class="bi bi-envelope"></i> ${escapeHtml(d.email || '—')}</div>
        <div><i class="bi bi-geo-alt"></i> ${escapeHtml((d.room || '—') + (d.floor ? ' · ' + d.floor : ''))}</div>
        <div><i class="bi bi-clock"></i> ${escapeHtml(d.timings || '—')}</div>
      </div>
    `;
  }
  function formsList(forms) {
    if (!forms || !forms.length) return '<p class="muted">—</p>';
    return '<ul class="dept-list">' + forms.map(f =>
      `<li><a href="${escapeHtml(f.url || '#')}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-arrow-down"></i> ${escapeHtml(f.title)}</a></li>`
    ).join('') + '</ul>';
  }
  function qrBlock(d) {
    const target = (d.public_url || ('https://lahanmun.gov.np/?dept=' + d.slug));
    const qr = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(target);
    return `<div class="qr"><img alt="QR" src="${qr}" onerror="this.replaceWith(Object.assign(document.createElement('div'),{textContent:'QR उपलब्ध छैन'}))"/><div class="qr__url">${escapeHtml(target)}</div></div>`;
  }

  // ---------- Boot ----------
  function start() { renderGrid(); renderDetail(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
