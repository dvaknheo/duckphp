<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//autoload file
$autoload_file = __DIR__.'/../vendor/autoload.php';
if (is_file($autoload_file)) {
    require_once $autoload_file;
} else {
    $autoload_file = __DIR__.'/../../vendor/autoload.php';
    if (is_file($autoload_file)) {
        require_once $autoload_file;
    }
}
////////////////////////////////////////
if (!class_exists(\ProjectNameTemplate\System\App::class)) {
    \DuckPhp\Core\AutoLoader::RunQuickly([
        'path'=>__DIR__.'/../',
    ]);
    \DuckPhp\Core\AutoLoader::addPsr4("ProjectNameTemplate\\", 'src');
}
$options = [
    //'is_debug' => true,
    // more options ...
];
////[[[[
//fix path_info
if($_SERVER['PATH_INFO']===''&&$_SERVER['SCRIPT_NAME']==='/index.php'){
    $path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
    var_dump($path);
}
////]]]]
\ProjectNameTemplate\System\App::RunQuickly($options);
