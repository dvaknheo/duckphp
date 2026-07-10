# DuckPhp\GlobalAdmin\AdminServiceInterface

管理员服务接口。

## 简介

`AdminServiceInterface` 定义了管理员服务层需要实现的权限检查、超级管理员判断和日志记录方法。它通常作为 `AdminActionInterface` 中 `service()` 返回对象的接口约定。

## 接口定义

```php
namespace DuckPhp\GlobalAdmin;

interface AdminServiceInterface
{
    public function doCheckAccess(int $admin_id, string $class, string $method, ?string $url = null);
    public function doIsSuper(int $admin_id);
    public function doLog(int $admin_id, string $string, ?string $type = null);
}
```

## 方法说明

    public function doCheckAccess(int $admin_id, string $class, string $method, ?string $url = null);
检查指定管理员是否有权限访问控制器类 `$class` 的方法 `$method`。`$url` 为可选的访问 URL

    public function doIsSuper(int $admin_id);
判断指定管理员 ID 是否为超级管理员

    public function doLog(int $admin_id, string $string, ?string $type = null);
记录指定管理员的操作日志。`$type` 为日志类型

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md)
