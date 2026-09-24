# DuckPhp\GlobalUser\UserServiceInterface

## 简介

`UserServiceInterface` 是"用户服务"的契约接口，定义前台服务侧需要实现的三件事：权限判断（`canAccess`）、操作日志（`log`）、批量取用户名（`batchGetUsernames`）。`UserActionInterface::service()` 返回的对象即实现本接口。

与管理员侧 `AdminServiceInterface` 相比：没有 `isSuper`，多了一个 `batchGetUsernames()`。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`interface UserServiceInterface`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function canAccess($user_id, ?string $url, string $class, string $method): bool
    {
        // 返回该用户能否访问 url/controller/method
    }
    public function log($user_id, string $string, ?string $type = null, array $ext = [])
    {
        // 记录用户操作
    }
    public function batchGetUsernames(array $ids): array
    {
        // 返回 id => 用户名
    }
}
```

## 注意事项

- `canAccess` 的 `$user_id` 可为 `int|string`；`$url` 为可空的 URL。
- 参数顺序与 `UserActionInterface::canAccess()` 一致：**`$url` 在 `$class`/`$method` 之前**。这是破坏性变更：旧代码若按 `$class, $method, $url` 顺序传实参会静默错位，务必检查调用处。
- "当前用户是谁"的查询属于 `UserActionInterface::id()/name()/data()`；本接口方法都以 `$user_id` 显式传入。

## 方法列表

### 公共方法

    public function canAccess($user_id, ?string $url, string $class, string $method): bool
判断指定用户能否访问某 URL/控制器类/方法。参数顺序与 `UserActionInterface::canAccess()` 一致：url 在前。

    public function log($user_id, string $string, ?string $type = null, array $ext = [])
记录指定用户的一条操作日志。

    public function batchGetUsernames(array $ids): array
按 ID 批量取用户名（返回映射数组）。

## 相关链接

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — 返回本服务接口的动作契约
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 用户组件（含 service() 跨 Phase 代理）
