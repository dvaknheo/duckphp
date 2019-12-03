<?php
if (defined('DuckPhp_HEAD_FILE_LOADED')) {
    return;
}
define('DuckPhp_HEAD_FILE_LOADED', true);

$IN_COMPOSER=false;
if ($IN_COMPOSER) {
    require_once(__DIR__ .'/../vendor/autoload.php');
    return;
}

$file=realpath(__DIR__.'/../../autoload.php');
if (!is_file($file)) {
    exit("Can't found DuckPhp.php -- By ".__FILE__);
}
define('DuckPhp_WARNING_IN_TEMPLATE', true);
require_once $file;
return;
