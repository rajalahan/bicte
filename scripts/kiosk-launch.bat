@echo off
REM ============================================================
REM  Lahan Municipality Smart Kiosk – Windows launcher
REM  Place a shortcut to this file in the kiosk user's Startup
REM  folder, or schedule via Task Scheduler "At log on".
REM ============================================================

REM Kill any orphan Chrome (after a crash / unclean shutdown)
taskkill /F /IM chrome.exe /T >nul 2>&1
timeout /t 2 /nobreak >nul

REM ---------- CONFIG ----------
set URL=http://kiosk.lahanmun.local/index.html
set PROFILE=C:\bicte\chrome-profile

REM Locate Chrome (prefer 64-bit install)
set CHROME="C:\Program Files\Google\Chrome\Application\chrome.exe"
if not exist %CHROME% set CHROME="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
if not exist %CHROME% (
    echo Google Chrome is not installed.
    pause
    exit /b 1
)

REM ---------- LAUNCH ----------
start "" %CHROME% ^
  --kiosk %URL% ^
  --user-data-dir=%PROFILE% ^
  --no-first-run ^
  --no-default-browser-check ^
  --disable-pinch ^
  --overscroll-history-navigation=0 ^
  --disable-translate ^
  --disable-features=TranslateUI ^
  --disable-session-crashed-bubble ^
  --disable-infobars ^
  --noerrdialogs ^
  --start-maximized ^
  --force-device-scale-factor=1
