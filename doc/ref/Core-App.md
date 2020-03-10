# Core\App

## 简介
最核心的类，其他
## 选项

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

