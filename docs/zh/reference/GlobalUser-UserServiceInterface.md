# DuckPhp\GlobalUser\UserServiceInterface

用户服务接口。

## 简介

`UserServiceInterface` 定义了用户服务的日志记录、批量获取用户名和权限检查方法。实现此接口的类作为 `GlobalUserTrait::log()` / `batchGetUsernames()` / `checkAccess()` 的委托目标。

自定义用户服务时，优先实现此接口并通过 `localService()` 返回。

## 接口定义

```php
namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    public function doLog(int $user_id, string $string, ?string $type = null): void;
    public function doBatchGetUsernames(array $ids): array;
    public function doCheckAccess(int $id, string $class, string $method, ?string $url = null): void;
}
```

## 方法说明

| 方法 | 说明 |
|---|---|
| `doLog(int $user_id, string $string, ?string $type = null): void` | 记录指定用户的操作日志 |
| `doBatchGetUsernames(array $ids): array` | 批量查询用户 ID 对应的用户名，返回 `[id => name]` 格式数组 |
| `doCheckAccess(int $id, string $class, string $method, ?string $url = null): void` | 检查是否有权限。无权限时抛出异常 |

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md)
