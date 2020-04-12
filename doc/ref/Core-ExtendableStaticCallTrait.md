# Core\ExtendableStaticCallTrait

## 简介

能扩展静态方法的 Trait 。

## 使用于
- 核心类 Core\App 

- 助手类 AppHelper 等

使用于各助手类和 App 类

## 方法

public static function AssignExtendStaticMethod($key, $value = null)

    分配静态方法。
public static function GetExtendStaticMethodList()
    
    获得已经扩展的静态方法列表。
public static function __callStatic($name, $arguments)    
    接管默认的魔术方法

protected static function CallExtendStaticMethod($name, $arguments)

    静态魔术方法的实质调用。
## 详解

ExtendableStaticCallTrait 这个 Trait 用于 App, ControllerHelper, ServiceHelper, ModelHelper, ViewHelper

作用是动态扩展类的静态方法。

你写自己的扩展的时候会用到。

如果一个类 use ExtendableStaticCallTrait . 你可以用 GetExtendStaticMethodList() 得到这个类有什么额外的静态方法。

如果你要给相应 trait 添加静态方法，使用

- AssignExtendStaticMethod($method,$callback);
- AssignExtendStaticMethod($assoc);

assoc 用于批量调用的数组

其中, $callback 为回调，额外的， $callback 还可以用 "MyClass::G::foo"  相当于回调 MyClass::G()->foo
