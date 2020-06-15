# Core\App

## 简介
最核心的类，其他
## 依赖关系
+ `Core\App` 
    + Trait [Core\Kernel](Core-Kernel.md)
    + Trait [Core\ThrowOn](Core-ThrowOn.md)
    + Trait [Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
    + Trait [Core\SystemWrapperTrait](Core-SystemWrapperTrait.md)

## 选项
使用 [Core\Kernel](Core-Kernel.md) 的默认选项。

## 方法
public function addBeforeShowHandler($handler)

    挂接在显示前输入的方法。
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
Core\App 类 可以视为几个类的组合

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
 + ServiceHelper

 
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