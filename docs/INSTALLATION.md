# Installation Guide

> Audience: IT Section, Lahan Nagarpalika.
> Goal: bring up the kiosk system on a single server (LAN) and a single touchscreen client.

## 1. Prerequisites

| Component         | Version                  | Notes                              |
| ----------------- | ------------------------ | ---------------------------------- |
| OS (server)       | Ubuntu 22.04 LTS         | or Windows Server 2019/2022        |
| OS (kiosk client) | Windows 10/11 (64-bit)   | dedicated touchscreen PC           |
| PHP               | 8.2+                     | with `mbstring intl pdo_mysql gd zip curl xml openssl tokenizer fileinfo bcmath` |
| Composer          | 2.6+                     |                                    |
| MySQL             | 8.0+ (or MariaDB 10.6+)  | utf8mb4                            |
| Node.js           | 20 LTS                   | only if you customise frontend     |
| Web server        | Nginx or Apache (Linux), IIS (Windows) | |
| Browser           | Google Chrome / Edge 120+| kiosk mode                         |

## 2. Clone the repository

```bash
git clone https://github.com/rajalahan/bicte.git /opt/bicte
cd /opt/bicte
```

## 3. Backend (Laravel 11)

```bash
cd backend

# 3.1 Dependencies
composer install --no-dev --optimize-autoloader

# 3.2 Environment
cp .env.example .env
php artisan key:generate

# 3.3 Database
mysql -u root -p -e "CREATE DATABASE bicte CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# Edit DB_USERNAME / DB_PASSWORD in .env, then:

php artisan migrate --seed
# → seeds 19 departments, FAQ, notices, achievements and creates
#   default admin: admin@lahanmun.gov.np / changeme  (CHANGE IT)

# 3.4 Storage / cache
php artisan storage:link
php artisan optimize
```

Test the API:
```bash
php artisan serve --host=0.0.0.0 --port=8000
curl http://127.0.0.1:8000/api/health
curl http://127.0.0.1:8000/api/departments | head
```

Change the admin password immediately:

```bash
php artisan tinker
>>> \App\Models\User::where('email','admin@lahanmun.gov.np')->first()->update(['password'=>\Hash::make('a-strong-password')]);
```

## 4. Frontend (static)

The kiosk frontend is plain HTML/CSS/JS – no build step required.

### Option A – served by the Laravel app (single origin)

Symlink (or copy) the `frontend/` folder into Laravel's `public/`:

```bash
ln -s /opt/bicte/frontend /opt/bicte/backend/public/kiosk
# now reachable at http://server/kiosk/index.html
```

### Option B – separate origin (recommended for kiosk)

Serve `frontend/` from Nginx as a static site. Add a meta tag in each HTML file (or modify `assets/js/api.js`) so the frontend knows the API base URL:

```html
<meta name="api-base" content="https://api.lahanmun.gov.np/api">
```

## 5. Verify

Open `http://<server>/kiosk/index.html` in any browser:

- [x] Top bar shows logo, scrolling notices, live clock and BS/AD date
- [x] 19 department tiles render
- [x] Click any tile → department page loads with services/docs/fees/QR
- [x] AI assistant answers “जन्मदर्ता कहाँ हुन्छ?”
- [x] Token page issues a code (offline counter is fine if API down)
- [x] `http://<server>/admin` → login → dashboard

## 6. Next steps

- [`KIOSK_SETUP.md`](KIOSK_SETUP.md) — Windows Chrome kiosk autostart
- [`DEPLOYMENT.md`](DEPLOYMENT.md) — production hardening (TLS, backups, supervisord)
- [`ARCHITECTURE.md`](ARCHITECTURE.md) — how the pieces fit together
