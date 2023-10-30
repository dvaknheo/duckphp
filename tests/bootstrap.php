<?php

foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}
class MyLibCoverage extends \LibCoverage\LibCoverage
{
    // 我们接下来修一下，一个函数只能 assert一次的问题。
    // 思路是开始的时候 没设置文件的时候 设置为0. 第一次跑完，填充全为0 的数组，报告达到100% 的时候，更新次数
    //
}
$setting = require __DIR__.'/data_for_tests/setting.php';
$options = $setting['options_test'];
\LibCoverage\LibCoverage::G(MyLibCoverage::G())->init($options);

