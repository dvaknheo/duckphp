<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../../../autoload.php');    //@DUCKPHP_HEADFILE

echo "<div>You should not run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行模板文件，建议用安装模式 </div>\n";              //@DUCKPHP_DELETE


////[[[[
$config = require_once(__DIR__.'/../config/DuckPhpSettings.config.php');
$class = $config['duckphp_app'];

$t = explode("\\",$class);
array_pop($t);
array_pop($t);
$namespace = implode("\\",$t);

\DuckPhp\Core\AutoLoader::G()->runAutoLoader();
\DuckPhp\Core\AutoLoader::G()->assignPathNamespace(__DIR__ . '/../src', $namespace."\\");    

$options = [
    'override_class' => $class,
    //...
];

\DuckPhp\DuckPhp::RunQuickly($options);
////]]]]
// if you use composer loader ,easyly  just 
//AdvanceDemo\System\App::RunQuickly($options);