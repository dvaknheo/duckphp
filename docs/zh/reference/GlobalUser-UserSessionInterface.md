# DuckPhp\GlobalUser\UserSessionInterface

## 简介

`UserSessionInterface` 是「用户会话」契约接口：把“当前用户”存入/取出会话，共 5 个方法：`setCurrentUser($user)`、`unsetCurrentUser()`、`getCurrentUser()`、`getCurrentUserName()`、`getCurrentUserId()`。

它与 `UserSessionTrait`（默认实现，基于宿主 `get()/set()`）配套；`GlobalUser` 通过 `user_callback_for_session` 选项获取本接口实现，以“会话”方式读取当前用户。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`interface UserSessionInterface`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserSessionInterface;
use DuckPhp\GlobalUser\UserSessionTrait;
use DuckPhp\Foundation\SessionTrait;

class UserSession implements UserSessionInterface
{
    use SessionTrait;      // get/set/unset（带 session_prefix）
    use UserSessionTrait;  // 实现本接口
}
```

## 注意事项

- `GlobalUser` 检测到配置了 `user_callback_for_session` 时，`id()/name()` 会优先走会话路径。
- `getCurrentUser()` 约定返回用户数组（含 `id`/`name` 等）；另有 id/name 便捷读法。

## 方法列表

### 公共方法

    public function setCurrentUser($user)
写入当前用户（通常登录/注册成功后）。

    public function unsetCurrentUser()
清除当前用户（退出登录）。

    public function getCurrentUser()
读取当前用户（数组）。

    public function getCurrentUserName()
读取当前用户名。

    public function getCurrentUserId()
读取当前用户 ID。

## 相关链接

- [DuckPhp\GlobalUser\UserSessionTrait](GlobalUser-UserSessionTrait.md) — 默认实现
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 通过 `user_callback_for_session` 使用本接口
