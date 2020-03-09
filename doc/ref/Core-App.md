# Core\App

## 简介
最核心的类，其他
## 选项

    //// basic config ////
    'path' => null,
    'namespace' => 'MY',
    'path_namespace' => 'app',
    
    'is_debug' => false,
    'platform' => '',
    'ext' => [],
    
    'override_class' => 'Base\App',
    'reload_for_flags' => true,
    'use_super_global' => false,
    'handle_all_dev_error' => true,
    'handle_all_exception' => true,
    'error_404' => null,          //'_sys/error-404',
    'error_500' => null,          //'_sys/error-500',
    'error_debug' => null,        //'_sys/error-debug',

    'skip_view_notice_error' => true,
    'skip_404_handler' => false,
    'skip_plugin_mode_check' => false,
    'skip_exception_check' => false,
    'skip_fix_path_info' => false,
    'skip_app_autoload' => false,
    
## 公开方法
public static function RunQuickly(array $options=[], callable $after_init=null): bool

    //
public function init(array $options=[], object $context=null)

    //
public function run(): bool

    //
public function clear(): void

    //
public function cleanAll()

    //
public function addBeforeShowHandler($handler)

    //
public function extendComponents($class, $methods, $components): void

    //

    //
public static function On404(): void

    //
public static function OnException($ex): void

    //
public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void

## 详解
Core\App 类 可以视为几个类的组合

### 作为内核的 App 入口类

### 作为 500,404 处理的 trait

### 覆盖系统的 core_systemwrapper

### 助手类
相关代码请参考 
 + AppHelper
 + XHelper

#### Core_Component
