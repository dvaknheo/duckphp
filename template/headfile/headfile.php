<?php
if (defined('DNMVCS_HEAD_FILE_LOADED')) {
    return;
}
define('DNMVCS_HEAD_FILE_LOADED', true);

$IN_COMPOSER=false;
if ($IN_COMPOSER) {
    require_once(__DIR__ .'/../vendor/autoload.php');
    return;
}

$file=realpath(__DIR__.'/../../autoload.php');
if (!is_file($file)) {
    exit("Can't found DNMVCS.php -- By ".__FILE__);
}
define('DNMVCS_WARNING_IN_TEMPLATE', true);

return;
