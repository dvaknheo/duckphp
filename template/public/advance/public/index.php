<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../../../autoload.php');    //@DUCKPHP_HEADFILE

echo "<div>You should not run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行模板文件，建议用安装模式 </div>\n";              //@DUCKPHP_DELETE

\DuckPhp\Core\AutoLoader::RunQuickly([]);
\DuckPhp\Core\AutoLoader::addPsr4( "AdvanceDemo\\", __DIR__ . '/../src',);

$options = [
    //...
];
AdvanceDemo\System\App::RunQuickly($options);