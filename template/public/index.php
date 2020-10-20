<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
require_once __DIR__.'/../app/System/App.php';

$options =[];

// 默认选项，全局处理所有开发期异常
// $options ['handle_all_dev_error'] = true;

// 默认选项，全局处理所有异常
// $options ['handle_all_exception'] = true;

//强烈建议去掉这项，用 composer.json 加载工程文件。
$options['path_namespace'] = 'app';

echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行这文件，建议用安装模式 </div>\n"; //@DUCKPHP_DELETE

\LazyToChange\System\App::RunQuickly($options);