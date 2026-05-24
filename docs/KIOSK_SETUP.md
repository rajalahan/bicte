# Kiosk Setup – 43" Touchscreen (Windows 10/11)

This document walks the IT Section through provisioning the touchscreen PC that drives the 43" display in the central office lobby.

## 1. Hardware checklist

| Item                | Recommended                                         |
| ------------------- | --------------------------------------------------- |
| Display             | 43" 1080p IR/PCAP touch monitor (or interactive flat panel) |
| Mini-PC             | Intel i3 / Ryzen 3, 8 GB RAM, 256 GB SSD            |
| OS                  | Windows 10/11 Pro (64-bit) – domain joined optional |
| Power               | Both display and PC on UPS                          |
| Network             | Wired Ethernet preferred                            |
| Optional            | USB receipt printer for token slips                 |

## 2. Windows pre-flight

1. Create a **dedicated local user** `kiosk` (no admin privileges).
2. Disable Windows Update reboots during business hours (Group Policy → "Active Hours").
3. Disable lock screen, screensaver, sleep mode (Settings → System → Power).
4. Disable on-screen keyboard auto-popup if hardware has its own.
5. Install Google Chrome (Stable channel) for **all users**.
6. Install **Noto Sans Devanagari** and **Mukta** fonts system-wide if not already present.

## 3. Lock down the browser

Install the Chrome ADMX templates and apply these policies (Group Policy → Computer or User):

| Policy                                  | Value                                                   |
| --------------------------------------- | ------------------------------------------------------- |
| `URLBlocklist`                          | `*` (block everything)                                  |
| `URLAllowlist`                          | `http://kiosk.lahanmun.local/*`, `https://api.open-meteo.com/*`, `https://api.qrserver.com/*`, `https://fonts.googleapis.com/*`, `https://fonts.gstatic.com/*`, `https://cdn.jsdelivr.net/*` |
| `IncognitoModeAvailability`             | `1` (disabled)                                          |
| `BookmarkBarEnabled`                    | `false`                                                 |
| `DefaultBrowserSettingEnabled`          | `true`                                                  |
| `PrintingEnabled`                       | `true` (only if printer attached)                       |
| `AutoplayAllowed`                       | `true` (audio voice playback)                           |
| `FullscreenAllowed`                     | `true`                                                  |

> If Chrome ADMX is unavailable, the same policies can be set via `HKLM\Software\Policies\Google\Chrome` registry keys.

## 4. Auto-start Chrome in kiosk mode

Use the Windows scheduler / Startup shortcut.

### 4.1 Create launcher script

Save as `C:\bicte\kiosk-launch.bat` (already provided in the repo as `scripts/kiosk-launch.bat`):

```bat
@echo off
REM Kill any orphan Chrome (after a crash / unclean shutdown)
taskkill /F /IM chrome.exe /T >nul 2>&1
timeout /t 2 /nobreak >nul

set URL=http://kiosk.lahanmun.local/index.html
set PROFILE=C:\bicte\chrome-profile

start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" ^
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
```

### 4.2 Run on login

Place a shortcut to `kiosk-launch.bat` in the kiosk user's Startup folder:

```
%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\
```

…or schedule it via Task Scheduler ("At log on of any user", "Run with highest privileges").

### 4.3 Auto-login on boot

`netplwiz` → uncheck "Users must enter a username and password" → set the kiosk user as the auto-login account.

### 4.4 Heartbeat / auto-recovery

Add a 5-minute heartbeat task that re-launches the script if Chrome is missing:

```bat
@echo off
tasklist /FI "IMAGENAME eq chrome.exe" 2>NUL | find /I "chrome.exe" >NUL || start "" "C:\bicte\kiosk-launch.bat"
```

Schedule with Task Scheduler → trigger every 5 minutes, indefinitely.

## 5. Touchscreen tweaks

- Calibrate touch via Tablet PC Settings.
- Disable Edge swipe gestures: `gpedit.msc → User Configuration → Administrative Templates → Windows Components → Edge UI → Allow edge swipe = Disabled`.
- Hide the Windows taskbar: Taskbar settings → "Automatically hide the taskbar".

## 6. Useful keyboard rescue

If something goes wrong on site, plug in a keyboard:

| Shortcut             | Action                                  |
| -------------------- | --------------------------------------- |
| `Ctrl + Alt + Del`   | Windows control                         |
| `Alt + F4`           | Quit Chrome kiosk                       |
| `Ctrl + Shift + N`   | (disabled by policy – good)             |
| `F11`                | Toggle fullscreen (if not in `--kiosk`) |

## 7. Field test checklist

- [ ] Display turns on automatically when PC boots
- [ ] Chrome launches in fullscreen, no chrome (the browser kind) visible
- [ ] Touch works on tiles, AI assistant text input, token buttons
- [ ] Notice ticker scrolls
- [ ] Clock + Nepali date update every second
- [ ] After 3 minutes idle on a sub-page, the kiosk auto-returns to home
- [ ] After 1 minute idle, cursor disappears
- [ ] Yanking the network cable: kiosk keeps working from `assets/data/*.json` fallbacks
- [ ] Restart the kiosk PC → it boots straight back into the kiosk

## 8. On-call cheat sheet (paste in lobby)

```
Service centre cannot be touched? Press Ctrl+Alt+Del → restart.
Information out of date?         Login at https://admin.lahanmun.gov.np
Citizens cannot type Nepali?     Tap any field – on-screen keyboard auto-shows.
Voice button not working?        Ensure the mic is enabled in Windows privacy settings.
```
