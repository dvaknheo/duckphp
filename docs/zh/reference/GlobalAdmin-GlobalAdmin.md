# DuckPhp\GlobalAdmin\GlobalAdmin

全局管理员组件。

## 简介

`GlobalAdmin` 组件提供了一套管理员（后台用户）操作的抽象入口。它本身只包含默认的空实现，需要在具体项目中通过子类或 `class_admin` 选项注入实际的管理员服务。

该组件通过 `DuckPhp\DuckPhp` 的 `ext` 选项加载。

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

### 事件监听

```php
GlobalAdmin::_()->on('logined', function () {
    // 管理员登录后触发
});
```

### 视图融合

```php
$data = GlobalAdmin::_()->mergeView($data, true, '_sys/header', '_sys/footer');
```

### 在 Controller 中使用

ControllerHelperTrait 提供了便捷方法：

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

### 方法一：实现接口

通过 `class_admin` 选项指定自定义管理员服务类，实现 `AdminActionInterface`：

```php
class App extends DuckPhp
{
    public $options = [
        'class_admin' => \App\Service\AdminService::class,
    ];
}
```

### 方法二：实现服务接口

实现 `AdminServiceInterface`，通过 `localService()` 返回服务实例：

```php
namespace App\Service;

use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminService implements AdminServiceInterface
{
    public function doCheckAccess(int $admin_id, string $class, string $method, ?string $url = null): void
    {
        if ($admin_id !== 1) {
            throw new \Exception('No Access');
        }
    }
    public function doIsSuper(int $admin_id): bool
    {
        return $admin_id === 1;
    }
    public function doLog(int $admin_id, string $string, ?string $type = null): void
    {
        // 写入日志
    }
}
```

然后在 `GlobalAdmin` 子类中重写 `localService()`：

```php
class MyAdmin extends GlobalAdmin
{
    protected function localService()
    {
        return new AdminService();
    }
}
```

## 全部选项

```php
public $options = [
    'class_admin' => '',
];
```

## 方法列表

### `GlobalAdmin`（主类）

`GlobalAdmin` 实现了 `AdminActionInterface`，默认方法都抛出 `AdminException("No Impelment")`。

| 方法 | 说明 |
|---|---|
| `id($check_login = true)` | 获取当前管理员 ID |
| `name($check_login = true): string` | 获取当前管理员名称 |
| `login(array $post): array` | 执行登录逻辑 |
| `logout(): void` | 执行登出逻辑 |
| `urlForLogin($url_back, $ext)` | 生成登录页面 URL |
| `urlForLogout($url_back, $ext)` | 生成登出 URL |
| `urlForHome($url_back, $ext)` | 生成后台首页 URL |

### `GlobalAdminTrait`（委托方法）

由 `GlobalAdminTrait` 提供的事件、服务和视图委托方法：

| 方法 | 说明 |
|---|---|
| `service()` | 返回管理员服务实例。通过 PhaseProxy 创建跨 Phase 代理 |
| `localService()` | 返回本地管理员服务实例。默认抛出未实现异常 |
| `on($event, $callback)` | 注册事件监听（基于 GlobalEvent） |
| `fire($event, ...$args)` | 触发事件 |
| `checkAccess($class, $method, $url)` | 检查权限，委托给 `localService()->doCheckAccess()` |
| `isSuper(): bool` | 判断是否超级管理员，委托给 `localService()->doIsSuper()` |
| `log($string, $type)` | 记录日志，委托给 `localService()->doLog()` |
| `getHeaderFooterData($input): array` | 返回管理员界面头尾数据 |
| `mergeView($data, $with_set_head_foot, $header, $footer): array` | 融合管理员视图头尾到数据中 |

### 受保护方法

| 方法 | 说明 |
|---|---|
| `initContext($context)` | 如果设置了 `class_admin` 选项，替换为指定的管理员服务类 |

## 相关链接

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md)
- [DuckPhp\GlobalAdmin\GlobalAdminTrait](GlobalAdmin-GlobalAdminTrait.md)
- [DuckPhp\GlobalAdmin\AdminException](GlobalAdmin-AdminException.md)
- [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md)
