# DuckPhp\Foundation\Controller\ActionBase

## 简介

`ActionBase` 是工程「Action 复用类」的推荐基类（abstract）。在 DuckPHP 的分层约定里，控制器内可复用的“动作片段”可抽成 Action 类；`ActionBase` 与控制器基类 `Controller\Base` 一样，只提供 `use SingletonTrait` 的单例访问。

## 类信息

- 命名空间：`DuckPhp\Foundation\Controller`
- 声明：`abstract class ActionBase`
- 使用的 Trait：`DuckPhp\Foundation\SingletonTrait`

## 使用方式

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\ActionBase;

class LoginAction extends ActionBase
{
    public function doLogin($name, $password)
    {
        // 复用的“动作”逻辑
    }
}
// 其它控制器内：LoginAction::_()->doLogin(...)
```

## 注意事项

- 本类不强制任何方法签名；是否把 `do_*` 作为动作前缀由路由选项决定（`Route` 的 `controller_prefix_post` 等）。
- 与 `Controller\Base` 的区别是命名与用途约定：Base 面向控制器主体，ActionBase 面向可复用动作。

## 方法列表

本类为空抽象基类，未额外声明方法（`_()` 来自 SingletonTrait）。

## 相关链接

- [DuckPhp\Foundation\Controller\Base](Foundation-Controller-Base.md) — 控制器基类
- [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) — 单例入口来源
