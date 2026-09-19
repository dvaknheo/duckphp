<?php declare(strict_types=1);
/**
 * ZThirdDemo - dev only: router script for PHP's built-in server.
 *
 * Why this file exists:
 * - PHP's built-in server does not hand URIs that look like files (e.g. /res/main.css)
 *   to index.php, so resources served by RouteHookResource would 404;
 * - when a router script is used, PATH_INFO is empty, so the framework cannot tell
 *   which route was requested (it only repairs that when SCRIPT_NAME is /index.php).
 *
 * Usage:  php -S 127.0.0.1:8080 -t public public/router.php
 * (the framework's own `php bin/cli.php run` starts the server without a router)
 */
$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;   // let the built-in server send the real file itself
}
$_SERVER['PATH_INFO'] = $path;      // tell the framework which route was requested
require __DIR__ . '/index.php';
