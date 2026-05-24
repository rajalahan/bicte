# Lahan Municipality – Smart Information Board System

A modern, AI‑integrated, touch‑first digital information kiosk for **Lahan Nagarpalika** (Siraha, Province 2, Nepal), designed for a 43‑inch touchscreen kiosk in the central municipality office.

The system delivers municipal service information to citizens in formal Nepali, with an AI assistant, department‑wise pages, dynamic notice management, citizen feedback, digital token issuance, and a secure admin panel.

> Project codename: **bicte** (Better Information & Citizen Touch‑point for E‑governance)

---

## Highlights

- **Kiosk‑first UI** – 43" touchscreen, Chrome kiosk mode, large touch targets, full Nepali (Noto Sans Devanagari).
- **Government colour scheme** – Nepal flag‑inspired blue / white / red.
- **Real‑time clock + Nepali (Bikram Sambat) and English (AD) date**.
- **Scrolling notice ticker** powered by the dynamic notice CMS.
- **19 department pages** with services, required documents, fees, process, room/floor, contact, charter, downloadable forms and QR.
- **AI Citizen Assistant** – Nepali‑first FAQ + intent matching, voice‑ready hooks, search.
- **Digital token / queue system**, citizen feedback, public complaint, achievements slider, festival greetings, weather widget.
- **Laravel 11 + MySQL backend** with REST API, admin panel and role‑based auth.
- **Offline‑first frontend** – static JSON fallback so the kiosk keeps working if the LAN goes down.
- **Production‑ready** – Nginx + systemd + Chrome kiosk + autostart guides included.

---

## Repository layout

```
bicte/
├── README.md
├── docs/
│   ├── INSTALLATION.md          # step-by-step install
│   ├── DEPLOYMENT.md            # production deploy (Linux/Windows)
│   ├── KIOSK_SETUP.md           # Chrome kiosk + Windows autostart
│   └── ARCHITECTURE.md          # system design, data flow, scaling
│
├── frontend/                    # Static kiosk UI (HTML5 + Bootstrap 5 + vanilla JS)
│   ├── index.html               # Homepage
│   ├── department.html          # Generic department page (data-driven)
│   ├── assistant.html           # AI citizen assistant
│   ├── notices.html             # Notice board
│   ├── feedback.html            # Citizen feedback / complaint
│   ├── token.html               # Digital token / queue
│   ├── directory.html           # Office directory + smart map
│   └── assets/
│       ├── css/
│       │   ├── main.css         # design tokens, layout, components
│       │   ├── kiosk.css        # 43" kiosk overrides
│       │   └── department.css
│       ├── js/
│       │   ├── app.js           # bootstraps every page
│       │   ├── clock.js         # real-time clock
│       │   ├── nepali-date.js   # AD ⇄ BS conversion
│       │   ├── ticker.js        # scrolling notice ticker
│       │   ├── i18n.js          # Nepali strings & helpers
│       │   ├── department.js    # renders department.html from JSON
│       │   ├── assistant.js     # AI assistant intent engine
│       │   ├── token.js
│       │   ├── feedback.js
│       │   └── api.js           # talks to Laravel API w/ offline fallback
│       ├── img/                 # logos, mayor photos, dept icons
│       └── data/                # offline/static fallback JSON
│           ├── officials.json
│           ├── departments.json
│           ├── notices.json
│           ├── faq.json
│           └── achievements.json
│
├── backend/                     # Laravel 11 API + admin
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Http/Controllers/Admin/
│   │   └── Models/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── schema.sql           # raw SQL schema (vendor-neutral reference)
│   ├── resources/views/admin/   # Blade admin panel
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   ├── config/
│   ├── composer.json
│   └── .env.example
│
└── scripts/
    ├── kiosk-launch.sh          # Linux launcher
    ├── kiosk-launch.bat         # Windows launcher
    └── install.sh               # one-shot dev bootstrap
```

---

## Tech stack

| Layer        | Choice                                            |
| ------------ | ------------------------------------------------- |
| Frontend     | HTML5, CSS3, Bootstrap 5, vanilla ES2020 modules  |
| Fonts        | Noto Sans Devanagari, Mukta, Inter                |
| Backend      | **Laravel 11** (PHP 8.2+)                         |
| Database     | **MySQL 8** (MariaDB 10.6+ compatible)            |
| Admin auth   | Laravel Breeze + Spatie permissions               |
| AI assistant | Local intent engine + pluggable LLM/RAG endpoint  |
| Cache        | File / Redis (optional)                           |
| Web server   | Nginx + PHP‑FPM (Linux) or IIS + PHP (Windows)    |
| Kiosk        | Google Chrome `--kiosk` on Windows 10/11          |

---

## Quick start (developer)

```bash
# 1. Backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve            # http://127.0.0.1:8000

# 2. Frontend (static – any HTTP server works)
cd ../frontend
python3 -m http.server 5173  # http://127.0.0.1:5173
```

The frontend auto‑detects the API at `http://127.0.0.1:8000/api` and falls back to `assets/data/*.json` when offline.

For full installation and Windows kiosk autostart, see [`docs/INSTALLATION.md`](docs/INSTALLATION.md) and [`docs/KIOSK_SETUP.md`](docs/KIOSK_SETUP.md).

To deploy on a shared cPanel host (eHosting Nepal, Hostinger, Namecheap, etc.) see [`docs/CPANEL_DEPLOYMENT.md`](docs/CPANEL_DEPLOYMENT.md). One zip + 9 numbered steps + admin login.

---

## Departments covered

योजना शाखा · सूचना प्रविधि शाखा · सरसफाइ शाखा · स्वास्थ्य शाखा · शिक्षा शाखा · WASH Unit · भवन निर्माण तथा नक्सा पास शाखा · आन्तरिक राजस्व शाखा · महिला तथा बालबालिका शाखा · रोजगार सेवा केन्द्र · पञ्जीकरण शाखा · नगर प्रहरी · कृषि विकास शाखा · पशु विकास शाखा · प्रशासन शाखा · सामाजिक सुरक्षा शाखा · विपद व्यवस्थापन शाखा · न्यायिक समिति · वडा कार्यालय जानकारी

---

## License

© Lahan Nagarpalika. Internal e‑governance project. All third‑party assets retain their original licenses.

Public information is sourced from the official municipality website [lahanmun.gov.np](https://lahanmun.gov.np/) and should be verified by the IT Section before publication on the kiosk.
