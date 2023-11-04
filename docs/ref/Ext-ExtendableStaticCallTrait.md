# DuckPhp\Core\ExtendableStaticCallTrait
[toc]

## 简介

能扩展静态方法的 Trait 。作用是动态扩展类的静态方法，你写自己的扩展的时候会用到。
## 使用于
核心类和全部助手类
- [DucPhp\Core\App](Core-App.md)
- [DucPhp\Helper\AdvanceHelper](Helper-AdvanceHelper.md)
- [DucPhp\Helper\BusinessHelper](Helper-BusinessHelper.md)
- [DucPhp\Helper\ModelHelper](Helper-ModelHelper.md)
- [DucPhp\Helper\ViewHelper](Helper-ViewHelper.md)

## 方法
全部方法如下

    public static function AssignExtendStaticMethod($key, $value = null)
    public static function AssignExtendStaticMethod($assoc)
分配静态方法。第二种模式 assoc 用于批量调用的数组
其中, $value 为回调。 额外的， $value 还可以用 "MyClass@foo"  相当于回调 MyClass::G()->foo。

    public static function GetExtendStaticMethodList()
获得已经扩展的静态方法列表。
如果一个类 use ExtendableStaticCallTrait . 你可以用 GetExtendStaticMethodList() 得到这个类有什么额外的静态方法。

    public static function __callStatic($name, $arguments)
接管默认的魔术方法

    protected static function CallExtendStaticMethod($name, $arguments)
静态魔术方法的实质调用。你可能会重写他。
## 说明
无额外说明。

## 文档信息
修订版本：

修订时间：








