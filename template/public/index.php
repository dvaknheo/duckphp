<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');    // @DUCKPHP_HEADFILE

echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行这文件，建议用安装模式 </div>\n"; //@DUCKPHP_DELETE

$options =[
    //'is_debug'=>true,
];

// 设置工程命名空间对应的目录，但强烈推荐修改 composer.json 使用 composer 加载。 
require_once __DIR__.'/../app/System/App.php';
$options['path_namespace'] = 'app';


//其他默认选项
//$options['path_info_compact_enable'] => true; // 如果你没设置 PATH_INFO 打开这项兼容
//$options['use_setting_file'] = true; // 如果你使用设置文件。

\LazyToChange\System\App::RunQuickly($options);
return;
/*
//也可以用
$options['override_class'] = \LazyToChange\System\App::class;
\DuckPhp\DuckPhp::RunQuickly($options);

return;
*/