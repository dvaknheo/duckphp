# DuckPhp\GlobalAdmin\AdminServiceInterface

管理员服务接口。

## 简介

`AdminServiceInterface` 定义了无状态的管理员服务方法：权限检查、日志记录和超级管理员判断。实现此接口的类作为 `GlobalAdmin::localService()` 的返回值。

## 接口定义

```php
namespace DuckPhp\GlobalAdmin;

interface AdminServiceInterface
{
    public function checkAccess($admin_id, string $class, string $method, ?string $url = null);
    public function log($admin_id, string $string, ?string $type = null, array $ext = []);

    public function isSuper($admin_id): bool;
}
```

## 方法说明

| 方法 | 说明 |
|---|---|
| `checkAccess($admin_id, $class, $method, $url)` | 检查指定管理员是否有权限。无权限时抛出异常 |
| `log($admin_id, $string, $type, $ext)` | 记录管理员操作日志。`$type` 为日志分类，`$ext` 为扩展数据 |
| `isSuper($admin_id): bool` | 判断指定管理员 ID 是否为超级管理员 |

> 与 `UserServiceInterface` 的区别：没有 `batchGetUsernames()`，增加了 `isSuper()`。

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md)
