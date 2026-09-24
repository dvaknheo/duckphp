# DuckPhp\GlobalUser\UserActionInterface

## 简介

`UserActionInterface` 是「用户会话动作」的契约接口：实现方（如 `GlobalUser`，工程内也可以是 `UserAction` 类）向框架提供当前用户的身份查询（`id/name/data`）、站点 URL（`urlForRegister/urlForLogin/urlForLogout/urlForHome`）、权限判断（`canAccess/log/batchGetUsernames`）以及服务代理（`service`）。

与管理员侧 `AdminActionInterface` 相比，用户侧多了"注册 URL（`urlForRegister`）"与"批量取用户名（`batchGetUsernames`）"，没有 `isSuper`。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`interface UserActionInterface`
- 实现方：`DuckPhp\GlobalUser\GlobalUser`

## 使用方式

工程中让自定义的"用户动作实现"实现本接口即可接入框架（通常通过 `user_callback_for_*` 选项指向该类）：

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserActionInterface;

class UserAction implements UserActionInterface
{
    public function id(bool $check_login = true) { /* 返回当前用户 id */ }
    public function name(bool $check_login = true): string { /* … */ }
    public function data(bool $check_login = true): array { /* … */ }
    public function service() { /* 返回跨 Phase 的用户服务（UserServiceInterface） */ }
    public function urlForRegister(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function urlForHome(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool { /* … */ }
    public function log(string $string, ?string $type = null, array $ext = []) { /* … */ }
    public function batchGetUsernames(array $ids): array { /* … */ }
}
```

## 注意事项

- `id()/name()/data()` 的 `$check_login` 为 `true` 时通常要求已登录。
- `canAccess()` 的参数顺序为 **`$url` 在前**（`?string $url, ?string $class, ?string $method`）。这是**破坏性变更**：旧代码若按 `$class, $method, $url` 的旧顺序传实参，会静默错位。务必检查调用处是否已更新到新顺序。

## 方法列表

### 公共方法

    public function id(bool $check_login = true)
返回当前用户 ID（`int|string`）。

    public function name(bool $check_login = true): string
返回当前用户名。

    public function data(bool $check_login = true): array
返回当前用户数据数组。

    public function service()
返回跨 Phase 的用户服务（`UserServiceInterface`）。

    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
注册页 URL。

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
登录页 URL。

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
退出登录 URL。

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
站内首页 URL。

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
判断当前用户能否访问指定 URL/控制器类/方法（缺省用当前路由）。注意参数顺序：url 在首位。

    public function log(string $string, ?string $type = null, array $ext = [])
记录一条用户操作日志。

    public function batchGetUsernames(array $ids): array
按 ID 批量取用户名（返回 id => 用户名 的映射）。

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 本接口的默认实现
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — 用户服务接口
