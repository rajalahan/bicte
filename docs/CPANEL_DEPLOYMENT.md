# Deploying to cPanel hosting (eHosting Nepal etc.)

A practical step-by-step for shared cPanel hosts. Same pattern works on Hostinger, Namecheap, A2, BigRock, AwebHosting, ThunderHost — anywhere with cPanel + PHP 8.2 + MySQL.

> **Prerequisites on your hosting account:**
> - PHP **8.2** or newer (cPanel → "Select PHP Version" → set 8.2/8.3)
> - MySQL 5.7+ / MariaDB 10.4+
> - At least 200 MB disk space
> - SSL certificate (cPanel → "SSL/TLS" or AutoSSL — use Let's Encrypt, free)

The same Laravel app will host:
- `https://yourdomain.com/admin` → CMS login (edit content here)
- `https://yourdomain.com/api/*` → REST API consumed by the kiosk
- `https://yourdomain.com/kiosk/index.html` → public kiosk frontend

---

## Step 1 — Confirm/upgrade PHP version

1. Log in to cPanel.
2. Open **Select PHP Version** (sometimes called "MultiPHP Manager").
3. Set PHP version to **8.2** (or 8.3) for your domain.
4. Click **Extensions** and tick:
   `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `xmlreader`, `zip`.
5. Save.

If your host caps at PHP 8.1, ask support to enable 8.2 — most do on request.

---

## Step 2 — Create the database

1. cPanel → **MySQL® Databases**.
2. Under **Create New Database**, type `bicte` → **Create**.
   *(cPanel will prefix it with your account, so the real name is e.g. `lahanmu1_bicte`.)*
3. Under **MySQL Users → Add New User**, create a user (e.g. `bicte_admin`) and a strong password. **Write down both.**
4. Scroll to **Add User To Database** → pick the user + database → **All Privileges** → submit.
5. Note the final values:
   - Host: `localhost`
   - Database name: `lahanmu1_bicte` (with prefix)
   - User: `lahanmu1_bicte_admin` (with prefix)
   - Password: *(what you set)*

---

## Step 3 — Build the deployment package on your computer

On your local machine (where everything currently runs):

**Linux/macOS:**
```bash
cd path/to/bicte
bash deploy/cpanel/prepare-deploy.sh
```

**Windows:**
```bat
cd path\to\bicte
deploy\cpanel\prepare-deploy.bat
```

This produces `bicte/dist/bicte-deploy.zip`. Inside the zip you'll find:
```
bicte/                ← Laravel app, vendor/ pre-installed
public_html/
  ├── index.php       ← already configured to point at ../bicte
  ├── .htaccess
  ├── robots.txt
  └── kiosk/          ← static kiosk frontend
HOW-TO-UPLOAD.txt
```

> If you don't have Composer locally, you can skip this step and instead clone the repo on the server (Step 4 alternative).

---

## Step 4 — Upload to cPanel

1. cPanel → **File Manager** → home directory (the level *above* `public_html`).
2. **Upload** → choose `bicte-deploy.zip` from your computer.
3. Right-click the uploaded zip → **Extract**.
4. After extraction you'll have:
   - `bicte/` (next to `public_html`, NOT inside it)
   - `public_html/` (with the new `index.php`, `.htaccess`, `kiosk/` subfolder)
5. Delete the zip file.

> **Do NOT put the `bicte/` folder inside `public_html/`.** Keeping the Laravel source outside the document root is what protects your `.env`, database credentials and storage from the public internet.

> If File Manager hides `.htaccess`, click **Settings** (top-right) → check **Show Hidden Files (dotfiles)** → reload.

### Alternative: clone via Terminal (no local build needed)

If your cPanel has the **Terminal** app and Git installed:

```bash
cd ~
git clone -b feat/smart-kiosk-scaffold https://github.com/rajalahan/bicte.git bicte-src
mv bicte-src/backend bicte
cp deploy/cpanel/public_html-index.php public_html/index.php
cp deploy/cpanel/htaccess-public_html.txt public_html/.htaccess
cp -r bicte-src/frontend public_html/kiosk
cd bicte
composer install --no-dev --optimize-autoloader
```

---

## Step 5 — Configure `.env`

In File Manager, navigate to `/home/<your_user>/bicte/`.

If `.env` doesn't exist yet, copy `.env.production-example` to `.env` (right-click → Copy → rename).

Right-click `.env` → **Edit**. Set these values:

```env
APP_NAME="Lahan Municipality Kiosk"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Kathmandu
APP_URL=https://yourdomain.com
APP_LOCALE=ne

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lahanmu1_bicte
DB_USERNAME=lahanmu1_bicte_admin
DB_PASSWORD=YourMySQLPasswordHere

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com

CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=no-reply@yourdomain.com
MAIL_PASSWORD=YourEmailPassword
MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

Save.

---

## Step 6 — Run database migrations

Two options depending on whether your host gives you Terminal access.

### Option A — Terminal app (preferred)

cPanel → **Terminal** → run:

```bash
cd ~/bicte
php artisan key:generate --force
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize
```

You should see all 13 migrations + 7 seeders run, ending with:
```
Default admin: admin@lahanmun.gov.np / changeme  (CHANGE IMMEDIATELY)
```

### Option B — Web installer (if no Terminal)

1. Open `deploy/cpanel/install.php` from this repo.
2. Edit the line `$INSTALL_TOKEN = 'CHANGE_ME_TO_A_LONG_RANDOM_STRING_AT_LEAST_32_CHARS';` to something like `BiCTe_2026_xPQ8_aZ7q9_LANDS_NOW_changeme!`.
3. Upload that edited file to `public_html/_install_KIOSK.php` (use a hard-to-guess name).
4. In your browser, visit each URL in order (replace `<TOKEN>`):
   ```
   https://yourdomain.com/_install_KIOSK.php?token=<TOKEN>&run=whoami
   https://yourdomain.com/_install_KIOSK.php?token=<TOKEN>&run=key
   https://yourdomain.com/_install_KIOSK.php?token=<TOKEN>&run=migrate
   https://yourdomain.com/_install_KIOSK.php?token=<TOKEN>&run=link
   https://yourdomain.com/_install_KIOSK.php?token=<TOKEN>&run=optimize
   ```
5. **Delete `public_html/_install_KIOSK.php` immediately afterwards.** It can wipe your DB if left in place.

---

## Step 7 — File permissions

The web server (usually user `nobody` or your cPanel user) must be able to write to two directories. In File Manager:

1. Right-click `bicte/storage` → **Change Permissions** → tick all "Read", "Write", "Execute" for User; "Read", "Execute" for Group/World → set to **755** (or **775** if PHP is running as a different user) → **Apply to subdirectories**.
2. Same for `bicte/bootstrap/cache`.

If you see "permission denied" errors in the logs (`bicte/storage/logs/laravel.log`), bump those two folders to **775**.

---

## Step 8 — Tell the kiosk where the API lives

The static kiosk pages currently default to `http://127.0.0.1:8000/api`. On production, point them at your real domain.

In File Manager, edit `public_html/kiosk/index.html`. Inside `<head>`, add **two** lines (the second is for the discreet admin button):

```html
<meta name="api-base"   content="https://yourdomain.com/api">
<meta name="admin-url"  content="https://yourdomain.com/admin">
```

Repeat for the other HTML files: `department.html`, `assistant.html`, `notices.html`, `feedback.html`, `token.html`, `directory.html`.

(Or run this once over SSH/Terminal:)
```bash
cd ~/public_html/kiosk
for f in *.html; do
  sed -i 's|<head>|<head>\n  <meta name="api-base"  content="https://yourdomain.com/api">\n  <meta name="admin-url" content="https://yourdomain.com/admin">|' "$f"
done
```

---

## Step 9 — Test

| URL | What you should see |
|---|---|
| `https://yourdomain.com/api/health` | `{"status":"ok","time":"…"}` |
| `https://yourdomain.com/api/departments` | JSON array with all 19 शाखा |
| `https://yourdomain.com/admin` | Blue admin login page |
| `https://yourdomain.com/kiosk/index.html` | Full kiosk homepage with logo, ticker, 19 tiles |

Login at `/admin`:
- Email: `admin@lahanmun.gov.np`
- Password: `changeme`

**Change the password immediately** — cPanel Terminal:
```bash
cd ~/bicte
php artisan tinker
>>> \App\Models\User::where('email','admin@lahanmun.gov.np')->first()->update(['password'=>\Hash::make('YourStrongPassword!')]);
>>> exit
```

Now whenever you want to edit anything (departments, services, room numbers, fees, notices, mayor message, AI answers), just go to **`https://yourdomain.com/admin`**, login, and use the menus on the left:
- 🏛 शाखा — edit any department's services / docs / fees / room
- 📢 सूचना — manage scrolling notice ticker
- 💬 गुनासो — view / triage citizen complaints
- 🎫 टोकन — live queue board

Changes appear instantly on the kiosk after refresh.

---

## Step 10 — Lock it down

- [ ] **HTTPS only.** cPanel → SSL/TLS → AutoSSL → enable for the domain. Then in `public_html/.htaccess`, uncomment the two `RewriteCond/RewriteRule` lines that force HTTPS.
- [ ] **Block `.env`.** Already done by the htaccess we shipped, but double-check: visit `https://yourdomain.com/.env` — should return 403/404, never the file content.
- [ ] **Disable directory listing.** Already done (`Options -Indexes`).
- [ ] **Strong admin password** (Step 9).
- [ ] **Daily DB backup.** cPanel → Backup → schedule weekly full backups, or use **Backup Wizard** for ad-hoc.
- [ ] **Fail2Ban / cPHulk** — turn it on in WHM (ask your host) to throttle login brute force.

---

## Common issues

| Symptom | Fix |
|---|---|
| White page / "500 Internal Server Error" on `/admin` | Check `bicte/storage/logs/laravel.log`. Most often: missing PHP extension (Step 1) or wrong `.env` DB credentials. |
| `SQLSTATE[HY000] [1045] Access denied` | DB user/password wrong, or user not added to the database (Step 2). |
| `The MAC is invalid` after login | `APP_KEY` mismatch. Re-run `php artisan key:generate --force` and `php artisan optimize:clear`. |
| Kiosk page renders but officials are "—" and ticker says "लोड गर्न सकिएन" | The kiosk can't reach the API. Did you set `<meta name="api-base">` (Step 8)? Test `/api/departments` in a browser. |
| `vendor/autoload.php not found` | You forgot to upload the `bicte/vendor/` folder. Re-run the build script (Step 3) or upload it via FTP. |
| 404 on every URL except home | `.htaccess` not uploaded. File Manager → Settings → Show hidden files → upload `htaccess-public_html.txt` and rename to `.htaccess`. |
| Only `/admin` works, `/kiosk/...` shows file listing | Apache `mod_dir` is missing the `index.html` directive. Add `DirectoryIndex index.html index.php` to top of `public_html/.htaccess`. |
| `Class "Spatie\Permission\..." not found` | Vendor folder corrupted in upload. Delete `bicte/vendor/`, re-upload, or run `composer install` via Terminal. |
| Permission denied writing to storage/logs | Step 7 — set storage and bootstrap/cache to 775. |

---

## Updating the site later

Whenever you change code on your laptop and want to push it to cPanel:

```bash
# locally
git pull
bash deploy/cpanel/prepare-deploy.sh
# upload bicte/dist/bicte-deploy.zip via File Manager
# extract → say YES to overwrite
# in cPanel Terminal:
cd ~/bicte
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
```

For content changes (department text, mayor message, notices, FAQ) you don't need to redeploy — just edit through `/admin`.

---

## What if my host doesn't allow apps outside public_html?

Some very basic hosting plans only give you `public_html` and nothing above. In that case:

1. Upload everything from `bicte-deploy.zip` into `public_html/` so you have:
   ```
   public_html/
   ├── bicte/        ← Laravel app
   ├── kiosk/        ← static frontend
   ├── index.php
   └── .htaccess
   ```
2. Edit `public_html/index.php` — change `__DIR__ . '/../bicte'` to `__DIR__ . '/bicte'`.
3. Add this rule to the **top** of `public_html/.htaccess` to block direct access to the source:
   ```
   RedirectMatch 403 ^/bicte(/|$)
   ```

This works but is slightly less secure than the recommended layout above. Use only as a last resort.
