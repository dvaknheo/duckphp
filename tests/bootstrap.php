<?php

foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

$setting = require __DIR__.'/data_for_tests/setting.php';
$options = $setting['options_test'];
\LibCoverage\LibCoverage::G()->init($options);
