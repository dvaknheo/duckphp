# 一般流程
## 相关类


## 请求流程和生命周期

我们看入口类文件 public/index.php

```php
<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
$path = realpath(__DIR__.'/..');
$namespace = rtrim('MY\\', '\\');                    // @DUCKPHP_NAMESPACE
////[[[[
$options =
array(
    //省略一堆注释性配置
);
////]]]]
$options['path'] = $path;
$options['namespace'] = $namespace;
$options['error_404'] = '_sys/error_404';
$options['error_500'] = '_sys/error_500';
$options['error_debug'] = '_sys/error_debug';

$options['is_debug'] = true;                  // @DUCKPHP_DELETE
$options['skip_setting_file'] = true;                 // @DUCKPHP_DELETE
echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE


\DuckPhp\App::RunQuickly($options, function () {
});
```

入口类前面部分是处理头文件的。
然后处理直接 copy 代码提示，不要直接运行。
起作用的主要就这句话
```php
\DuckPHP\App::RunQuickly($options, function () {
});
```
相当于 \DuckPHP\App::G()->init($options)->run(); 第二个参数的回调用于 init 之后执行。

init, run 分两步走的模式。

最后留了 dump 选项的语句。

接下来我们看 $options 里可以选什么


## 高级说明


### 请求流程和生命周期
DuckPHP\App::RunQuickly($options) 发生了什么

等价于 DuckPHP\App::G()->init($options,$callback)->run();

init 为初始化阶段 ，run 为运行阶段。$callback 在init() 之后执行（也是为了偷懒

init 初始化阶段
    处理是否是插件模式
    处理自动加载  AutoLoader::G()->init($options, $this)->run();
    处理异常管理 ExceptionManager::G()->init($exception_options, $this)->run();
    如果有子类，切入子类继续 checkOverride() 
    调整补齐选项 initOptions()
    

    【重要】 onInit()，可 override 处理这里了。
    默认的 onInit
        初始化 Configer
        从 Configer 再设置 是否调试状态和平台 reloadFlags();
        初始化 View
        设置为已载入 View ，用于发生异常时候的显示。
        初始化 Route
        初始化扩展 initExtentions()
    初始化阶段就结束了。

run() 运行阶段

    处理 addBeforeRunHandler() 引入的 beforeRunHandlers
    * onRun ，可 override 处理这里了。
    重制 RuntimeState 并设置为开始
    绑定路由
    ** 开始路由处理 Route::G()->run();
    如果返回 404 则 On404() 处理 404
    clear 清理
        如果没显示，而且还有 beforeShowHandlers() 处理（用于处理 DB 关闭等
        设置 RuntimeState 为结束

   路由流程


## 全部默认选项
```
array (
  'all_config' => 
  array (
  ),
  
  'before_get_db_handler' => NULL,
  
// 来自 [DBManger]
  'controller_base_class' => NULL,
  'controller_hide_boot_class' => false,
  'controller_methtod_for_miss' => '_missing',
  'controller_postfix' => '',
  'controller_prefix_post' => 'do_',
  'controller_welcome_class' => 'Main',
  'database_list' => NULL,
  'db_before_query_handler' => 
  array (
    0 => 'MY\\Base\\App',
    1 => 'OnQuery',
  ),
  'db_close_at_output' => true,
  'db_close_handler' => NULL,
  'db_create_handler' => NULL,
  'db_exception_handler' => NULL,
  'default_exception_handler' => 
  array (
    0 => 'DuckPhp\\App',
    1 => 'OnDefaultException',
  ),
  'dev_error_handler' => 
  array (
    0 => 'DuckPhp\\App',
    1 => 'OnDevErrorHandler',
  ),
  'enable_cache_classes_in_cli' => false,
  'error_404' => '_sys/error_404',
  'error_500' => '_sys/error_500',
  'error_debug' => '_sys/error_debug',
  'error_exception' => '_sys/error_exception',
  'ext' => 
  array (
    'DuckPhp\\Ext\\Misc' => true,
    'DuckPhp\\Ext\\SimpleLogger' => true,
    'DuckPhp\\Ext\\DBManager' => true,
    'DuckPhp\\Ext\\RouteHookRewrite' => true,
    'DuckPhp\\Ext\\RouteHookRouteMap' => true,
    'DuckPhp\\Ext\\StrictCheck' => false,
    'DuckPhp\\Ext\\RouteHookOneFileMode' => false,
    'DuckPhp\\Ext\\RouteHookDirectoryMode' => false,

    'DuckPhp\\Ext\\Lazybones' => false,
    'DuckPhp\\Ext\\Pager' => false,
  ),
  'handle_all_dev_error' => true,
  'handle_all_exception' => true,
  'is_debug' => true,
  'log_file' => '',
  'log_prefix' => 'DuckPhpLog',
  'log_sql' => false,
  'namespace' => 'MY',
  'namespace_controller' => 'Controller',
  'override_class' => 'Base\\App',
  'path' => '/mnt/d/MyWork/sites/DNMVCS/template/',
  'path_config' => 'config',
  'path_lib' => 'lib',
  'path_namespace' => 'app',
  'path_view' => 'view',
  'path_view_override' => '',
  'platform' => '',
  'rewrite_map' => 
  array (
  ),
  'route_map' => 
  array (
  ),
  'route_map_important' => 
  array (
  ),
  'setting' => 
  array (
  ),
  'setting_file' => 'setting',
  'skip_404_handler' => false,
  'skip_app_autoload' => false,
  'skip_env_file' => true,
  'skip_exception_check' => false,
  'skip_fix_path_info' => false,
  'skip_plugin_mode_check' => false,
  'skip_setting_file' => true,
  'skip_system_autoload' => true,
  'skip_view_notice_error' => true,
  'system_exception_handler' => 
  array (
    0 => 'DuckPhp\\App',
    1 => 'set_exception_handler',
  ),
  'use_context_db_setting' => true,
  'use_flag_by_setting' => true,
  'use_short_function' => true,
  'use_short_functions' => true,
  'use_super_global' => false,
)
```

