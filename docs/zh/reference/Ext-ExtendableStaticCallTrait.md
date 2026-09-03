# DuckPhp\Ext\ExtendableStaticCallTrait

## 简介

`ExtendableStaticCallTrait` 为类提供「外部扩展静态方法」的能力：通过 `AssignExtendStaticMethod()` 注册额外静态方法（键 → 回调），之后对类调用这些“未定义”的静态方法时，由魔术 `__callStatic` 转交给注册的回调执行。

回调可以是闭包/可调用数组，也支持两种字符串简写：`Class@method`（把 `Class` 经 `_()` 取单例）与 `Class->method`（`new Class` 后调用）。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`trait ExtendableStaticCallTrait`

## 使用方式

```php
class Demo
{
    use \DuckPhp\Ext\ExtendableStaticCallTrait;
}

// 注册扩展静态方法
Demo::AssignExtendStaticMethod('foo', function ($a) { return 'foo:'.$a; });
Demo::AssignExtendStaticMethod('bar', MyClass::class.'@baz'); // 或 'MyClass->baz'

echo Demo::foo(1);    // foo:1 （经 __callStatic → CallExtendStaticMethod）
```

## 注意事项

- 注册表按 `static::class` 分开存放（每个子类一份 `$static_methods`）。
- `AssignExtendStaticMethod($key, $value)`：`$key` 为数组且 `$value === null` 时按批量合并；否则单键赋值。
- 字符串回调 `Class@method` 使用 `$class::_()`（单例）；`Class->method` 使用 `new $class()`。
- 注册的回调找不到时，`call_user_func_array(null, …)` 会抛错——确保调用的方法已注册。

## 方法列表

### 公共方法

    public static function AssignExtendStaticMethod($key, $value = null)
注册一个（或批量）扩展静态方法。

    public static function GetExtendStaticMethodList()
返回当前类已注册的扩展静态方法表。

    public static function __callStatic($name, $arguments)
魔术：把未定义的静态调用转给 `CallExtendStaticMethod`。

### 受保护方法

    protected static function CallExtendStaticMethod($name, $arguments)
按名查注册表并解析回调（支持 `@`/`->` 简写）后调用。

## 相关链接

- [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md) — 基于静态转发思想的门面基类（相关设计）
