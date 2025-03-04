<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//autoload file
$autoload_file = __DIR__.'../vendor/autoload.php';
if (is_file($autoload_file)) {
    require_once $autoload_file;
} else {
    $autoload_file = __DIR__.'/../../vendor/autoload.php';
    if (is_file($autoload_file)) {
        require_once $autoload_file;
    }
}
////////////////////////////////////////

if (!class_exists(\SebastianBergmann\CodeCoverage\CodeCoverage::class)) {
    echo "Need CodeCoverage";
    exit;
}

// 设置工程命名空间对应的目录，但强烈推荐修改 composer.json 使用 composer 加载
if (!class_exists(\ProjectNameTemplate\System\App::class)) {
    \DuckPhp\Core\AutoLoader::RunQuickly([]);
    \DuckPhp\Core\AutoLoader::addPsr4("ProjectNameTemplate\\", 'src');
}

function cover($src)
{
    $coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
    $coverage->filter()->addDirectoryToWhitelist($src);
    $coverage->start(DATE(DATE_ATOM));
    register_shutdown_function(function () use ($coverage) {
        $coverage->stop();
        $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade;
        $writer->process($coverage, __DIR__ .'/cover_report/');
    });
}

$ref = new ReflectionClass(\DuckPhp\DuckPhp::class);
$path_duckphp = realpath(dirname($ref->getFileName())).'/';
cover($path_duckphp);

/////////////
class MainController
{
    public function action_index()
    {
        echo '<meta http-equiv="refresh" content="5;cover_report/index.html" />';
        echo "用于计算执行行数 ，请确保 cover_report 可写。5秒后跳转到结果页面";
        var_dump(DATE(DATE_ATOM));
    }
}

class DemoApp extends \DuckPhp\DuckPhp
{
    public $options = [
        'is_debug' => true,
        'path' => __DIR__.'/',
        'namespace_controller' => '\\',
    ];
}

$options = [
    //
];

DemoApp::RunQuickly($options);

