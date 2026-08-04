<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//autoload file
$autoload_file = __DIR__.'/vendor/autoload.php';
if (is_file($autoload_file)) {
    require_once $autoload_file;
} else {
    $autoload_file = __DIR__.'/../vendor/autoload.php';
    if (is_file($autoload_file)) {
        require_once $autoload_file;
    }
}
////////////////////////////////////////

//strong suguess use composer
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

\ProjectNameTemplate\System\App::RunQuickly($options);
