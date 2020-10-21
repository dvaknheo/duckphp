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
    
'default_exception_do_log' => true,
'default_exception_self_display' => true,
'close_resource_at_output' => false,
    

## 方法

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