<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');    // @DUCKPHP_HEADFILE
require_once __DIR__.'/../app/System/App.php';  //@DUCKPHP_DELETE

$options =[
    //'is_debug'=>true,
];

// 设置工程命名空间对应的目录，但强烈建议用 composer 加载。
$options['path_namespace'] = 'app';


//$options['path_info_compact_enable'] => true;
//$options['use_setting_file'] = true;

echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行这文件，建议用安装模式 </div>\n"; //@DUCKPHP_DELETE

\LazyToChange\System\App::RunQuickly($options);
