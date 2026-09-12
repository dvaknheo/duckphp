# DuckPhp\GlobalUser\UserLoginServiceInterface

## 简介

`UserLoginServiceInterface` 是「用户登录服务」契约接口，描述服务侧的注册/登录/退出：`register(array $post)`、`login(array $post)`、`logout()`。它是 `UserLoginActionInterface` 的服务侧对应物，通常由工程的 Service 类实现，并经 `GlobalUser` 的 `user_callback_for_login_service` 选项挂载。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`interface UserLoginServiceInterface`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserLoginServiceInterface;

class UserLoginService implements UserLoginServiceInterface
{
    public function register(array $post) { /* 返回用户数据 */ }
    public function login(array $post)    { /* 返回用户数据 */ }
    public function logout()              { /* 退出清理 */ }
}
```

## 注意事项

- 本接口只声明注册/登录/退出；权限判断、日志、批量取用户名等仍属 `UserServiceInterface`。
- 与 `UserLoginActionInterface` 方法签名一致，区别在语义定位。

## 方法列表

### 公共方法

    public function register(array $post)
执行注册并返回用户数据。

    public function login(array $post)
执行登录并返回用户数据。

    public function logout()
执行退出登录。

## 相关链接

- [DuckPhp\GlobalUser\UserLoginActionInterface](GlobalUser-UserLoginActionInterface.md) — 动作侧同名契约
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — 权限/日志/批量取名服务契约
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 使用方
