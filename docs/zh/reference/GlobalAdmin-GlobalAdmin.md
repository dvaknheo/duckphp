# DuckPhp\GlobalAdmin\GlobalAdmin

全局管理员组件。

## 简介

`GlobalAdmin` 组件提供了一套管理员（后台用户）操作的抽象入口。它本身只包含默认的空实现，需要在具体项目中通过子类或 `service()` 方法注入实际的管理员服务。

该组件通常通过 `DuckPhp\DuckPhp` 的 `ext` 选项加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `class_admin` | `''` | 自定义管理员服务类。为空时使用默认的 `GlobalAdmin` 自身。 |

## 事件常量

```php
const EVENT_LOGINED = 'logined';    // 登录成功事件
const EVENT_LOGOUTED = 'logouted';  // 登出事件
const EVENT_ACCESSED = 'accessed';  // 访问事件
```

## 使用方式

### 基础调用

```php
use DuckPhp\GlobalAdmin\GlobalAdmin;

$adminId = GlobalAdmin::_()->id();           // 当前管理员 ID
$adminName = GlobalAdmin::_()->name();       // 当前管理员名称

GlobalAdmin::_()->login($postData);          // 登录
GlobalAdmin::_()->logout();                  // 登出
```

### 在 Controller 中使用

```php
use DuckPhp\Foundation\Controller\Helper;

class AdminController
{
    public function index()
    {
        if (!Helper::AdminId()) {
            return Helper::Admin()->urlForLogin();
        }
        // ...
    }
}
```

## 自定义管理员服务

通过 `class_admin` 选项指定自定义管理员服务类，该类需要实现 `DuckPhp\GlobalAdmin\AdminActionInterface`：

```php
class App extends DuckPhp
{
    public $options = [
        'class_admin' => \App\Service\AdminService::class,
    ];
}
```

```php
namespace App\Service;

use DuckPhp\GlobalAdmin\AdminActionInterface;

class AdminService implements AdminActionInterface
{
    public function id($check_login = true) : int
    public function name($check_login = true)
    public function service()
    public function login(array $post)
    public function logout(): void
    public function urlForLogin($url_back = null, $ext = null)
    public function urlForLogout($url_back = null, $ext = null)
    public function urlForHome($url_back = null, $ext = null)
    public function checkAccess($class, string $method, ?string $url = null)
    public function isSuper(): bool
    public function log(string $string, ?string $type = null)
}
```

## 注意事项

1. `GlobalAdmin` 默认方法都抛出 `Exception("No Impelment")`，必须提供实际实现。
2. 通常通过 `class_admin` 选项或重写 `service()` 方法来接入业务逻辑。
3. `checkAccess()` 和 `isSuper()` 会委托给 `service()` 处理。
4. 配合 `DuckPhp\GlobalAdmin\AdminActionInterface` 使用。

## 全部选项

```php
public $options = [
    'class_admin' => '',
];
```

## 方法列表

### 公共方法

    public function service()
返回管理员服务实例。默认抛出未实现异常

    public function id($check_login = true) : int
获取当前管理员 ID

    public function name($check_login = true)
获取当前管理员名称

    public function login(array $post)
执行登录逻辑

    public function logout(): void
执行登出逻辑

    public function urlForLogin($url_back = null, $ext = null)
生成登录页面 URL

    public function urlForLogout($url_back = null, $ext = null)
生成登出 URL

    public function urlForHome($url_back = null, $ext = null)
生成后台首页 URL

    public function checkAccess($class, string $method, ?string $url = null)
检查当前管理员是否有权限访问指定类和方法

    public function isSuper(): bool
判断当前管理员是否为超级管理员

    public function log(string $string, ?string $type = null)
记录管理员日志

### 受保护方法

    protected function initContext(object $context)
如果设置了 `class_admin` 选项，则替换为指定的管理员服务类

## 相关链接

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md)
- [DuckPhp\Component\ZCallTrait](Component-ZCallTrait.md)
