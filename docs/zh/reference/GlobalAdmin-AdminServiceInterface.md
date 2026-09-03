# DuckPhp\GlobalAdmin\AdminServiceInterface

## 简介

`AdminServiceInterface` 是“管理员服务”的契约接口，定义后台服务侧需要实现的三件事：权限判断（`canAccess`）、操作日志（`log`）、超级管理员判断（`isSuper`）。`AdminActionInterface::localService()`/`service()` 返回的对象即实现本接口。

工程上通常把鉴权与日志做成一个可替换的 Service 类，再通过选项（如 `admin_callback_for_local_service`）挂到 `GlobalAdmin` 上。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`interface AdminServiceInterface`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminService implements AdminServiceInterface
{
    public function canAccess($admin_id, string $class, string $method, ?string $url = null): bool
    {
        // 返回该管理员能否访问 controller/method/url
    }
    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
    {
        // 记录管理员操作
    }
    public function isSuper($admin_id): bool
    {
        // 是否超管
    }
}
```

## 注意事项

- `canAccess` 的 `$admin_id` 可为 `int|string`；`$url` 为可空的回跳/当前 URL。
- 本接口不含“当前管理员是谁”的查询——那是 `AdminActionInterface::id()/name()/data()` 的职责；本接口的三个方法都以 `$admin_id` 为参数显式传入。

## 方法列表

### 公共方法

    public function canAccess($admin_id, string $class, string $method, ?string $url = null): bool
判断指定管理员能否访问某控制器类/方法/URL。

    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
记录指定管理员的一条操作日志。

    public function isSuper($admin_id): bool
判断指定管理员是否超级管理员。

## 相关链接

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — 返回本服务接口的动作契约
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 管理员组件（含 service() 跨 Phase 代理）
