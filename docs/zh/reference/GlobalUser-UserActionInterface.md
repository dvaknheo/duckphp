# DuckPhp\GlobalUser\UserActionInterface

用户操作接口。

## 简介

`UserActionInterface` 定义了用户系统需要实现的全部方法。`GlobalUser` 通过 `user_callback_*` 选项委托回调来实现这些方法，实际用户系统开发中实现此接口的类作为回调目标。

## 接口定义

```php
namespace DuckPhp\GlobalUser;

interface UserActionInterface
{
    public function id(bool $check_login = true);
    public function name(bool $check_login = true): string;
    public function data(bool $check_login = true): array;

    public function service();
    public function localService();

    public function urlForRegist(?string $url_back = null, ?array $ext = null): string;
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string;
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string;
    public function urlForHome(?string $url_back = null, ?array $ext = null): string;

    public function mergeViewData(array $input): array;

    public function checkAccess(string $class, string $method, ?string $url = null);
    public function log(string $string, ?string $type = null, array $ext = []);
    
    public function batchGetUsernames(array $ids): array;
}
```

## 方法说明

| 方法 | 说明 |
|---|---|
| `id($check_login)` | 获取当前用户 ID。未登录时根据 `$check_login` 决定是否抛异常 |
| `name($check_login): string` | 获取当前用户名 |
| `data($check_login): array` | 获取当前用户完整数据（ID、角色、头像等） |
| `service()` | 返回 `UserServiceInterface` 实例（通常通过 PhaseProxy 包装） |
| `localService()` | 返回本地的 `UserServiceInterface` 实例 |
| `urlForRegist($url_back, $ext): string` | 注册页面 URL |
| `urlForLogin($url_back, $ext): string` | 登录页面 URL |
| `urlForLogout($url_back, $ext): string` | 登出 URL |
| `urlForHome($url_back, $ext): string` | 首页 URL |
| `mergeViewData($input): array` | 融合用户视图头尾数据到 `$input['__view_data']` |
| `checkAccess($class, $method, $url)` | 检查当前用户是否有权限访问指定类和方法 |
| `log($string, $type, $ext)` | 记录用户操作日志 |
| `batchGetUsernames($ids): array` | 批量查询用户 ID 对应的用户名，返回 `[id => name]` |

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md)
