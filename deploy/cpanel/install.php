<?php
/**
 * One-time web installer for cPanel hosts WITHOUT SSH / Terminal access.
 *
 * USAGE
 * -----
 * 1. Edit $INSTALL_TOKEN below to a long random string of YOUR choice.
 *    Anyone who knows this URL + token can run installer commands, so
 *    keep it secret and DELETE THIS FILE after you finish setup.
 *
 * 2. Upload this file as:    public_html/_install_KIOSK.php
 *    (NOT named "install.php" - use a hard-to-guess name.)
 *
 * 3. Visit in browser:
 *      https://yourdomain.com/_install_KIOSK.php?token=YOUR_TOKEN&run=key
 *      https://yourdomain.com/_install_KIOSK.php?token=YOUR_TOKEN&run=migrate
 *      https://yourdomain.com/_install_KIOSK.php?token=YOUR_TOKEN&run=link
 *      https://yourdomain.com/_install_KIOSK.php?token=YOUR_TOKEN&run=optimize
 *
 * 4. **DELETE THIS FILE** when done.  This script can wipe your DB
 *    if left in place.
 *
 * Each command runs an artisan equivalent and streams the output.
 */

// =============== CONFIG (EDIT BEFORE UPLOADING) ================
$INSTALL_TOKEN = 'CHANGE_ME_TO_A_LONG_RANDOM_STRING_AT_LEAST_32_CHARS';
$APP_PATH      = __DIR__ . '/../bicte';   // adjust if your Laravel folder is named differently
// ===============================================================

if (!isset($_GET['token']) || !hash_equals($INSTALL_TOKEN, (string) $_GET['token'])) {
    http_response_code(403);
    exit('Forbidden');
}
if ($INSTALL_TOKEN === 'CHANGE_ME_TO_A_LONG_RANDOM_STRING_AT_LEAST_32_CHARS') {
    http_response_code(500);
    exit('Refusing to run: edit $INSTALL_TOKEN in install.php first.');
}

@set_time_limit(300);
header('Content-Type: text/plain; charset=utf-8');

if (!is_dir($APP_PATH)) {
    exit("APP_PATH '$APP_PATH' not found. Edit install.php and fix the path.\n");
}

chdir($APP_PATH);
$cmd = $_GET['run'] ?? 'help';
$artisan = PHP_BINARY . ' ' . escapeshellarg($APP_PATH . '/artisan');

switch ($cmd) {
    case 'help':
        echo "Available commands (append ?token=...&run=NAME):\n";
        echo "  key       -  generate APP_KEY in .env\n";
        echo "  migrate   -  run migrations and seed (idempotent)\n";
        echo "  fresh     -  DROP and re-create all tables, then seed (DESTRUCTIVE)\n";
        echo "  link      -  create storage symlink\n";
        echo "  optimize  -  cache config / routes / views (do this after editing .env)\n";
        echo "  clear     -  clear all caches\n";
        echo "  whoami    -  show server info, php version, db connectivity\n";
        echo "\nIMPORTANT: delete this file after you're done.\n";
        break;

    case 'whoami':
        passthru("$artisan --version");
        echo "PHP: " . PHP_VERSION . "\n";
        echo "User: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user()) . "\n";
        echo "Path: " . __DIR__ . "\n\n";
        passthru("$artisan db:show 2>&1");
        break;

    case 'key':       passthru("$artisan key:generate --force --ansi"); break;
    case 'migrate':   passthru("$artisan migrate --force --seed --ansi"); break;
    case 'fresh':
        if (($_GET['confirm'] ?? '') !== 'yes-i-know-this-deletes-data') {
            exit("Refusing to run 'fresh': append &confirm=yes-i-know-this-deletes-data to the URL.\n");
        }
        passthru("$artisan migrate:fresh --force --seed --ansi"); break;
    case 'link':      passthru("$artisan storage:link --ansi"); break;
    case 'optimize':  passthru("$artisan optimize --ansi"); break;
    case 'clear':     passthru("$artisan optimize:clear --ansi"); break;

    default:
        http_response_code(400);
        exit("Unknown command: $cmd\n");
}

echo "\n--- done ---\n";
echo "REMINDER: delete this file (public_html/" . basename(__FILE__) . ") now.\n";
