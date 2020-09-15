<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE

// 这几项是在子类里无法更改的
$options = [
//    'use_autoloader' => true,
    // 使用 DuckPhp\AutoLoader 加载器，你可以用composer
//    'skip_plugin_mode_check' => false,
    // 跳过插件模式检查
//    'handle_all_dev_error' => true,
    // 处理所有开发期异常
//    'handle_all_exception' => true,
    // 处理所有异常
//    'override_class' => 'System\App',
    // 再入的子类
//    'path_namespace' => 'app',
    // 自动加载的目录
];


$namespace = 'LazyToChange';                              // @DUCKPHP_NAMESPACE
$path = realpath(__DIR__.'/..');
$options['namespace'] = $namespace;
$options['path'] = $path;

echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行这文件，建议用安装模式 </div>\n"; //@DUCKPHP_DELETE

\DuckPhp\DuckPhp::RunQuickly($options);
