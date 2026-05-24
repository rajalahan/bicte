#!/usr/bin/env bash
# ============================================================
# Build a deployment-ready ZIP for cPanel upload.
#
# Run this on your LOCAL dev machine. It produces:
#   dist/bicte-deploy.zip
# which contains:
#   - bicte/      (Laravel app, with vendor/ pre-installed, ready to upload
#                  to /home/<cpanel_user>/bicte/)
#   - public_html/(replacement index.php + .htaccess + the static frontend
#                  in a 'kiosk/' subfolder, ready to merge into your
#                  cPanel public_html/)
#
# Re-run any time you want to push a fresh build to production.
# ============================================================

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
DIST="$ROOT/dist"
STAGE="$DIST/stage"

echo "▶ Project root: $ROOT"
rm -rf "$DIST"
mkdir -p "$STAGE/bicte" "$STAGE/public_html"

# ---------- 1. Build Laravel app ----------
echo "▶ Installing PHP dependencies (production)..."
( cd "$ROOT/backend" && composer install --no-dev --optimize-autoloader --no-interaction )

echo "▶ Copying backend → stage/bicte/"
rsync -a \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='.env.backup' \
    --exclude='database/database.sqlite' \
    --exclude='storage/app/public/*' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/logs/*' \
    --exclude='bootstrap/cache/*.php' \
    --exclude='.git*' \
    "$ROOT/backend/" "$STAGE/bicte/"

# ---------- 2. Public_html ----------
echo "▶ Building public_html/"
cp "$ROOT/deploy/cpanel/public_html-index.php" "$STAGE/public_html/index.php"
cp "$ROOT/deploy/cpanel/htaccess-public_html.txt" "$STAGE/public_html/.htaccess"
cp "$ROOT/backend/public/robots.txt"              "$STAGE/public_html/robots.txt"

# Frontend (kiosk) goes inside public_html/kiosk/
echo "▶ Copying frontend → stage/public_html/kiosk/"
mkdir -p "$STAGE/public_html/kiosk"
rsync -a "$ROOT/frontend/" "$STAGE/public_html/kiosk/"

# ---------- 3. .env template ----------
cp "$ROOT/backend/.env.example" "$STAGE/bicte/.env.production-example"
cat > "$STAGE/HOW-TO-UPLOAD.txt" <<'EOF'
============================================================
 Lahan Municipality Kiosk – cPanel deployment package
============================================================

CONTENTS
--------
  bicte/            -> upload to /home/<your_cpanel_user>/bicte/
                       (NOT inside public_html – it must stay private)
  public_html/      -> MERGE these files into your existing
                       /home/<your_cpanel_user>/public_html/
                       (don't delete files already in there
                       unless you know what they do)

NEXT STEPS
----------
1. cPanel → File Manager → upload bicte-deploy.zip into your
   home directory, then "Extract" it.
2. Move the extracted "bicte/" folder to /home/<user>/bicte/.
3. Move every file inside the extracted "public_html/" into
   your real public_html/.
4. cPanel → MySQL Databases → create database + user, give
   user "ALL PRIVILEGES" on the DB.
5. Edit /home/<user>/bicte/.env (rename .env.production-example
   to .env first if .env doesn't exist) and fill in:
      APP_URL=https://yourdomain.com
      DB_DATABASE=<your_cpanel_db>
      DB_USERNAME=<your_cpanel_user>
      DB_PASSWORD=<password>
6. Run setup commands.  Either via cPanel → Terminal:
      cd ~/bicte
      php artisan key:generate
      php artisan migrate --seed
      php artisan storage:link
      php artisan optimize
   …or via the web installer (see deploy/cpanel/install.php
   in the repo) if no Terminal app is available.
7. Set folder permissions to 755:
      ~/bicte/storage      (writable by web user)
      ~/bicte/bootstrap/cache
8. Visit https://yourdomain.com/admin
   Login: admin@lahanmun.gov.np / changeme
   CHANGE THE PASSWORD IMMEDIATELY.

The kiosk will be at:
  https://yourdomain.com/kiosk/index.html

Full guide: docs/CPANEL_DEPLOYMENT.md
EOF

# ---------- 4. Zip it ----------
echo "▶ Zipping..."
( cd "$STAGE" && zip -qr "$DIST/bicte-deploy.zip" . )
SIZE=$(du -h "$DIST/bicte-deploy.zip" | cut -f1)

echo
echo "✔ Build complete."
echo "  $DIST/bicte-deploy.zip  ($SIZE)"
echo
echo "Upload that single zip to cPanel and follow HOW-TO-UPLOAD.txt"
