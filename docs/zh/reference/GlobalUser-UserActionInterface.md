# DuckPhp\GlobalUser\UserActionInterface

用户动作接口。

## 简介

`UserActionInterface` 定义了全局用户对象需要实现的基本操作。`GlobalUser` 类实现了此接口，调用者可以面向此接口编程。

## 接口定义

```php
namespace DuckPhp\GlobalUser;

interface UserActionInterface
{
    public function id($check_login = true);
    public function name($check_login = true) : string;
    public function service();
    public function localService();

    public function regist(array $post): array;
    public function login(array $post): array;
    public function logout();

    public function on($event, $callback);
    public function fire($event, ...$args);

    public function urlForRegist($url_back = null, $ext = null) : string;
    public function urlForLogin($url_back = null, $ext = null) : string;
    public function urlForLogout($url_back = null, $ext = null) : string;
    public function urlForHome($url_back = null, $ext = null) : string;
    
    public function checkAccess($class, string $method, ?string $url = null);
    public function log(string $string, ?string $type = null);
    
    public function getHeaderFooterData(array $input): array;
    public function mergeView(array $data, bool $with_set_head_foot = true, ?string $header = null, ?string $footer = null): array;
}
```

## 方法说明

| 方法 | 说明 |
|---|---|
| `id($check_login = true)` | 获取当前用户 ID |
| `name($check_login = true): string` | 获取当前用户名称 |
| `service()` | 返回实际的用户服务实例（跨 Phase 代理） |
| `localService()` | 返回本地用户服务实例 |
| `regist(array $post): array` | 执行注册操作 |
| `login(array $post): array` | 执行登录操作 |
| `logout()` | 执行登出操作 |
| `on($event, $callback)` | 注册事件监听 |
| `fire($event, ...$args)` | 触发事件 |
| `urlForRegist($url_back, $ext): string` | 生成注册页面 URL |
| `urlForLogin($url_back, $ext): string` | 生成登录 URL |
| `urlForLogout($url_back, $ext): string` | 生成登出 URL |
| `urlForHome($url_back, $ext): string` | 生成用户首页 URL |
| `checkAccess($class, $method, $url)` | 检查是否有权限访问指定类和方法 |
| `log($string, $type)` | 记录用户操作日志 |
| `getHeaderFooterData($input): array` | 返回用户界面头尾数据 |
| `mergeView($data, $with_set_head_foot, $header, $footer): array` | 融合用户视图数据 |

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md)
