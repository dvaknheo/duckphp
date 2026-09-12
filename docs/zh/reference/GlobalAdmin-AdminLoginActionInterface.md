# DuckPhp\GlobalAdmin\AdminLoginActionInterface

## 简介

`AdminLoginActionInterface` 是「管理员登录动作」契约接口，与 `AdminActionInterface` 分离，专门描述登录/退出相关的动作：`login(array $post)` 与 `logout()`。

框架的 `GlobalAdmin` 同时实现 `AdminActionInterface` 与本接口；工程里也可以把登录逻辑单独实现为本接口（如 `AdminLoginAction`），再通过 `admin_callback_for_login_service` 选项接给 `GlobalAdmin::login()/logout()` 使用。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`interface AdminLoginActionInterface`
- 实现方：`DuckPhp\GlobalAdmin\GlobalAdmin`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminLoginActionInterface;

class AdminLoginAction implements AdminLoginActionInterface
{
    public function login(array $post)
    {
        // 校验账号密码等；返回管理员数据（将写入会话）
        return ['id' => 1, 'name' => 'root'];
    }
    public function logout()
    {
        // 可选的清理逻辑
    }
}
```

> 也可实现 `AdminLoginServiceInterface`（`login/logout` 同名方法）作为服务侧实现，经 `admin_callback_for_login_service` 挂载。

## 注意事项

- `login(array $post)` 通常返回“当前管理员数据”，由调用方（如 `GlobalAdmin::login()`）写入会话。
- `AdminActionInterface` 中保留了 `login/logout` 的注释占位，实际已迁移到本接口。

## 方法列表

### 公共方法

    public function login(array $post)
执行登录（输入为表单/POST 数据数组）。

    public function logout()
执行退出登录。

## 相关链接

- [DuckPhp\GlobalAdmin\AdminLoginServiceInterface](GlobalAdmin-AdminLoginServiceInterface.md) — 服务侧同名契约
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — 管理员动作契约
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 默认实现
