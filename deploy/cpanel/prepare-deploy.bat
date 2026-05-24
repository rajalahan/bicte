@echo off
REM ============================================================
REM Build a deployment-ready ZIP for cPanel upload (Windows).
REM Same output as prepare-deploy.sh.  Requires PowerShell 5+.
REM ============================================================

setlocal enableextensions
pushd "%~dp0..\.."
set ROOT=%CD%
set DIST=%ROOT%\dist
set STAGE=%DIST%\stage

echo ^> Project root: %ROOT%

if exist "%DIST%" rmdir /S /Q "%DIST%"
mkdir "%STAGE%\bicte"
mkdir "%STAGE%\public_html"

echo ^> Installing PHP dependencies (production)...
pushd "%ROOT%\backend"
call composer install --no-dev --optimize-autoloader --no-interaction
if errorlevel 1 ( echo composer install failed & popd & popd & exit /b 1 )
popd

echo ^> Copying backend - stage/bicte/
robocopy "%ROOT%\backend" "%STAGE%\bicte" /E ^
    /XD node_modules .git ^
    /XF .env .env.backup database\database.sqlite >nul

REM Clear runtime caches that will be re-created on the server
del /Q "%STAGE%\bicte\storage\framework\cache\data\*"  2>nul
del /Q "%STAGE%\bicte\storage\framework\sessions\*"     2>nul
del /Q "%STAGE%\bicte\storage\framework\views\*"        2>nul
del /Q "%STAGE%\bicte\storage\logs\*"                   2>nul
del /Q "%STAGE%\bicte\bootstrap\cache\*.php"            2>nul

echo ^> Building public_html/
copy /Y "%ROOT%\deploy\cpanel\public_html-index.php"  "%STAGE%\public_html\index.php"  >nul
copy /Y "%ROOT%\deploy\cpanel\htaccess-public_html.txt" "%STAGE%\public_html\.htaccess" >nul
copy /Y "%ROOT%\backend\public\robots.txt"            "%STAGE%\public_html\robots.txt" >nul

echo ^> Copying frontend - stage/public_html/kiosk/
mkdir "%STAGE%\public_html\kiosk"
robocopy "%ROOT%\frontend" "%STAGE%\public_html\kiosk" /E >nul

copy /Y "%ROOT%\backend\.env.example" "%STAGE%\bicte\.env.production-example" >nul

echo ^> Zipping...
powershell -NoLogo -Command "Compress-Archive -Path '%STAGE%\*' -DestinationPath '%DIST%\bicte-deploy.zip' -Force"

echo.
echo Build complete.
echo   %DIST%\bicte-deploy.zip
echo.
echo Upload that single zip to cPanel and follow deploy/cpanel/HOW-TO-DEPLOY.md
popd
endlocal
