# DuckPhp\GlobalAdmin\AdminActionInterface

## 简介

`AdminActionInterface` 是「管理员会话动作」的契约接口：实现方（如 `GlobalAdmin`，工程内也可以是 `AdminAction` 类）向框架提供当前管理员的身份查询（`id/name/data`）、登录态相关 URL（`urlForLogin/urlForLogout/urlForHome`）、权限判断（`canAccess/log/isSuper`）以及服务代理（`service`）。

它把"管理员是谁、能做什么"抽象出来，使 `Foundation` 层的 Admin 控制器基类与 `GlobalAdmin` 均面向本接口编程。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`interface AdminActionInterface`
- 实现方：`DuckPhp\GlobalAdmin\GlobalAdmin`

## 使用方式

工程中让自定义的"管理员动作实现"实现本接口即可被接入框架（通常通过 `admin_callback_for_*` 选项指向该类）：

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminAction implements AdminActionInterface
{
    public function id(bool $check_login = true) { /* 返回当前管理员 id */ }
    public function name(bool $check_login = true): string { /* … */ }
    public function data(bool $check_login = true): array { /* … */ }
    public function service() { /* 返回跨 Phase 的管理员服务（AdminServiceInterface） */ }
    public function urlForLogin(?string $url_back = null): string { /* … */ }
    public function urlForLogout(): string { /* … */ }
    public function urlForHome(): string { /* … */ }
    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool { /* … */ }
    public function log(string $string, ?string $type = null, array $ext = []) { /* … */ }
    public function isSuper(): bool { /* … */ }
}
```

## 注意事项

- `id()/name()/data()` 的 `$check_login` 为 `true` 时通常要求已登录（未登录按实现处理，如抛异常）。
- `canAccess()` 的参数顺序为 **`$url` 在前**（`?string $url, ?string $class, ?string $method`）。这是**破坏性变更**：旧代码若按 `$class, $method, $url` 的旧顺序传实参，会静默错位。务必检查调用处是否已更新到新顺序。

## 方法列表

### 公共方法

    public function id(bool $check_login = true)
返回当前管理员 ID（`int|string`）。

    public function name(bool $check_login = true): string
返回当前管理员名。

    public function data(bool $check_login = true): array
返回当前管理员数据数组。

    public function service()
返回跨 Phase 的管理员服务（`AdminServiceInterface`）。

    public function urlForLogin(?string $url_back = null): string
登录页 URL；`$url_back` 为登录后回跳地址。

    public function urlForLogout(): string
退出登录 URL。

    public function urlForHome(): string
后台首页 URL。

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
判断当前管理员是否能访问指定 URL/控制器类/方法（缺省用当前路由）。注意参数顺序：url 在首位。

    public function log(string $string, ?string $type = null, array $ext = [])
记录一条管理员操作日志。

    public function isSuper(): bool
当前管理员是否为超级管理员。

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 本接口的默认实现
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — 管理员服务接口
