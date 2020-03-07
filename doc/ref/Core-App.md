# Core\App

## 简介
最核心的类，其他
## 选项

    //// basic config ////
    'path' => null,
    'namespace' => 'MY',
    'path_namespace' => 'app',
    
    //// properties ////
    'is_debug' => false,
    'platform' => '',
    'ext' => [],
    
    'override_class' => 'Base\App',
    'reload_for_flags' => true,
    'use_super_global' => false,
    'skip_view_notice_error' => true,
    'skip_404_handler' => false,
    'skip_plugin_mode_check' => false,
    'skip_exception_check' => false,
    'skip_fix_path_info' => false,
    
    //// error handler ////
    'handle_all_dev_error' => true,
    'handle_all_exception' => true,
    'error_404' => null,          //'_sys/error-404',
    'error_500' => null,          //'_sys/error-500',
    'error_debug' => null,        //'_sys/error-debug',
    
    //// Class Autoloader ////
    // 'path'=>null,
    // 'namespace'=>'MY',
    // 'path_namespace'=>'app',
    // 'skip_system_autoload'=>true,
    'skip_app_autoload' => false,
    //'enable_cache_classes_in_cli'=>true,

    //// Class Configer ////
    // 'path'=>null,
    // 'path_config'=>'config',
    // 'all_config'=>[],
    // 'setting'=>[],
    // 'setting_file'=>'setting',
    // 'skip_setting_file'=>false,
    
    //// Class View Class ////
    // 'path'=>null,
    // 'path_view'=>'view',
    

    //// Class Route ////
    // 'path'=>null,
    // 'namespace'=>'MY',
    // 'namespace_controller'=>'Controller',
    // 'controller_base_class'=>null,
    // 'controller_welcome_class'=>'Main',
    // 'controller_hide_boot_class'=>false,
    // 'controller_methtod_for_miss'=>null,
    // 'controller_prefix_post'=>'do_',
    // 'controller_postfix'=>'',
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
public function getStaticComponentClasses()

    //
public function getDynamicComponentClasses()

    //
public function addDynamicComponentClass($class)

    //
public function deleteDynamicComponentClass($class)

    //
public static function On404(): void

    //
public static function OnException($ex): void

    //
public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void

    //
public static function header($output, bool $replace = true, int $http_response_code=0)

    //
public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = ### ', string $domain  = '', bool $secure = false, bool $httponly = false)

    //
public static function &GLOBALS($k, $v=null)

    //
public static function &STATICS($k, $v=null, $_level=1)

    //
public static function &CLASS_STATICS($class_name, $var_name)

    //
public static function session_start(array $options=[])

    //
public static function session_id($session_id=null)

    //
public static function session_destroy()

    //
public static function session_set_save_handler(\SessionHandlerInterface $handler)

## 详解
Core\App 类 可以视为几个类的组合

### 作为内核的 App 入口类

### 作为 500,404 处理的 trait

### 覆盖系统的 core_systemwrapper

### 助手类
### Core_Glue

#### Core_Component

