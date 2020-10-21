<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE

$options =[
    //'is_debug'=>true,
];

require_once __DIR__.'/../app/System/App.php';
//设置命名空间 LazyToChange 对应的目录，但强烈建议用 composer 加载。
$options['path_namespace'] = 'app';

echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行这文件，建议用安装模式 </div>\n"; //@DUCKPHP_DELETE

\LazyToChange\System\App::RunQuickly($options);
