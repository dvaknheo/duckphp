# DuckPhp\GlobalUser\UserControllerInterface

## 简介

`UserControllerInterface` 是一个**空的标记接口**（marker interface，不声明任何方法）。工程中的“前台登录用户控制器”基类通过 `implements` 它来标识自己属于用户区（要求用户登录的控制器），便于框架按接口识别与约束。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`interface UserControllerInterface`（无方法）

## 使用方式

```php
namespace MyProject\Controller;

use DuckPhp\GlobalUser\UserControllerInterface;

class UserBase implements UserControllerInterface
{
    // 用 implements 标记：凡继承本类的控制器都算“用户控制器”
}
```

## 注意事项

- 本接口不提供方法；用户的会话/权限能力来自 `UserActionInterface` 与 `UserServiceInterface`。
- `Foundation\Controller\UserControllerBase` 即按此模式组织（细节见 Foundation 篇）。

## 方法列表

本接口为空标记接口，不声明任何方法。

## 相关链接

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — 用户动作接口
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 用户组件实现
