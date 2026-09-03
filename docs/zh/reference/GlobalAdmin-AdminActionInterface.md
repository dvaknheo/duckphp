# DuckPhp\GlobalAdmin\AdminActionInterface

## 简介

`AdminActionInterface` 是「管理员会话动作」的契约接口：实现方（如 `GlobalAdmin`，工程内也可以是 `AdminAction` 类）向框架提供当前管理员的身份查询（`id/name/data`）、登录态相关 URL（`urlForLogin/urlForLogout/urlForHome`）、视图合并（`mergeViewData/_Show`）、权限判断（`canAccess/log/isSuper`）以及服务访问（`service/localService`）。

它把“管理员是谁、能做什么”抽象出来，使 `Foundation` 层的 Admin 控制器基类与 `GlobalAdmin` 均面向本接口编程。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`interface AdminActionInterface`
- 实现方：`DuckPhp\GlobalAdmin\GlobalAdmin`

## 使用方式

工程中让自定义的“管理员动作实现”实现本接口即可被接入框架（通常通过 `admin_callback_for_*` 选项指向该类）：

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminAction implements AdminActionInterface
{
    public function id(bool $check_login = true) { /* 返回当前管理员 id */ }
    public function name(bool $check_login = true): string { /* … */ }
    public function data(bool $check_login = true): array { /* … */ }
    public function service() { /* 跨 Phase 的服务代理（可交给 GlobalAdmin 实现） */ }
    public function localService() { /* 返回 AdminServiceInterface 实现 */ }
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function urlForHome(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function mergeViewData(array $input): array { /* … */ }
    public function _Show(array $data = [], string $view = '') { /* … */ }
    public function canAccess(?string $class = null, ?string $method = null, ?string $url = null): bool { /* … */ }
    public function log(string $string, ?string $type = null, array $ext = []) { /* … */ }
    public function isSuper(): bool { /* … */ }
}
```

## 注意事项

- `id()/name()/data()` 的 `$check_login` 为 `true` 时通常要求已登录（未登录按实现处理，如抛异常）。
- `service()` 与 `localService()` 的区别：`localService` 返回当前 Phase 内直接的服务；`service` 返回可跨 Phase 调用的代理（`GlobalAdmin` 中用 `PhaseProxy` 实现）。

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

    public function localService()
返回本地（当前 Phase）管理员服务（`AdminServiceInterface`）。

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
登录页 URL；`$url_back` 为登录后回跳地址。

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
退出登录 URL。

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
后台首页 URL。

    public function mergeViewData(array $input): array
把管理员登录信息（id/name/退出 URL/头部页脚 HTML）合并进视图数据并返回。

    public function _Show(array $data = [], string $view = '')
以管理员页面方式渲染：合并视图数据、设置 header/footer 模板后交给 `View` 输出。

    public function canAccess(?string $class = null, ?string $method = null, ?string $url = null): bool
判断当前管理员是否能访问指定控制器/方法/URL（缺省用当前路由）。

    public function log(string $string, ?string $type = null, array $ext = [])
记录一条管理员操作日志。

    public function isSuper(): bool
当前管理员是否为超级管理员。

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 本接口的默认实现
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — 管理员服务接口
