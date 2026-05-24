<?php
/**
 * cPanel deployment entry point.
 *
 * Drop this file into ~/public_html/index.php so the public domain serves
 * the Laravel app while the application source stays OUTSIDE public_html
 * (recommended for shared hosting security).
 *
 * Expected directory layout on the cPanel server:
 *
 *   /home/<cpanel_user>/
 *   |-- bicte/                    <-- uploaded Laravel app (PRIVATE, not web-reachable)
 *   |   |-- app/  bootstrap/  config/  database/  resources/
 *   |   |-- routes/  storage/  vendor/
 *   |   |-- .env  artisan
 *   |   `-- ...
 *   `-- public_html/              <-- document root for the domain
 *       |-- index.php             <-- THIS FILE
 *       |-- .htaccess             <-- copy from bicte/public/.htaccess
 *       |-- robots.txt
 *       `-- kiosk/                <-- static frontend (copy of bicte/frontend/)
 *           `-- index.html ...
 *
 * If your Laravel folder is not literally named "bicte", change the
 * APP_PATH variable below to match.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ---------------------------------------------------------------
//  Where (relative to public_html) does the Laravel app live?
// ---------------------------------------------------------------
$APP_PATH = __DIR__ . '/../bicte';

// Maintenance-mode short-circuit
if (file_exists($maintenance = $APP_PATH . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $APP_PATH . '/vendor/autoload.php';

(require_once $APP_PATH . '/bootstrap/app.php')
    ->handleRequest(Request::capture());
