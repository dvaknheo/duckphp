# DuckPhp\Ext\MyFacadesBase

## 简介

`MyFacadesBase` 是 Facade（门面）类的基类：任何继承它的“门面类”，其未定义的静态调用都会进入 `__callStatic`，由 `MyFacadesAutoLoader` 解析出真实对象后转发执行（找不到目标时抛 `ErrorException("BadCall")`）。

与 `MyFacadesAutoLoader` 配合：自动加载器动态生成的类继承本类，因此门面调用链 = 门面类 → `MyFacadesBase::__callStatic` → `MyFacadesAutoLoader::getFacadesCallback` → 真实对象方法。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class MyFacadesBase extends DuckPhp\Core\ComponentBase`

## 使用方式

一般不需要直接写门面类——由 `MyFacadesAutoLoader` 自动生成：

```php
// 配置好 facades_map 后：
MyFacades\Mail::send($to, $body); // → MailService::_()->send(...)
```

若要手工定义门面，继承本类即可获得同样的静态转发能力。

## 注意事项

- `__callStatic` 通过 `MyFacadesAutoLoader::_()->getFacadesCallback(static::class, $name)` 解析；返回 `null` 时抛 `ErrorException("BadCall")`。
- 真实目标对象取的是 `$class::_()` 单例。

## 方法列表

### 公共方法

    public function __construct()
空构造器。

    public static function __callStatic($name, $arguments)
把未定义的静态调用转发给真实对象（经 MyFacadesAutoLoader 解析）。

## 相关链接

- [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) — 门面自动加载与解析
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 组件基类
