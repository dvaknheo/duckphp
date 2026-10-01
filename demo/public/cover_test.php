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

// point the project namespace at its directory; using composer's autoload instead
// is strongly recommended
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
        // coverage tooling page: kept English-only (it is not part of the demo tour)
        echo "Counting executed lines; make sure cover_report/ is writable. Redirecting to the report in 5 seconds.";
        var_dump(DATE(DATE_ATOM));
    }
}

class DemoApp extends \DuckPhp\DuckPhp
{
    public $options = [
        'is_debug' => true,
        'path' => __DIR__.'/',
        'namespace_controller' => '\\',
        // the controller below is `action_index()`, so the welcome route has to
        // look for the `action_` prefixed method (without this the page 404s)
        'controller_method_prefix' => 'action_',
    ];
}

$options = [
    //
];

DemoApp::RunQuickly($options);

