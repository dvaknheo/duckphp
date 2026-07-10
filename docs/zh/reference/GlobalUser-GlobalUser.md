# DuckPhp\GlobalUser\GlobalUser

全局用户组件。

## 简介

`GlobalUser` 组件提供了一套前台用户操作的抽象入口。它本身只包含默认的空实现，需要在具体项目中通过子类或 `service()` 方法注入实际的用户服务。

该组件通常通过 `DuckPhp\DuckPhp` 的 `ext` 选项加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `class_user` | `''` | 自定义用户服务类。为空时使用默认的 `GlobalUser` 自身。 |

## 事件常量

```php
const EVENT_LOGINED = 'logined';    // 登录成功事件
const EVENT_LOGOUTED = 'logouted';  // 登出事件
```

## 使用方式

### 基础调用

```php
use DuckPhp\GlobalUser\GlobalUser;

$userId = GlobalUser::_()->id();           // 当前用户 ID
$userName = GlobalUser::_()->name();       // 当前用户名称

GlobalUser::_()->login($postData);          // 登录
GlobalUser::_()->logout();                  // 登出
GlobalUser::_()->regist($postData);          // 注册
```

### 在 Controller 中使用

```php
use DuckPhp\Foundation\Controller\Helper;

class UserController
{
    public function index()
    {
        if (!Helper::UserId()) {
            return Helper::User()->urlForLogin();
        }
        // ...
    }
}
```

## 自定义用户服务

通过 `class_user` 选项指定自定义用户服务类，该类需要实现 `DuckPhp\GlobalUser\UserActionInterface`：

```php
class App extends DuckPhp
{
    public $options = [
        'class_user' => \App\Service\UserService::class,
    ];
}
```

```php
namespace App\Service;

use DuckPhp\GlobalUser\UserActionInterface;

class UserService implements UserActionInterface
{
    public function id($check_login = true) : int
    public function name($check_login = true) : string
    public function service()
    public function login(array $post)
    public function logout(): void
    public function regist(array $post)
    public function urlForLogin($url_back = null, $ext = null) : string
    public function urlForLogout($url_back = null, $ext = null) : string
    public function urlForHome($url_back = null, $ext = null) : string
    public function urlForRegist($url_back = null, $ext = null) : string
    public function batchGetUsernames($ids)
    public function checkAccess($class, string $method, ?string $url = null)
}
```

## 注意事项

1. `GlobalUser` 默认方法都抛出 `Exception("No Impelment")`，必须提供实际实现。
2. 通常通过 `class_user` 选项或重写 `service()` 方法来接入业务逻辑。
3. `batchGetUsernames()` 会委托给 `service()` 处理。
4. `checkAccess()` 默认返回 `true`，可在子类中重写。
5. 配合 `DuckPhp\GlobalUser\UserActionInterface` 使用。

## 全部选项

```php
public $options = [
    'class_user' => '',
];
```

## 方法列表

### 公共方法

    public function service()
返回用户服务实例。默认抛出未实现异常

    public function id($check_login = true) : int
获取当前用户 ID

    public function name($check_login = true) : string
获取当前用户名称

    public function login(array $post)
执行登录逻辑

    public function logout(): void
执行登出逻辑

    public function regist(array $post)
执行注册逻辑

    public function urlForLogin($url_back = null, $ext = null) : string
生成登录页面 URL

    public function urlForLogout($url_back = null, $ext = null) : string
生成登出 URL

    public function urlForHome($url_back = null, $ext = null) : string
生成用户首页 URL

    public function urlForRegist($url_back = null, $ext = null) : string
生成注册页面 URL

    public function batchGetUsernames($ids)
批量获取用户名称

    public function checkAccess($class, string $method, ?string $url = null)
检查当前用户是否有权限访问指定类和方法。默认返回 true

### 受保护方法

    protected function initContext(object $context)
如果设置了 `class_user` 选项，则替换为指定的用户服务类

## 相关链接

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md)
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md)
- [DuckPhp\Component\ZCallTrait](Component-ZCallTrait.md)
