#!/usr/bin/env bash
# ============================================================
# One-shot developer bootstrap for bicte.
# Use on a fresh Ubuntu/Debian dev machine after cloning.
# Production deploys should follow docs/DEPLOYMENT.md instead.
# ============================================================

set -euo pipefail

cd "$(dirname "$0")/.."
ROOT="$(pwd)"

echo "▶ bicte dev bootstrap"
echo "  root: $ROOT"

# ---------- Backend ----------
cd "$ROOT/backend"

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -d vendor ]; then
    echo "▶ composer install"
    composer install
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    echo "▶ php artisan key:generate"
    php artisan key:generate
fi

read -rp "Run migrate:fresh --seed? (DESTROYS data) [y/N] " ans
case "$ans" in
    y|Y) php artisan migrate:fresh --seed ;;
    *)   php artisan migrate --seed || true ;;
esac

# ---------- Frontend ----------
cd "$ROOT/frontend"

# Static – nothing to build. Optionally serve via python so we have file:// CORS sanity.
if command -v python3 >/dev/null 2>&1; then
    echo "▶ Frontend will be served on http://127.0.0.1:5173 (Ctrl+C to stop)"
fi

cat <<EOF

✔ bicte is ready.

  API:       cd backend && php artisan serve   →  http://127.0.0.1:8000
  Frontend:  cd frontend && python3 -m http.server 5173
  Admin:     http://127.0.0.1:8000/admin
             admin@lahanmun.gov.np / changeme   (CHANGE IT!)

EOF
