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

### 专有选项
'default_exception_do_log' => true,

    发生异常时候记录日志
'default_exception_self_display' => true,

    发生异常的时候如有可能，调用异常类的 display() 方法。
'close_resource_at_output' => false,
    
    输出时候关闭资源输出（仅供第三方扩展参考
### 错误处理配置

'error_404' => null,          //'_sys/error-404',

    404 的View或者回调
'error_500' => null,          //'_sys/error-500',

    500 的View或者回调
'error_debug' => null,        //'_sys/error-debug',

    404 的View或者回调
    
### 扩展 [DuckPhp\Core\Kernel](Core-Kernel.md) 的默认选项。
详情见 DuckPhp\Core\Kernel 参考文档

"use_autoloader"
"skip_plugin_mode_check"
"path"
"override_class"
"is_debug"
"platform"
"use_flag_by_setting"
"use_super_global"
"use_short_functions"
"skip_404_handler"
"skip_exception_check"

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


public static function On404(): void

    //
public static function CallException($ex): void

    //
public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void


    //

## 详解
DuckPhp\Core\App 类 可以视为几个类的组合

### 作为内核的 App 入口类

### 作为 500,404 处理的 trait

### 覆盖系统默认函数的 core_systemwrapper

### 助手类
相关代码请参考 
 + HelperTrait
 + AppHelper
 + ControllerHelper
 + ModelHelper
 + ViewHelper
 + BusinessHelper

 ## 其他独特方法
 


 
 ## 全方法索引
 
 @autogen:
