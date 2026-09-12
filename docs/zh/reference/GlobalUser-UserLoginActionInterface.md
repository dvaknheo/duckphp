# DuckPhp\GlobalUser\UserLoginActionInterface

## 简介

`UserLoginActionInterface` 是「用户登录动作」契约接口，与 `UserActionInterface` 分离，描述注册/登录/退出：`register(array $post)`、`login(array $post)`、`logout()`。

框架的 `GlobalUser` 同时实现 `UserActionInterface` 与本接口；工程里也可把登录逻辑单独实现为本接口，再经 `user_callback_for_login_service` 选项供 `GlobalUser::register()/login()/logout()` 调用。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`interface UserLoginActionInterface`
- 实现方：`DuckPhp\GlobalUser\GlobalUser`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserLoginActionInterface;

class UserLoginAction implements UserLoginActionInterface
{
    public function register(array $post)
    {
        // 注册并返回用户数据（将写入会话）
        return ['id' => 7, 'name' => 'duck'];
    }
    public function login(array $post)
    {
        // 登录并返回用户数据
    }
    public function logout()
    {
        // 退出清理
    }
}
```

## 注意事项

- `register()/login()` 通常返回“当前用户数据”，由调用方写入会话。
- 与 `UserLoginServiceInterface` 方法签名一致，区别在语义定位（动作层 / 服务层）。

## 方法列表

### 公共方法

    public function register(array $post)
执行注册（输入为表单/POST 数据数组）。

    public function login(array $post)
执行登录。

    public function logout()
执行退出登录。

## 相关链接

- [DuckPhp\GlobalUser\UserLoginServiceInterface](GlobalUser-UserLoginServiceInterface.md) — 服务侧同名契约
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — 用户动作契约
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 默认实现
