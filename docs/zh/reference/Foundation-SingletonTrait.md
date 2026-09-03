# DuckPhp\Foundation\SingletonTrait

## 简介

`SingletonTrait` 是 Foundation 层对 `Core\SingletonExTrait` 的**薄封装**：把框架核心的单例入口以 `Foundation` 命名空间下的名字再暴露一次，供 Foundation 各基类（`Controller\Base`、`Business\Base`、`Model\Base` 等）统一引入。

其实现即 `use DuckPhp\Core\SingletonExTrait as Singleton; use Singleton;` —— 语义与 `Core\SingletonExTrait` 完全一致（`类名::_()` 走 `PhaseContainer::GetObject`）。

## 类信息

- 命名空间：`DuckPhp\Foundation`
- 声明：`trait SingletonTrait`
- 使用的 Trait：`DuckPhp\Core\SingletonExTrait`（别名 `Singleton`）

## 使用方式

```php
namespace MyProject;

use DuckPhp\Foundation\SingletonTrait;

class AnyClass
{
    use SingletonTrait;
}
$obj = AnyClass::_(); // 单例式取实例
```

## 注意事项

- 本 Trait 只是转发，没有额外状态；如果已经在用 `Core\SingletonExTrait`，功能等价。
- 为什么存在？让 Foundation 层代码不直接依赖 `Core` 命名空间的写法更统一（分层约定：业务类尽量经 Foundation 引入能力）。

## 方法列表

本 Trait 未自行声明方法：`_($object = null)` 由 `Core\SingletonExTrait` 提供（组合后即可 `类名::_()` 取实例）。

## 相关链接

- [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) — 实际实现
- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) — 单例存放容器
