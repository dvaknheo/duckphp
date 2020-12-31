<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');    // @DUCKPHP_HEADFILE

//如果配置了 compose.json 加载 ，可以省略这两句
\DuckPhp\DuckPhp::assignPathNamespace(__DIR__ . '/../app', 'LazyToChange');
\DuckPhp\DuckPhp::runAutoLoader();

echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行这文件，建议用安装模式 </div>\n";              //@DUCKPHP_DELETE

$options = [
    // 这里可以添加更多选项。
];
//*/
\LazyToChange\System\App::RunQuickly($options);
//*/

/* //等价于
$options['override_class'] = LazyToChange\System\App::class,
\DuckPhp\DuckPhp::RunQuickly($options);
//*/