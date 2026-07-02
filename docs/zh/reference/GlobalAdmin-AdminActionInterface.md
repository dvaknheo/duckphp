# DuckPhp\GlobalAdmin\AdminActionInterface

管理员动作接口。

## 简介

`AdminActionInterface` 定义了全局管理员对象需要实现的基本操作。实现该接口的类可以作为 `DuckPhp\GlobalAdmin\GlobalAdmin` 的实际逻辑提供者。

## 接口定义

```php
namespace DuckPhp\GlobalAdmin;

interface AdminActionInterface
{
    public function id($check_login = true);
    public function name($check_login = true);
    public function service();
    public function login(array $post);
    public function logout();

    public function urlForLogin($url_back = null, $ext = null);
    public function urlForLogout($url_back = null, $ext = null);
    public function urlForHome($url_back = null, $ext = null);

    public function checkAccess($class, string $method, ?string $url = null);
    public function isSuper();
    public function log(string $string, ?string $type = null);
}
```

## 方法说明

    public function id($check_login = true)
获取当前管理员 ID。如果 `$check_login` 为 `true` 且未登录，可能会抛出异常

    public function name($check_login = true)
获取当前管理员名称

    public function service()
返回实际的管理员服务实例

    public function login(array $post)
执行登录操作，通常接收表单数组

    public function logout()
执行登出操作

    public function urlForLogin($url_back = null, $ext = null)
生成登录 URL。`$url_back` 为登录后返回的地址，`$ext` 为扩展参数

    public function urlForLogout($url_back = null, $ext = null)
生成登出 URL

    public function urlForHome($url_back = null, $ext = null)
生成后台首页 URL

    public function checkAccess($class, string $method, ?string $url = null)
检查当前管理员是否有权限访问指定控制器类和方法

    public function isSuper()
判断当前管理员是否为超级管理员

    public function log(string $string, ?string $type = null)
记录管理员操作日志

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md)
