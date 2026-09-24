# DuckPhp\GlobalUser\UserLoginServiceInterface

## 简介

`UserLoginServiceInterface` 是「用户登录服务」契约接口，描述服务侧的注册/登录/退出：`register(array $post)`、`login(array $post)`、`logout($id)`。它是 `UserLoginActionInterface` 的服务侧对应物，通常由工程的 Service 类实现，并经 `GlobalUser` 的 `user_callback_for_login_service` 选项挂载。

用户侧比管理侧多一个 `register(array $post)` 方法。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`interface UserLoginServiceInterface`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserLoginServiceInterface;

class UserLoginService implements UserLoginServiceInterface
{
    public function register(array $post) { /* 执行注册并返回用户数据 */ }
    public function login(array $post)    { /* 执行登录并返回用户数据 */ }
    public function logout($id)          { /* 退出清理，$id 为用户 ID */ }
}
```

## 注意事项

- 本接口只声明注册/登录/退出；权限判断、日志、批量取用户名等仍属 `UserServiceInterface`。
- `logout($id)` 的 `$id` 参数为 `int|string` 类型，表示要登出的用户 ID。

## 方法列表

### 公共方法

    public function register(array $post)
执行注册并返回用户数据。

    public function login(array $post)
执行登录并返回用户数据。

    public function logout($id)
执行退出登录；`$id` 为要登出的用户 ID（`int|string`）。

## 相关链接

- [DuckPhp\GlobalUser\UserLoginActionInterface](GlobalUser-UserLoginActionInterface.md) — 动作侧同名契约
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — 权限/日志/批量取名服务契约
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 使用方
