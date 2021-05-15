<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../../vendor/autoload.php');

function cover($src)
{
    $coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();    
    $coverage->filter()->addDirectoryToWhitelist($src);
    $coverage->start(DATE(DATE_ATOM));
    register_shutdown_function(function()use($coverage){
        $coverage->stop();
        $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
        $writer->process($coverage, __DIR__ .'/cover_report/');
    });
}
if (!class_exists(\SebastianBergmann\CodeCoverage\CodeCoverage::class)) {
    echo "开发人员专用";
    return;
}
cover(realpath(__DIR__.'/../../../src'));


if (!class_exists(\LazyToChange\System\App::class)) {
    \DuckPhp\DuckPhp::assignPathNamespace(__DIR__ . '/../../app', "LazyToChange\\"); 
    \DuckPhp\DuckPhp::runAutoLoader();
}

$options = [
    'path' => realpath(__DIR__.'/../../').'/',
    'override_class' => LazyToChange\System\App::class,

];
LazyToChange\System\App::RunQuickly($options);


echo '<meta http-equiv="refresh" content="5;cover_report/index.html" />';
echo "用于计算执行行数 ，请确保 cover_report 可写。5秒后跳转到结果页面";
var_dump(DATE(DATE_ATOM));