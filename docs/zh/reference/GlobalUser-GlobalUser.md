# DuckPhp\GlobalUser\GlobalUser

全局用户组件。

## 简介

`GlobalUser` 组件提供了一套前台用户操作的抽象入口。它本身只包含默认的空实现，需要在具体项目中通过子类或 `class_user` 选项注入实际的用户服务。

该组件通过 `DuckPhp\DuckPhp` 的 `ext` 选项加载。

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
GlobalUser::_()->regist($postData);         // 注册
```

### 事件监听

```php
GlobalUser::_()->on('logined', function () {
    // 用户登录后触发
});
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

### 方法一：实现接口

通过 `class_user` 选项指定自定义用户服务类：

```php
class App extends DuckPhp
{
    public $options = [
        'class_user' => \App\Service\UserService::class,
    ];
}
```

### 方法二：实现服务接口

实现 `UserServiceInterface`，通过 `localService()` 返回服务实例：

```php
namespace App\Service;

use DuckPhp\GlobalUser\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function doCheckAccess(int $id, string $class, string $method, ?string $url = null): void
    {
        // 权限校验
    }
    public function doLog(int $user_id, string $string, ?string $type = null): void
    {
        // 写入日志
    }
    public function doBatchGetUsernames(array $ids): array
    {
        return [];
    }
}
```

## 方法列表

### `GlobalUser`（主类）

`GlobalUser` 实现了 `UserActionInterface`，默认方法都抛出 `UserException("No Impelment")`。

| 方法 | 说明 |
|---|---|
| `id($check_login = true)` | 获取当前用户 ID |
| `name($check_login = true): string` | 获取当前用户名称 |
| `regist(array $post): array` | 执行注册逻辑 |
| `login(array $post): array` | 执行登录逻辑 |
| `logout(): void` | 执行登出逻辑 |
| `urlForLogin($url_back, $ext): string` | 生成登录页面 URL |
| `urlForLogout($url_back, $ext): string` | 生成登出 URL |
| `urlForHome($url_back, $ext): string` | 生成用户首页 URL |
| `urlForRegist($url_back, $ext): string` | 生成注册页面 URL |

### `GlobalUserTrait`（委托方法）

| 方法 | 说明 |
|---|---|
| `service()` | 返回用户服务实例。通过 PhaseProxy 创建跨 Phase 代理 |
| `localService()` | 返回本地用户服务实例。默认抛出未实现异常 |
| `on($event, $callback)` | 注册事件监听 |
| `fire($event, ...$args)` | 触发事件 |
| `checkAccess($class, $method, $url)` | 检查权限，委托给 `localService()->doCheckAccess()` |
| `log($string, $type)` | 记录日志，委托给 `localService()->doLog()` |
| `batchGetUsernames($ids)` | 批量获取用户名，委托给 `localService()->doBatchGetUsernames()` |
| `getHeaderFooterData($input): array` | 返回用户界面头尾数据 |
| `mergeView($data, $with_set_head_foot, $header, $footer): array` | 融合用户视图头尾到数据中 |

### 受保护方法

| 方法 | 说明 |
|---|---|
| `initContext($context)` | 如果设置了 `class_user` 选项，替换为指定的用户服务类 |

## 相关链接

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md)
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md)
- [DuckPhp\GlobalUser\GlobalUserTrait](GlobalUser-GlobalUserTrait.md)
- [DuckPhp\GlobalUser\UserException](GlobalUser-UserException.md)
- [DuckPhp\GlobalUser\UserControllerInterface](GlobalUser-UserControllerInterface.md)
