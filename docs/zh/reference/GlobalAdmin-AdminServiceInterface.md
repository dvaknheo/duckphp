# DuckPhp\GlobalAdmin\AdminServiceInterface

管理员服务接口。

## 简介

`AdminServiceInterface` 定义了管理员服务的权限检查、超级管理员判断和日志记录方法。实现此接口的类作为 `GlobalAdminTrait::checkAccess()` / `isSuper()` / `log()` 的委托目标。

自定义管理员服务时，优先实现此接口并通过 `localService()` 返回，而非直接实现 `AdminActionInterface`。

## 接口定义

```php
namespace DuckPhp\GlobalAdmin;

interface AdminServiceInterface
{
    public function doCheckAccess(int $admin_id, string $class, string $method, ?string $url = null): void;
    public function doIsSuper(int $admin_id): bool;
    public function doLog(int $admin_id, string $string, ?string $type = null): void;
}
```

## 方法说明

| 方法 | 说明 |
|---|---|
| `doCheckAccess(int $admin_id, string $class, string $method, ?string $url = null): void` | 检查指定管理员是否有权限。无权限时抛出异常，通过不抛异常表示允许 |
| `doIsSuper(int $admin_id): bool` | 判断指定管理员 ID 是否为超级管理员 |
| `doLog(int $admin_id, string $string, ?string $type = null): void` | 记录指定管理员的操作日志 |

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md)
