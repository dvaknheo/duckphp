# DuckPhp\GlobalAdmin\AdminActionInterface

管理员动作接口。

## 简介

`AdminActionInterface` 定义了全局管理员对象需要实现的基本操作。`GlobalAdmin` 类实现了此接口，调用者可以面向此接口编程。

## 接口定义

```php
namespace DuckPhp\GlobalAdmin;

interface AdminActionInterface
{
    public function id($check_login = true);
    public function name($check_login = true): string;
    public function service();
    public function localService();

    public function login(array $post): array;
    public function logout();

    public function on($event, $callback);
    public function fire($event, ...$args);
    
    public function urlForLogin($url_back = null, $ext = null);
    public function urlForLogout($url_back = null, $ext = null);
    public function urlForHome($url_back = null, $ext = null);

    public function checkAccess($class, string $method, ?string $url = null);
    public function log(string $string, ?string $type = null);
    
    public function getHeaderFooterData(array $input): array;
    public function mergeView(array $data, bool $with_set_head_foot = true, ?string $header = null, ?string $footer = null): array;

    public function isSuper();
}
```

## 方法说明

| 方法 | 说明 |
|---|---|
| `id($check_login = true)` | 获取当前管理员 ID。`$check_login` 为 true 且未登录时可能抛出异常 |
| `name($check_login = true): string` | 获取当前管理员名称 |
| `service()` | 返回实际的管理员服务实例（跨 Phase 代理） |
| `localService()` | 返回本地管理员服务实例 |
| `login(array $post): array` | 执行登录操作，返回结果数组 |
| `logout()` | 执行登出操作 |
| `on($event, $callback)` | 注册事件监听 |
| `fire($event, ...$args)` | 触发事件 |
| `urlForLogin($url_back, $ext)` | 生成登录 URL。`$url_back` 为登录后返回地址 |
| `urlForLogout($url_back, $ext)` | 生成登出 URL |
| `urlForHome($url_back, $ext)` | 生成后台首页 URL |
| `checkAccess($class, $method, $url)` | 检查是否有权限访问指定类和方法 |
| `log($string, $type)` | 记录管理员操作日志 |
| `getHeaderFooterData($input): array` | 返回管理员界面头尾数据 |
| `mergeView($data, $with_set_head_foot, $header, $footer): array` | 融合管理员视图数据 |
| `isSuper()` | 判断是否为超级管理员 |

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md)
