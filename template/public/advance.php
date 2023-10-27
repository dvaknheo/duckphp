<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');    //@DUCKPHP_HEADFILE

echo "<div>You should not run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行模板文件，建议用安装模式 </div>\n";              //@DUCKPHP_DELETE

// 设置工程命名空间对应的目录，但强烈推荐修改 composer.json 使用 composer 加载 
if (!class_exists(\LazyToChange\System\App::class)) {
    \DuckPhp\DuckPhp::G()->assignPathNamespace(__DIR__ . '/advance', "AdvanceDemo\\"); 
    \DuckPhp\DuckPhp::G()->runAutoLoader();
}

$options = [
    // 这里可以添加更多选项
    //'is_debug' => true,
    //'override_class' => '',
];
//*/
\DuckPhp\DuckPhp::RunQuickly($options);