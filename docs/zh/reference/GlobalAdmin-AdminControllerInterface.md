# DuckPhp\GlobalAdmin\AdminControllerInterface

## 简介

`AdminControllerInterface` 是一个**空的标记接口**（marker interface，不声明任何方法）。工程中的“后台管理员控制器”基类通过 `implements` 它来标识自己属于 Admin 区域，便于框架按接口识别/约束管理员控制器。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`interface AdminControllerInterface`（无方法）

## 使用方式

```php
namespace MyProject\Controller;

use DuckPhp\GlobalAdmin\AdminControllerInterface;

class AdminBase implements AdminControllerInterface
{
    // 用 implements 标记：凡继承本类的控制器都算“管理员控制器”
}
```

## 注意事项

- 本接口不提供方法，实际管理员的会话/权限能力来自 `AdminActionInterface` 与 `AdminServiceInterface`。
- `Foundation\Controller\AdminControllerBase` 即按此模式组织（细节见 Foundation 篇）。

## 方法列表

本接口为空标记接口，不声明任何方法。

## 相关链接

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — 管理员动作接口
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 管理员组件实现
