# DuckPhp\GlobalUser\UserActionInterface

用户动作接口。

## 简介

`UserActionInterface` 定义了全局用户对象需要实现的基本操作。实现该接口的类可以作为 `DuckPhp\GlobalUser\GlobalUser` 的实际逻辑提供者。

## 接口定义

```php
namespace DuckPhp\GlobalUser;

interface UserActionInterface
{
    public function id($check_login = true) : int;
    public function name($check_login = true) : string;
    public function service();

    public function login(array $post);
    public function logout();
    public function regist(array $post);

    public function urlForLogin($url_back = null, $ext = null) : string;
    public function urlForLogout($url_back = null, $ext = null) : string;
    public function urlForHome($url_back = null, $ext = null) : string;
    public function urlForRegist($url_back = null, $ext = null) : string;

    public function batchGetUsernames($ids);
    public function checkAccess($class, string $method, ?string $url = null);
}
```

## 方法说明

    public function id($check_login = true) : int;
获取当前用户 ID。如果 `$check_login` 为 `true` 且未登录，可能会抛出异常

    public function name($check_login = true) : string;
获取当前用户名称

    public function service();
返回实际的用户服务实例

    public function login(array $post);
执行登录操作

    public function logout();
执行登出操作

    public function regist(array $post);
执行注册操作

    public function urlForLogin($url_back = null, $ext = null) : string;
生成登录 URL

    public function urlForLogout($url_back = null, $ext = null) : string;
生成登出 URL

    public function urlForHome($url_back = null, $ext = null) : string;
生成用户首页 URL

    public function urlForRegist($url_back = null, $ext = null) : string;
生成注册页面 URL

    public function batchGetUsernames($ids)
批量获取用户名称

    public function checkAccess($class, string $method, ?string $url = null)
检查当前用户是否有权限访问指定控制器类和方法

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md)
