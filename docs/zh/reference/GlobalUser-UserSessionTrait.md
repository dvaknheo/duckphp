# DuckPhp\GlobalUser\UserSessionTrait

## 简介

`UserSessionTrait` 是 `UserSessionInterface` 的默认实现 Trait：与提供 `get()/set()` 会话读写的宿主组合（典型是同时 `use Foundation\SessionTrait`）后，即得到“把当前用户存于会话”的能力。

它以键 `'user'` 存放用户数组，派生 id/name 便捷读取；`unsetCurrentUser()` 以写入空数组实现退出。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`trait UserSessionTrait`
- 依赖宿主提供：`get(string $key, $default = null)`、`set(string $key, $value)`（如 `Foundation\SessionTrait`）

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalUser\UserSessionTrait;

class UserSession
{
    use SessionTrait;      // get/set/unset
    use UserSessionTrait;  // 本 Trait
    // 现在具备 getCurrentUserId()/getCurrentUserName()/getCurrentUser()/setCurrentUser()/unsetCurrentUser()
}
```

## 注意事项

- 会话键固定为 `'user'`；存储前缀由宿主会话实现（如 `session_prefix`）决定。
- `getCurrentUserId()` 缺省 `0`，`getCurrentUserName()` 缺省 `''`。
- `setCurrentUser($user)` 写入传入值（登录/注册时应写含 `id`/`name` 的数组）。

## 方法列表

### 公共方法

    public function getCurrentUserId()
读当前用户 ID（会话 `user.id`，缺省 `0`）。

    public function getCurrentUserName(): string
读当前用户名（会话 `user.name`，缺省 `''`）。

    public function getCurrentUser(): array
读当前用户数组（会话 `user`，缺省 `[]`）。

    public function setCurrentUser($user)
把用户数据写入会话。

    public function unsetCurrentUser()
清除当前用户（写入 `[]`）。

## 相关链接

- [DuckPhp\GlobalUser\UserSessionInterface](GlobalUser-UserSessionInterface.md) — 本 Trait 实现的接口
- [DuckPhp\Foundation\Controller\SessionTrait](Foundation-Controller-SessionTrait.md) — 提供 `get/set` 的会话基座
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 使用会话的组件
