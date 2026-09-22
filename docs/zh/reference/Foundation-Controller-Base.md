# DuckPhp\Foundation\Controller\Base

## 简介

`Foundation\Controller\Base` 是工程「控制器层」的推荐基类（abstract）。源码极简：只有 `use SingletonTrait`，即让控制器类拥有 `类名::_()` 式的单例访问。

工程中你的控制器基类应继承它（或直接继承它以共享该模式），控制器动作仍按 `action_xxx()` 等路由约定书写；本类不绑定任何输入/输出能力——那由 [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) 提供。

## 类信息

- 命名空间：`DuckPhp\Foundation\Controller`
- 声明：`abstract class Base`
- 使用的 Trait：`DuckPhp\Foundation\SingletonTrait`

## 使用方式

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\Base;

abstract class MyControllerBase extends Base
{
    // 子类即可用 static::_() 取实例
}
```

## 注意事项

- 这是“示例工程分层”里的基础：真正“登录/后台/无权限处理”的控制器基类见 `AdminControllerBase`/`UserControllerBase`；`Controller\Helper` 提供静态方法。
- 本类未定义路由方法；业务动作由具体控制器按框架路由约定提供。

## 方法列表

本类为空抽象基类，未额外声明方法（`_()` 由 SingletonTrait → `Core\SingletonExTrait` 提供）。

## 相关链接

- [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) — 单例入口来源
- [DuckPhp\Foundation\Controller\AdminControllerBase](Foundation-Controller-AdminControllerBase.md) / [UserControllerBase](Foundation-Controller-UserControllerBase.md) — 需登录的控制器基类
- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — 控制器静态助手
