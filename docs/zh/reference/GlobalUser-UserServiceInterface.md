# DuckPhp\GlobalUser\UserServiceInterface

用户服务接口。

## 简介

`UserServiceInterface` 定义了无状态的用户服务方法：权限检查、日志记录和批量查询。实现此接口的类作为 `GlobalUser::localService()` 的返回值。

## 接口定义

```php
namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    public function checkAccess($user_id, string $class, string $method, ?string $url = null);
    public function log($user_id, string $string, ?string $type = null, array $ext = []);

    public function batchGetUsernames(array $ids): array;
}
```

## 方法说明

| 方法 | 说明 |
|---|---|
| `checkAccess($user_id, $class, $method, $url)` | 检查指定用户是否有权限访问。无权限时抛出异常 |
| `log($user_id, $string, $type, $ext)` | 记录用户操作日志。`$type` 为日志分类，`$ext` 为扩展数据 |
| `batchGetUsernames($ids): array` | 批量查询用户 ID 对应的用户名，返回 `[id => name]` 格式数组 |

## 使用方式

作为 `GlobalUser::localService()` 回调的返回值：

```php
'user_callback_get_service' => [UserService::class, 'service'],
```

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md)
