# DuckPhp\GlobalAdmin\AdminActionInterface

管理员操作接口。

## 简介

`AdminActionInterface` 定义了管理员系统需要实现的全部方法。`GlobalAdmin` 通过 `admin_callback_*` 选项委托回调来实现这些方法。

## 接口定义

```php
namespace DuckPhp\GlobalAdmin;

interface AdminActionInterface
{
    public function id(bool $check_login = true);
    public function name(bool $check_login = true): string;
    public function data(bool $check_login = true): array;

    public function service();
    public function localService();

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string;
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string;
    public function urlForHome(?string $url_back = null, ?array $ext = null): string;

    public function mergeViewData(array $input): array;

    public function checkAccess(string $class, string $method, ?string $url = null);
    public function log(string $string, ?string $type = null, array $ext = []);
    
    public function isSuper(): bool;
}
```

## 方法说明

| 方法 | 说明 |
|---|---|
| `id($check_login)` | 获取当前管理员 ID |
| `name($check_login): string` | 获取当前管理员名 |
| `data($check_login): array` | 获取当前管理员完整数据 |
| `service()` | 返回 `AdminServiceInterface` 实例（通常通过 PhaseProxy 包装） |
| `localService()` | 返回本地的 `AdminServiceInterface` 实例 |
| `urlForLogin($url_back, $ext): string` | 登录页面 URL |
| `urlForLogout($url_back, $ext): string` | 登出 URL |
| `urlForHome($url_back, $ext): string` | 后台首页 URL |
| `mergeViewData($input): array` | 融合管理员视图头尾数据 |
| `checkAccess($class, $method, $url)` | 检查当前管理员是否有权限 |
| `log($string, $type, $ext)` | 记录管理员操作日志 |
| `isSuper(): bool` | 判断是否超级管理员 |

> 与 `UserActionInterface` 的区别：没有 `regist`（注册）相关方法，增加了 `isSuper()`。

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md)
