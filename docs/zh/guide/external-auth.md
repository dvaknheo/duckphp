# 外部用户/管理员系统

DuckPHP 通过 `GlobalUser` 和 `GlobalAdmin` 组件提供用户/管理员认证的抽象层。你的项目可以接入任意外部认证系统（OAuth、LDAP、自建用户中心等），只需要实现对应的接口。

## 概念与架构

```
Controller 层 (路由入口)
    │
    ├── Helper::User() / Helper::Admin()     ← 获取当前用户/管理员对象
    │
    ▼
GlobalUser / GlobalAdmin                     ← 框架组件（抽象入口）
    │
    ├── id() / name()                        ← 当前身份信息
    ├── login() / logout()                   ← 认证操作
    ├── service() / localService()           ← 委托给实际实现
    │
    ▼
Your UserService / AdminService              ← 你的实现（接入外部系统）
    │
    └── doCheckAccess() / doLog() / ...
```

**关键设计：**
- `GlobalUser` / `GlobalAdmin` 是框架组件，通过 `ext` 选项加载
- 它们的方法默认抛出 `"No Impelment"`，必须提供实际实现
- 通过 `service()` 委托模式，你可以接入任意外部认证逻辑

---

## GlobalUser：用户认证系统

### 启用组件

在 `src/System/App.php` 中开启 `GlobalUser`：

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\GlobalUser\GlobalUser::class => true,
        ],
        // 可选：指定自定义用户服务类
        'class_user' => \YourProject\System\UserService::class,
    ];
}
```

### 基础使用

在 Controller 中通过 Helper 方法访问当前用户：

```php
namespace YourProject\Controller;

class UserController extends Base
{
    public function profile()
    {
        $userId = Helper::UserId();
        if (!$userId) {
            Helper::Show302(Helper::User()->urlForLogin());
            return;
        }
        $userName = Helper::UserName();
        Helper::Show(get_defined_vars(), 'user/profile');
    }
    public function doLogin()
    {
        $post = Helper::POST();
        $result = Helper::User()->login($post);
        // $result 包含登录结果，由你的实现决定格式
        Helper::ShowJson($result);
    }
    public function doLogout()
    {
        Helper::User()->logout();
        Helper::Show302('/');
    }
}
```

可用 Helper 方法：

| 方法 | 说明 |
|---|---|
| `Helper::User()` | 获取 GlobalUser 实例 |
| `Helper::UserId()` | 获取当前用户 ID，未登录返回 null |
| `Helper::UserName()` | 获取当前用户名 |
| `Helper::UserService()` | 获取用户服务实例 |

### 实现用户服务

有两种实现方式：

#### 方式一：实现 `UserActionInterface`（通过 `class_user` 选项）

```php
namespace YourProject\System;

use DuckPhp\GlobalUser\UserActionInterface;

class UserService implements UserActionInterface
{
    // 使用 ZCallTrait 以便跨 Phase 调用
    use \DuckPhp\Foundation\ZCallTrait;
    
    // ---- 认证信息 ----
    public function id($check_login = true)
    {
        // 从 Session / Token 获取当前用户 ID
        $userId = $_SESSION['user_id'] ?? null;
        if ($check_login && !$userId) {
            throw new \Exception('未登录');
        }
        return $userId;
    }
    public function name($check_login = true): string
    {
        $userId = $this->id($check_login);
        return $this->doBatchGetUsernames([$userId])[$userId] ?? '';
    }
    
    // ---- 认证操作 ----
    public function regist(array $post): array
    {
        // 接入你的注册逻辑
        // $post 包含 username, password, email 等
        $userId = $this->createUser($post);
        $_SESSION['user_id'] = $userId;
        return ['success' => true, 'user_id' => $userId];
    }
    public function login(array $post): array
    {
        // 接入你的登录逻辑
        $user = $this->verifyPassword($post['username'], $post['password']);
        if (!$user) {
            return ['success' => false, 'error' => '用户名或密码错误'];
        }
        $_SESSION['user_id'] = $user['id'];
        return ['success' => true, 'user_id' => $user['id']];
    }
    public function logout()
    {
        unset($_SESSION['user_id']);
    }
    
    // ---- 委托方法（可委托给 AdminServiceInterface） ----
    public function service() { /* 见 GlobalUserTrait 实现 */ }
    public function localService() { return $this; }
    
    public function on($event, $callback) { /* 事件系统 */ }
    public function fire($event, ...$args) { /* 事件系统 */ }
    
    // ---- URL ----
    public function urlForLogin($url_back = null, $ext = null): string
    {
        return Helper::Url('/user/login');
    }
    public function urlForLogout($url_back = null, $ext = null): string
    {
        return Helper::Url('/user/logout');
    }
    public function urlForHome($url_back = null, $ext = null): string
    {
        return Helper::Url('/user/profile');
    }
    public function urlForRegist($url_back = null, $ext = null): string
    {
        return Helper::Url('/user/regist');
    }
    
    // ---- 权限与审计（可选） ----
    public function checkAccess($class, string $method, ?string $url = null)
    {
        return true; // 默认允许
    }
    public function log(string $string, ?string $type = null)
    {
        // 写入用户操作日志
    }
    
    // ---- 视图融合（可选） ----
    public function getHeaderFooterData(array $input): array
    {
        return ['header' => '', 'footer' => ''];
    }
    public function mergeView(array $data, bool $with_set_head_foot = true, ?string $header = null, ?string $footer = null): array
    {
        return $data;
    }
    
    // ---- 批量操作 ----
    public function batchGetUsernames($ids)
    {
        // 从数据库查询用户名映射
        return []; // [id => name]
    }
}
```

#### 方式二：实现 `UserServiceInterface`（通过 `localService()`）

更精简，只需实现三个 `do*` 方法：

```php
namespace YourProject\System;

use DuckPhp\GlobalUser\UserServiceInterface;

class UserAdminService implements UserServiceInterface
{
    public function doLog(int $user_id, string $string, ?string $type = null): void
    {
        // INSERT INTO user_log (user_id, content, type) VALUES (?, ?, ?)
    }
    public function doBatchGetUsernames(array $ids): array
    {
        // SELECT id, name FROM users WHERE id IN (...)
        return [1 => 'admin', 2 => 'user1']; // 示例
    }
    public function doCheckAccess(int $id, string $class, string $method, ?string $url = null): void
    {
        if ($id !== 1) {
            throw new \Exception('无权限');
        }
    }
}
```

然后在 `GlobalUser` 子类中重写 `localService()`：

```php
namespace YourProject\System;

class MyUserService extends \DuckPhp\GlobalUser\GlobalUser
{
    protected function localService()
    {
        return new UserAdminService();
    }
    // 仍需要实现 id() / name() / login() / logout() / url*() 等方法
    public function id($check_login = true) { /* ... */ }
    public function name($check_login = true): string { /* ... */ }
    // ...
}
```

---

## GlobalAdmin：管理员认证系统

`GlobalAdmin` 的结构与 `GlobalUser` 类似，但多了一个 `checkAccess()` 和 `isSuper()` 方法，专门用于后台权限管理。

### 启用组件

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\GlobalAdmin\GlobalAdmin::class => true,
        ],
        'class_admin' => \YourProject\System\AdminService::class,
    ];
}
```

### 基础使用

```php
namespace YourProject\Controller;

class AdminController extends Base
{
    public function dashboard()
    {
        // 检查是否管理员
        if (!Helper::AdminId()) {
            Helper::Show302(Helper::Admin()->urlForLogin());
            return;
        }
        // 检查超级管理员
        if (!Helper::Admin()->isSuper()) {
            Helper::Show404();
            return;
        }
        Helper::Show(get_defined_vars(), 'admin/dashboard');
    }
}
```

可用 Helper 方法：

| 方法 | 说明 |
|---|---|
| `Helper::Admin()` | 获取 GlobalAdmin 实例 |
| `Helper::AdminId()` | 获取当前管理员 ID |
| `Helper::AdminService()` | 获取管理员服务实例 |

### 实现管理员服务

实现 `AdminServiceInterface`，通过 `localService()` 返回：

```php
namespace YourProject\System;

use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminAuthService implements AdminServiceInterface
{
    public function doCheckAccess(int $admin_id, string $class, string $method, ?string $url = null): void
    {
        // 检查权限：从数据库读取角色权限
        $permissions = $this->getPermissions($admin_id);
        $key = $class . '::' . $method;
        if (!in_array($key, $permissions, true)) {
            throw new \Exception('权限不足');
        }
    }
    public function doIsSuper(int $admin_id): bool
    {
        return $admin_id === 1; // ID 为 1 的是超级管理员
    }
    public function doLog(int $admin_id, string $string, ?string $type = null): void
    {
        // 记录管理操作日志
        // INSERT INTO admin_log (admin_id, content, type, ip) VALUES (?, ?, ?, ?)
    }
    private function getPermissions(int $admin_id): array
    {
        // 从缓存或数据库加载权限列表
        return [
            'AdminController::dashboard',
            'AdminController::users',
            'UserController::index',
        ];
    }
}
```

然后在子类中重写 `localService()`：

```php
namespace YourProject\System;

class MyAdmin extends \DuckPhp\GlobalAdmin\GlobalAdmin
{
    protected function localService()
    {
        return new AdminAuthService();
    }
    public function id($check_login = true)
    {
        return $_SESSION['admin_id'] ?? null;
    }
    public function name($check_login = true): string
    {
        return $_SESSION['admin_name'] ?? '';
    }
    public function login(array $post): array
    {
        // 验证管理员身份
        if ($post['password'] === 'secret') {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_name'] = 'Admin';
            return ['success' => true];
        }
        return ['success' => false, 'error' => '密码错误'];
    }
    public function logout(): void
    {
        unset($_SESSION['admin_id'], $_SESSION['admin_name']);
    }
    public function urlForLogin($url_back = null, $ext = null)
    {
        return Helper::Url('/admin/login');
    }
    public function urlForLogout($url_back = null, $ext = null)
    {
        return Helper::Url('/admin/logout');
    }
    public function urlForHome($url_back = null, $ext = null)
    {
        return Helper::Url('/admin/dashboard');
    }
}
```

### 与 CRUD Controller 配合

使用 `skip_permission_check` 跳过权限检查：

```php
class AdminUserController extends Base
{
    public function index()
    {
        // 如果有权限控制，在这里检查
        Helper::Admin()->checkAccess(static::class, __FUNCTION__);
        // ...
    }
}
```

---

## 事件驱动集成

`GlobalUser` 和 `GlobalAdmin` 都支持事件监听，可以用来与第三方系统集成：

```php
// 在 App.php 的 onInited 中注册事件
protected function onInited()
{
    parent::onInited();
    
    // 用户登录后同步到第三方
    GlobalUser::_()->on('logined', function ($userId) {
        // 通知第三方系统
    });
}
```

可用事件：

| 组件 | 事件 | 触发时机 |
|---|---|---|
| `GlobalUser` | `EVENT_LOGINED` | 用户登录成功 |
| `GlobalUser` | `EVENT_LOGOUTED` | 用户登出 |
| `GlobalAdmin` | `EVENT_LOGINED` | 管理员登录成功 |
| `GlobalAdmin` | `EVENT_LOGOUTED` | 管理员登出 |
| `GlobalAdmin` | `EVENT_ACCESSED` | 管理员访问后台 |

---

## 视图融合

管理员/用户系统通常需要统一的后台/前台模板。`GlobalAdmin` 和 `GlobalUser` 提供了 `mergeView()` 方法来实现视图融合。

```php
// 在 Controller 中使用
public function index()
{
    $data = ['title' => '用户列表', 'users' => [...]];
    $data = GlobalUser::_()->mergeView($data, true, '_sys/header', '_sys/footer');
    Helper::Show($data, 'user/list');
}
```

---

## 会话管理（Session）

Session 操作在 Controller 层的 `Session` 类中管理，不要直接在 Business 或 Model 中读写 Session：

```php
namespace YourProject\Controller;

class Session extends \DuckPhp\Foundation\Controller\Session
{
    // 框架基类已提供 set() / get() / unset() 等方法
}
```

---

## 完整示例：自建用户系统

以下是一个整合示例：

```php
// 1. App.php 配置
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\GlobalUser\GlobalUser::class => true,
            \DuckPhp\GlobalAdmin\GlobalAdmin::class => true,
        ],
        'class_user' => \YourProject\System\UserService::class,
    ];
}

// 2. 用户服务
namespace YourProject\System;

use DuckPhp\GlobalUser\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function doLog(int $user_id, string $string, ?string $type = null): void
    {
        // 记录日志
    }
    public function doBatchGetUsernames(array $ids): array
    {
        // 批量查询用户名
        return [];
    }
    public function doCheckAccess(int $id, string $class, string $method, ?string $url = null): void
    {
        // 权限检查
    }
}

// 3. 自定义 GlobalUser
namespace YourProject\System;

class MyUser extends \DuckPhp\GlobalUser\GlobalUser
{
    protected function localService()
    {
        return new UserService();
    }
    public function id($check_login = true)
    {
        return $_SESSION['user_id'] ?? null;
    }
    public function name($check_login = true): string
    {
        return $_SESSION['user_name'] ?? '';
    }
    public function login(array $post): array
    {
        // 验证用户
        return ['success' => true, 'user_id' => 1];
    }
    public function logout(): void
    {
        unset($_SESSION['user_id']);
    }
    // ... urlFor* 方法
}

// 4. Controller 中使用
namespace YourProject\Controller;

class UserController extends Base
{
    public function index()
    {
        if (!Helper::UserId()) {
            Helper::Show302(Helper::User()->urlForLogin());
            return;
        }
        $userName = Helper::UserName();
        Helper::Show(get_defined_vars(), 'user/index');
    }
}
```

## 参考链接

- [DuckPhp\GlobalUser\GlobalUser](reference/GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserActionInterface](reference/GlobalUser-UserActionInterface.md)
- [DuckPhp\GlobalUser\UserServiceInterface](reference/GlobalUser-UserServiceInterface.md)
- [DuckPhp\GlobalAdmin\GlobalAdmin](reference/GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](reference/GlobalAdmin-AdminServiceInterface.md)
