<?php

foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}

if(!class_exists('LibCoverage\LibCoverage')){
    include __DIR__ . '/LibCoverage.php'; //use LibCoverage outside
}

$setting = require __DIR__.'/data_for_tests/setting.php';
$options = $setting['options_test'];
\LibCoverage\LibCoverage::G()->init($options);
