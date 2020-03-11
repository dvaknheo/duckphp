# Core\ExtendableStaticCallTrait

## 简介

能扩展静态方法的 Trait 。

## 使用于
- 核心类 Core\App 
- 助手类 AppHelper 等
使用于各助手类和 App 类

## 公开静态方法

AssignExtendStaticMethod($key, $value=null)

    分配静态方法。
GetExtendStaticStaticMethodList()
    
    获得已经扩展的静态方法列表。
__callStatic($name, $arguments)
    
    接管默认的魔术方法
## 详解

ExtendableStaticCallTrait 这个 Trait 用于 App, ControllerHelper, ServiceHelper, ModelHelper, ViewHelper

作用是动态扩展类的静态方法。

你写自己的扩展的时候会用到。