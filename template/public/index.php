<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
require_once(__DIR__.'/../app/System/App.php');

$options =[];

// 处理所有开发期异常
// $options ['handle_all_dev_error'] = true;

// 处理所有异常
// $options ['handle_all_exception'] = true;

//如果你在 composer.json 里加载文件，则去掉这项。
$options['path_project_namespace'] = 'app';

echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行这文件，建议用安装模式 </div>\n"; //@DUCKPHP_DELETE

\LazyToChange\System\App::RunQuickly($options);