#!/usr/bin/env bash
# ============================================================
# Lahan Municipality Smart Kiosk – Linux (Chromium) launcher
# Useful when running the kiosk on Ubuntu + GNOME / a digital-
# signage Linux box. Add to the kiosk user's autostart (Tweaks
# → Startup Applications) or run from systemd.
# ============================================================

set -euo pipefail

URL="${KIOSK_URL:-http://kiosk.lahanmun.local/index.html}"
PROFILE="${KIOSK_PROFILE:-$HOME/.bicte-chrome-profile}"

# Pick chromium or google-chrome – whichever is installed.
BROWSER=""
for candidate in google-chrome chromium-browser chromium; do
    if command -v "$candidate" >/dev/null 2>&1; then
        BROWSER="$candidate"
        break
    fi
done
if [ -z "$BROWSER" ]; then
    echo "Neither google-chrome nor chromium is installed." >&2
    exit 1
fi

# Disable screen blanking / sleep / DPMS
xset s off       || true
xset s noblank   || true
xset -dpms       || true

# Kill stragglers from a previous crash
pkill -f "$BROWSER --kiosk" || true
sleep 1

exec "$BROWSER" \
    --kiosk "$URL" \
    --user-data-dir="$PROFILE" \
    --no-first-run \
    --no-default-browser-check \
    --disable-pinch \
    --overscroll-history-navigation=0 \
    --disable-translate \
    --disable-features=TranslateUI \
    --disable-session-crashed-bubble \
    --disable-infobars \
    --noerrdialogs \
    --start-maximized \
    --force-device-scale-factor=1
