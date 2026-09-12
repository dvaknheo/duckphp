# DuckPhp\GlobalAdmin\AdminLoginServiceInterface

## 简介

`AdminLoginServiceInterface` 是「管理员登录服务」契约接口，描述服务侧的登录/退出能力：`login(array $post)` 与 `logout()`。它是 `AdminLoginActionInterface` 的服务侧对应物，通常由工程里的 Service 类实现，并经 `GlobalAdmin` 的 `admin_callback_for_login_service` 选项挂载。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`interface AdminLoginServiceInterface`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminLoginServiceInterface;

class AdminLoginService implements AdminLoginServiceInterface
{
    public function login(array $post)
    {
        // 校验并返回管理员数据
    }
    public function logout()
    {
        // 退出清理
    }
}

// App 选项：
// 'admin_callback_for_login_service' => [AdminLoginService::class, 'login'], // 见 GlobalAdmin 文档
```

## 注意事项

- 该接口只声明登录/退出两个方法；权限判断、日志等仍属 `AdminServiceInterface`。
- 与 `AdminLoginActionInterface` 的方法签名完全一致，区别在语义定位（Action 面向动作层，Service 面向服务层）。

## 方法列表

### 公共方法

    public function login(array $post)
执行登录并返回管理员数据。

    public function logout()
执行退出登录。

## 相关链接

- [DuckPhp\GlobalAdmin\AdminLoginActionInterface](GlobalAdmin-AdminLoginActionInterface.md) — 动作侧同名契约
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — 权限/日志服务契约
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 使用方
