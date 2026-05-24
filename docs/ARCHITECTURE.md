# Architecture

## 1. High-level diagram

```
                      ┌────────────────────────────────────────────┐
                      │          43" TOUCHSCREEN KIOSK PC           │
                      │                                              │
                      │   Google Chrome (kiosk mode, fullscreen)    │
                      │   ┌──────────────────────────────────────┐  │
                      │   │  Static frontend  (HTML5 / Bootstrap │  │
                      │   │   5 / vanilla JS)                    │  │
                      │   │   - index.html  (homepage)           │  │
                      │   │   - department.html (data-driven)    │  │
                      │   │   - assistant.html (AI chat)         │  │
                      │   │   - notices/feedback/token/directory │  │
                      │   └──────────────────────────────────────┘  │
                      │                  │                           │
                      │                  ▼ (fetch JSON)              │
                      │            assets/data/*.json  ◀── offline  │
                      └─────────────────┼─────────────┬──────────────┘
                                        │ REST        │
                                        ▼             ▼
                ┌──────────────────────────────┐  ┌─────────────────────────────┐
                │      Laravel 11 backend      │  │  Open-Meteo (weather)       │
                │  ─────────────────────────   │  │  api.qrserver.com (QR)      │
                │  /api/officials              │  └─────────────────────────────┘
                │  /api/departments            │
                │  /api/notices                │
                │  /api/faq                    │
                │  /api/achievements           │
                │  /api/feedback   (POST)      │
                │  /api/tokens     (POST)      │
                │  /api/assistant/ask (POST)   │
                │                              │
                │  /admin (Blade)              │ ◀── IT Section / dept editors
                │     - dashboard              │
                │     - departments / notices  │
                │     - feedback / tokens      │
                └────────────┬─────────────────┘
                             │
                             ▼
                ┌──────────────────────────────┐
                │       MySQL 8 database       │
                │   utf8mb4 / unicode_ci       │
                └──────────────────────────────┘
                             │
                             ▼ (optional)
                ┌──────────────────────────────┐
                │  AI provider (OpenAI/Ollama) │
                │  + SMS gateway, mail server  │
                └──────────────────────────────┘
```

## 2. Why this shape?

- **Static-first frontend.** The kiosk must keep working even if the LAN switch is rebooted. Every page bundles a JSON snapshot of departments, notices, FAQ and achievements; the API is preferred when reachable, the bundled JSON is the safety net.
- **Laravel for everything else.** A single, well-known PHP stack runs the REST API, the admin panel, the role/permission layer (Spatie) and the queue worker. Easy to host on the municipality's existing LAMP server.
- **MySQL.** The municipality's preferred RDBMS, plays nicely with Devanagari `utf8mb4_unicode_ci`.
- **Pluggable AI.** The `AssistantController` answers from the local `faqs` table by default. If `ASSISTANT_PROVIDER=openai|ollama` is configured, it adds an LLM layer that uses the local match as retrieval context (RAG-lite).
- **No build pipeline on the kiosk.** Plain HTML/CSS/JS means anyone with a text editor can hot-fix on site; no Node, no transpiler, no service worker complexity.

## 3. Data flow examples

### a) Citizen taps "नक्सा पास शाखा"
1. Browser navigates to `department.html?slug=building-permit`.
2. `department.js` calls `API.department('building-permit')`.
3. `api.js` tries `GET /api/departments/building-permit` (4 s timeout).
4. On success → DB-backed answer is rendered.
5. On failure → falls back to the bundled `assets/data/departments.json`, the kiosk never shows a blank page.

### b) Citizen asks the AI assistant "जन्मदर्ता कहाँ हुन्छ?"
1. `assistant.js` POSTs to `/api/assistant/ask`.
2. `AssistantController` runs the local intent matcher against `faqs` (substring + token scoring on Nepali keywords).
3. If `ASSISTANT_PROVIDER` is set, the best local match becomes context for an LLM call (OpenAI compatible or Ollama). Cached for 1 h to limit cost.
4. Response: `{ answer, answer_html, source }`.
5. The frontend renders the HTML answer and (best-effort) speaks it via `speechSynthesis` (`ne-NP`).

### c) Citizen submits a complaint
1. POST `/api/feedback` (rate-limited by IP).
2. `FeedbackController` stores the row with a generated ticket `LHN-YYYYMMDD-NNNN`.
3. Frontend shows the ticket.
4. If the API is unreachable, `feedback.js` queues the payload in `localStorage` and keeps working. The next online visit can flush this queue (TODO: add a small sync routine).

## 4. Security

- All write endpoints require Sanctum auth + `role:admin|editor` (Spatie Permission).
- Public POST endpoints (`/feedback`, `/tokens`, `/assistant/ask`) are rate-limited.
- Admin Blade panel uses Laravel's session auth + CSRF tokens.
- DB stores only what the citizen submitted; the kiosk does not log who pressed which button.
- `.env` and `storage/` are never committed.

## 5. Scalability

The single-server setup easily handles a 43" kiosk. To grow:

- **Horizontal frontend:** put `frontend/` on a CDN/edge cache. Each kiosk is just a Chrome window.
- **Read replicas:** all kiosk requests are GETs; point them at a MySQL replica.
- **Queue-driven SMS:** keep the queue worker on the API host; scale to a separate worker box if SMS volumes grow.
- **Multi-municipality:** namespace data by `municipality_id` (foreign key on every table). The kiosk passes a `?mun=lahan` query string and the API filters accordingly.

## 6. Localisation

- Default UI language: **ne** (Nepali). `?lang=en` or the top-bar toggle switches strings in `assets/js/i18n.js`.
- Numerals: `I18N.toNepaliNumeral()` is used wherever digits are user-visible.
- Dates: `nepali-date.js` converts Gregorian to BS using a 2070→2099 days-per-month table sourced from a verified Patro almanac.
- Fonts: Noto Sans Devanagari + Mukta from Google Fonts CDN (cached locally for fully air-gapped kiosks – see KIOSK_SETUP §3 allowlist).

## 7. Extending

| You want to…                    | Touch this                                                          |
| ------------------------------- | ------------------------------------------------------------------- |
| Add a new department            | Admin → शाखा → "नयाँ शाखा"  *or*  edit `frontend/assets/data/departments.json` and re-run `php artisan db:seed --class=DepartmentSeeder` |
| Add a new FAQ intent            | Admin (TODO: add FAQ CRUD UI) or edit `faq.json` + `php artisan db:seed --class=FaqSeeder` |
| Wire a real LLM                 | Set `ASSISTANT_PROVIDER`, `ASSISTANT_API_KEY`, `ASSISTANT_ENDPOINT`, `ASSISTANT_MODEL` in `.env` |
| Wire SMS notifications          | Set `SMS_PROVIDER`/`SMS_API_KEY`, then dispatch a job from `FeedbackController@store` |
| Replace the floor map           | Edit the inline SVG in `directory.html` or swap in a real GIS tile  |
| Add a second kiosk              | Run another Chrome client pointed at the same API. No code change.  |
