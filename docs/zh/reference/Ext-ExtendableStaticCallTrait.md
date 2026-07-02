# DuckPhp\Ext\ExtendableStaticCallTrait

> ⚠️ 警告：该扩展是实验性的或已废弃，不建议在新项目中使用。

## 简介

`ExtendableStaticCallTrait` 是一个 Trait，用于为类提供可扩展的静态方法调用能力。它允许在运行时为类动态分配静态方法，并通过 `__callStatic` 魔术方法调用这些方法。

该方法主要用于为 facade 或 helper 类提供静态调用扩展点，但实现较为复杂，且容易引入隐藏依赖，因此不建议在新项目中使用。

## 选项

无。

## 使用方式

### 在类中使用 Trait

```php
use DuckPhp\Ext\ExtendableStaticCallTrait;

class MyHelper
{
    use ExtendableStaticCallTrait;
}
```

### 分配扩展方法

```php
use DuckPhp\Ext\ExtendableStaticCallTrait;

class MyHelper
{
    use ExtendableStaticCallTrait;
}

MyHelper::AssignExtendStaticMethod('hello', function ($name) {
    return "Hello, {$name}";
});

MyHelper::AssignExtendStaticMethod([
    'foo' => function () { return 'foo'; },
    'bar' => function () { return 'bar'; },
]);
```

### 使用字符串回调

```php
MyHelper::AssignExtendStaticMethod('foo', 'FooService@doFoo'); // 调用 FooService::_()->doFoo(...)
MyHelper::AssignExtendStaticMethod('bar', 'BarService->doBar'); // 调用 (new BarService())->doBar(...)

MyHelper::foo();
MyHelper::bar();
```

### 获取已分配的扩展方法

```php
$methods = MyHelper::GetExtendStaticMethodList();
```

## 配置示例

无。

## 注意事项

1. 扩展方法以静态方式存储，按调用类的实际类名隔离。
2. 字符串回调支持 `Class@method` 和 `Class->method` 两种格式，分别解析为单例调用和实例调用。
3. 如果方法不存在或回调无效，调用时会触发 PHP 错误。
4. 该 Trait 会改变类的静态方法解析行为，应谨慎使用。

## 方法列表

### 公共方法

    public static function AssignExtendStaticMethod($key, $value = null)
为当前类分配一个或多个扩展静态方法。`$key` 为数组时批量分配。

    public static function GetExtendStaticMethodList()
获取当前类已分配的所有扩展静态方法。

    public static function __callStatic($name, $arguments)
拦截未定义的静态方法调用，并路由到已分配的扩展方法。

### 受保护方法

    protected static function CallExtendStaticMethod($name, $arguments)
解析并执行对应的扩展回调。支持字符串形式的 `Class@method` 和 `Class->method` 解析。

## 相关链接

- [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md)
