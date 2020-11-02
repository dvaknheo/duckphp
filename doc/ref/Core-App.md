# DuckPhp\Core\App
[toc]

## 简介
核心的类,`组件类`
## 依赖关系
+ `DuckPhp\Core\App` 
    + [DuckPhp\Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
    + [DuckPhp\Core\SystemWrapperTrait](Core-SystemWrapperTrait.md)
    + [Functions.php](Core-Functions.md)
    + Trait [DuckPhp\Core\Kernel](Core-Kernel.md)


## 选项
使用 [DuckPhp\Core\Kernel](Core-Kernel.md) 的默认选项。

并且有：

   [0] =>
    string(14) "use_autoloader"
    [1] =>
    string(22) "skip_plugin_mode_check"
    [2] =>
    string(4) "path"
    [3] =>
    string(14) "override_class"
    [4] =>
    string(8) "is_debug"
    [5] =>
    string(8) "platform"
    [6] =>
    string(19) "use_flag_by_setting"
    [7] =>
    string(16) "use_super_global"
    [8] =>
    string(19) "use_short_functions"
    [9] =>
    string(16) "skip_404_handler"
    [10] =>
    string(20) "skip_exception_check"


###
'default_exception_do_log' => true,
'default_exception_self_display' => true,
'close_resource_at_output' => false,
    
### 错误处理配置

'error_404' => null,          //'_sys/error-404',

    404 的View或者回调
'error_500' => null,          //'_sys/error-500',

    500 的View或者回调
'error_debug' => null,        //'_sys/error-debug',

    404 的View或者回调
## 方法


### 独有的静态方法

  0 => string 'G' (length=1)
  1 => string 'RunQuickly' (length=10)
  2 => string 'Blank' (length=5)
  3 => string 'system_wrapper_replace' (length=22)
  4 => string 'system_wrapper_get_providers' (length=28)
  5 => string 'On404' (length=5)
  6 => string 'OnDefaultException' (length=18)
  7 => string 'OnDevErrorHandler' (length=17)
  8 => string 'Route' (length=5)



public function extendComponents($class, $methods, $components): void

    //
public static function On404(): void

    //
public static function CallException($ex): void

    //
public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void


    //
public function getStaticComponentClasses()

    //
public function getDynamicComponentClasses()

    //
public function addDynamicComponentClass($class)

    //
public function removeDynamicComponentClass($class)

    //

## 详解
DuckPhp\Core\App 类 可以视为几个类的组合

### 作为内核的 App 入口类

### 作为 500,404 处理的 trait

### 覆盖系统的 core_systemwrapper

### 助手类
相关代码请参考 
 + HelperTrait
 + AppHelper
 + ControllerHelper
 + ModelHelper
 + ViewHelper
 + BusinessHelper

 ## 其他独特方法
 


 
 ## 方法索引


    public function __construct()
    public function extendComponents($method_map, $components = []): void
    public function cloneHelpers($new_namespace, $componentClassMap = [])
    
    public static function On404(): void
    public static function OnDefaultException($ex): void
    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void

    public function getStaticComponentClasses()
    public function getDynamicComponentClasses()
    public function addDynamicComponentClass($class)
    public function removeDynamicComponentClass($class)