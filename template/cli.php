<?php
require_once(__DIR__.'/../autoload.php');    //@DUCKPHP_HEADFILE

if (!class_exists(\ProjectNameTemplate\System\App::class)) {
    \DuckPhp\Core\AutoLoader::RunQuickly([
        'path'=>__DIR__.'/',
    ]);
    \DuckPhp\Core\AutoLoader::addPsr4("ProjectNameTemplate\\", 'src');
}
$options = [
    //'is_debug' => true,
    // more options ...
];

$options['path'] = __DIR__.'/';
\ProjectNameTemplate\System\App::RunQuickly($options);
