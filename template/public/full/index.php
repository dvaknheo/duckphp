<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/index.php' || $path === '/') {
    header('location: /public/index.php', true, 302);
}
if ($path === '/full/index.php' || $path === '/full/') {
    header('location: /full/public/index.php', true, 302);
}
