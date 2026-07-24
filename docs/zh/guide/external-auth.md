# 外部用户与管理员系统

DuckPHP 通过 `GlobalUser` 和 `GlobalAdmin` 组件提供用户/管理员系统的抽象入口。它们采用**回调配置**模式，只需在选项中指定回调函数即可接入，无需继承或实现复杂接口。

---

## Controller Helper 方法

以下方法适用于 Controller 层，通过 `Foundation\Controller\Helper` 调用：

| Helper 方法 | 说明 |
|---|---|
| `Helper::User()` | 获取 `GlobalUser` 实例 |
| `Helper::UserId(bool $check_login = true)` | 获取当前用户 ID。`$check_login = true` 且未登录时抛异常 |
| `Helper::UserName()` | 获取当前用户名 |
| `Helper::UserService()` | 获取用户服务实例（`UserServiceInterface` 的 PhaseProxy） |
| `Helper::Admin()` | 获取 `GlobalAdmin` 实例 |
| `Helper::AdminId(bool $check_login = true)` | 获取当前管理员 ID。`$check_login = true` 且未登录时抛异常 |
| `Helper::AdminName()` | 获取当前管理员名 |
| `Helper::AdminService()` | 获取管理员服务实例（`AdminServiceInterface` 的 PhaseProxy） |

### 使用示例

```php
// 用户端：检查登录（$check_login=false 不抛异常）
$userId = Helper::UserId(false);
if (!$userId) {
    Helper::Show302(Helper::User()->urlForLogin('/user/profile'));
    return;
}
$userName = Helper::UserName();

// 用户端：确认已登录（$check_login=true 默认，未登录抛异常）
$userId = Helper::UserId();  // 未登录时抛出异常

// 管理员端：权限检查
Helper::Admin()->checkAccess(__CLASS__, __FUNCTION__);
if (Helper::Admin()->isSuper()) {
    // 超级管理员专属功能
}
```

---

## 接口速览

用户和管理员系统分为两层：

### UserActionInterface

定义用户操作的 Web 状态相关方法。`GlobalUser` 通过 `user_callback_*` 选项委托回调来实现。

核心方法：`id()` / `name()` / `data()` → Session 读取；`urlForLogin()` / `urlForLogout()` 等 → URL 生成

### UserServiceInterface

定义无状态的用户服务方法：

```php
interface UserServiceInterface
{
    public function checkAccess($user_id, $class, $method, $url);
    public function log($user_id, $string, $type, $ext);
    public function batchGetUsernames($ids): array;
}
```

### AdminActionInterface

管理员操作接口，结构与 `UserActionInterface` 类似，区别：
- 没有 `regist` 相关方法
- 增加了 `isSuper(): bool`

### AdminServiceInterface

管理员服务接口：

```php
interface AdminServiceInterface
{
    public function checkAccess($admin_id, $class, $method, $url);
    public function log($admin_id, $string, $type, $ext);
    public function isSuper($admin_id): bool;
}
```

---

## 快速开始：搭建用户系统

### 第 1 步：实现 UserAction 类

```php
namespace YourProject\UserSystem;

class UserAction
{
    public function id($check_login = true)
    {
        return $_SESSION['user_id'] ?? null;
    }
    public function name($check_login = true): string
    {
        return $_SESSION['user_name'] ?? '';
    }
    public function data($check_login = true): array
    {
        return $_SESSION['user_data'] ?? [];
    }
    public function localService()
    {
        return new UserService();
    }
    public function service()
    {
        // 跨相位代理
        return \DuckPhp\Component\PhaseProxy::CreatePhaseProxy($phase, $this->localService());
    }
    public function urlForLogin($url_back = null, $ext = null): string { return '/login'; }
    public function urlForLogout($url_back = null, $ext = null): string { return '/logout'; }
    public function urlForHome($url_back = null, $ext = null): string { return '/'; }
    public function urlForRegist($url_back = null, $ext = null): string { return '/regist'; }
    public function mergeViewData(array $input): array { return $input; }
    public function checkAccess($class, $method, $url) {}
    public function log($string, $type = null, $ext = []) {}
    public function batchGetUsernames($ids): array { return []; }
}
```

### 第 2 步：实现 UserService

```php
namespace YourProject\UserSystem;

class UserService
{
    public function checkAccess($user_id, $class, $method, $url = null)
    {
        // 权限校验
    }
    public function log($user_id, $string, $type = null, $ext = [])
    {
        // 记录日志
    }
    public function batchGetUsernames(array $ids): array
    {
        // SELECT id, name FROM users WHERE id IN (...)
        return [];
    }
}
```

### 第 3 步：创建子 App 并配置回调

```php
namespace YourProject\UserSystem;

use DuckPhp\DuckPhp;

class UserApp extends DuckPhp
{
    public $options = [
        'user_callback_get_id' => [UserAction::class, 'id'],
        'user_callback_get_name' => [UserAction::class, 'name'],
        'user_callback_get_data' => [UserAction::class, 'data'],
        'user_callback_get_service' => [UserAction::class, 'service'],
        
        'user_url_login' => '/login',
        'user_url_logout' => '/logout',
        'user_url_home' => '/',
        'user_url_regist' => '/regist',
        
        'namespace' => __NAMESPACE__,
        'controller_url_prefix' => '',
    ];
}
```

### 第 4 步：主 App 注册子 App

```php
class App extends DuckPhp
{
    public $options = [
        'app' => [
            UserApp::class => ['not_empty' => true],
        ],
    ];
}
```

### 在 Controller/Business 中使用

```php
// Controller
public function index()
{
    $userId = Helper::UserId(false);  // false: 不抛异常，null 表示未登录
    if (!$userId) {
        Helper::Show302(Helper::User()->urlForLogin());
        return;
    }
    // Business 层
    $usernames = Helper::UserService()->batchGetUsernames([1, 2, 3]);
    Helper::Show(get_defined_vars(), 'user/index');
}
```

---

## 快速开始：搭建管理员系统

流程与用户系统相同，使用 `admin_*` 选项：

### 子 App

```php
class AdminApp extends DuckPhp
{
    public $options = [
        'admin_callback_get_id' => [AdminAction::class, 'id'],
        'admin_callback_get_name' => [AdminAction::class, 'name'],
        'admin_callback_get_service' => [AdminAction::class, 'service'],
        
        'admin_url_login' => '/admin/login',
        'admin_url_logout' => '/admin/logout',
        'admin_url_home' => '/admin/dashboard',
        
        'namespace' => __NAMESPACE__,
        'controller_url_prefix' => '',
    ];
}
```

### 主 App 注册

```php
class App extends DuckPhp
{
    public $options = [
        'app' => [
            AdminApp::class => ['not_empty' => true],
        ],
    ];
}
```

### Controller 中使用

```php
Helper::Admin()->checkAccess(__CLASS__, __FUNCTION__);
if (Helper::Admin()->isSuper()) {
    // 超级管理员
}
```

---

## 完整接口参考

各接口的完整方法列表和详细说明，请参见参考手册：

- [DuckPhp\GlobalUser\UserActionInterface](reference/GlobalUser-UserActionInterface.md)
- [DuckPhp\GlobalUser\UserServiceInterface](reference/GlobalUser-UserServiceInterface.md)
- [DuckPhp\GlobalAdmin\AdminActionInterface](reference/GlobalAdmin-AdminActionInterface.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](reference/GlobalAdmin-AdminServiceInterface.md)
