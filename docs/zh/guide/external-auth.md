# 外部用户与管理员系统

当我们拥有外部的用户系统的时候，我们可以通过 Helper 来使用他们。

## Controller Helper 方法

| Controller Helper 方法 | 说明 |
|---|---|
| `Helper::User()` | 获取 `GlobalUser` 实例（PhaseProxy → 子 App 相位） |
| `Helper::UserId()` | 调用 `GlobalUser::_()->id()`，返回当前用户 ID |
| `Helper::UserName()` | 调用 `GlobalUser::_()->name()`，返回当前用户名 |
| `Helper::UserService()` | 调用 `GlobalUser::_()->service()`，返回 `UserServiceInterface`的代理 |
| `Helper::Admin()` | 获取 `GlobalAdmin` 实例（PhaseProxy） |
| `Helper::AdminId()` | 获取当前管理员 ID |
| `Helper::AdminName()` | 获取当前管理员名称 |
| `Helper::AdminService()` | 获取 `GlobalAdmin::_()->service()` 返回 `AdminServiceInterface`的代理 |

### Controller 中使用示例

```php
// 获得当前用户ID。参数为 true 表示没登录会抛出异常。异常会在外部处理。
$userId = Helper::UserId(true);

// 权限检查
Helper::User()->checkAccess(__CLASS__, __FUNCTION__);

```

---

## Business Helper 调用 UserService

在 Business 层（无状态）中 通过 `Helper::UserService()` 获取 PhaseProxy 并跨相位执行
Helper::UserService() 调用的类可以视为拥有 `UserServiceInterface` 的接口的方法

```php
namespace YourProject\Business;

use DuckPhp\Foundation\Business\Helper;

class MyBusiness extends Base
{
    // 批量获取用户名字
    public function getUsernames(array $userIds): array
    {
        // Helper::UserService() 返回 PhaseProxy
        // doBatchGetUsernames() 会在子 App 相位中执行
        return Helper::UserService()->doBatchGetUsernames($userIds);
    }
}
```

### 配置

当你注册带有 GlobalUser 的子应用的时候会自动注册。
```php
namespace YourProject\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public $options = [
        'app' => [
            UserApp::class => [
                'controller_url_prefix' => 'myuser/',
                // 'class_user' => ''  // 如果你不需要这个提供者
            ],
        ],
    ];
}
```


---

### 事件系统

支持事件监听，用于与第三方系统集成：

```php
Helper::User()->on('logined', function ($userId) {
    // 用户登录后通知第三方
});
```

| 组件 | 事件常量 | 触发时机 |
|------|---------|---------|
| GlobalUser | `EVENT_LOGINED` | 用户登录成功 |
| GlobalUser | `EVENT_LOGOUTED` | 用户登出 |
| GlobalAdmin | `EVENT_LOGINED` | 管理员登录成功 |
| GlobalAdmin | `EVENT_LOGOUTED` | 管理员登出 |
| GlobalAdmin | `EVENT_ACCESSED` | 管理员访问后台 |

---

### 视图融合

```php
$data = Helper::User()->mergeView($data, true, '_sys/header', '_sys/footer');
Helper::Show($data, 'user/list');
```


## 架构设计

```
主 App（你的应用）
  │
  ├── options['app'][UserApp::class] = [...]    ← 注册子应用
  │
  ▼
子 App（UserApp，提供用户功能）
  │
  ├── options['class_user'] = UserAction::class  ← 指定用户操作类
  │
  ▼
UserAction（实现 UserActionInterface，处理 Web 状态）
  ├── id() / name()        ← Session / Cookie / Token
  ├── login() / logout()   ← 认证操作
  ├── urlForLogin() / ...   ← URL 生成
  │
  └── localService()        ← 返回 UserService（无状态业务）
       │
       ▼
UserService（实现 UserServiceInterface，纯业务）
  ├── doCheckAccess()
  ├── doLog()
  └── doBatchGetUsernames()
```

### 两层接口分工

| 接口 | 职责 | 适用范围 |
|------|------|---------|
| `UserActionInterface` | 处理 Web 状态相关操作（Session、Cookie、URL） | `class_user` 指定类 |
| `UserServiceInterface` | 纯无状态业务逻辑（权限检查、日志、批量查询） | `localService()` 返回 |

---

## GlobalUser：搭建用户系统

### 步骤概览

```
1. 创建 UserApp（子 App）并指定 class_user
2. 实现 UserAction（UserActionInterface + GlobalUserTrait）
3. 实现 UserService（UserServiceInterface）
4. 主 App 注册 UserApp 为子应用
```

### 第 1 步：创建子 App

```php
namespace YourProject\UserSystem\System;

use DuckPhp\DuckPhp;

class UserApp extends DuckPhp
{
    public $options = [
        'class_user' => UserAction::class,  // 指定用户操作类
        'namespace' => __NAMESPACE__,
    ];
}
```

### 第 2 步：实现你的 GlobalUser

替换 GlobalUser 的实现的类通常放在 Controller 层，命名为 GlobalUserAction

实现 `UserActionInterface`，搭配 `GlobalUserTrait` 提供事件/服务委托等方法：

通常不处理具体业务。而是调用其他 Action 来实现具体业务。

```php
namespace YourProject\UserSystem\Controller;

use DuckPhp\GlobalUser\GlobalUserTrait;
use DuckPhp\GlobalUser\UserActionInterface;

class GlobalUserAction implements UserActionInterface
{
    use GlobalUserTrait;

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
        // 验证用戶名密码
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        return ['success' => true, 'user_id' => $user['id']];
    }
    public function logout()
    {
        unset($_SESSION['user_id'], $_SESSION['user_name']);
    }
    public function regist(array $post): array
    {
        return ['success' => true, 'user_id' => $newId];
    }

    // 地址和实际相关
    public function urlForLogin($url_back = null, $ext = null): string { return __url('login'); }
    public function urlForLogout($url_back = null, $ext = null): string { return __url('logout'; }
    public function urlForHome($url_back = null, $ext = null): string { return '/'; }
    public function urlForRegist($url_back = null, $ext = null): string { return 'regist'; }

    // 你要提供页眉页脚片断给切调用者。
    public function getHeaderFooterData(array $input): array { return ['header' => '', 'footer' => '']; }

    // mergeView 用GlobalUserTrait的默认实现
    // mergeView 用于
    // public function mergeView(array $data, bool $with_set_head_foot = true, ?string $header = null, ?string $footer = null): array { return $data; }

    //一般接入 命名为 GlobalUserBusiness 的类
    protected function localService() { return GlobalUserBusiness::_(); }

}
```

### 第 3 步：实现 UserService

`UserServiceInterface` 只包含无状态业务方法，不涉及 Session/Cookie：

同样 GlobalUserBusiness 通常不处理具体业务。而是调用其他 Service 来实现具体业务。


```php
namespace YourProject\UserSystem\Business;

use DuckPhp\GlobalUser\UserServiceInterface;

class GlobalUserBusiness implements UserServiceInterface
{
    public function doLog(int $user_id, string $string, ?string $type = null): void
    {
        UserService::_()->log($user_id, $string, $type);
    }
    public function doBatchGetUsernames(array $ids): array
    {
        return UserService::_()->doBatchGetUsernames($ids); // [id => name]
    }
    public function doCheckAccess(int $id, string $class, string $method, ?string $url = null): void
    {
        UserService::_()->checkLogin($id, $class, $method $url);
    }
}
```

---

## GlobalAdmin：管理员系统

`GlobalAdmin` 与 `GlobalUser` 采用相同的子 App + PhaseProxy 模式。区别在于：

- 通过 `class_admin` 选项指定实现类
- **没有 `regist`（注册）接口**——管理员由后台创建而非自助注册
- 提供额外`isSuper()`（超级管理员判断）

### 接口介绍

| 接口 | 说明 |
|------|------|
| `AdminActionInterface` | 管理员操作接口（类比 UserActionInterface），无 `regist` 方法 |
| `AdminServiceInterface` | 管理员服务接口（类比 UserServiceInterface），多一个 `doIsSuper()` |
| `AdminControllerInterface` | 标记接口，用于标识控制器属于管理员端 |

`AdminActionInterface` 方法：

| 方法 | 说明 |
|---|---|
| `id()` / `name()` | 获取当前管理员 ID 和名称 |
| `login()` / `logout()` | 登录/登出 |
| `urlForLogin()` / `urlForLogout()` / `urlForHome()` | URL 生成 |
| `service()` / `localService()` | 服务委托模式 |
| `on()` / `fire()` | 事件系统 |
| `checkAccess()` | 权限检查（委托给 AdminService） |
| `isSuper()` | 超级管理员判断（委托给 AdminService） |
| `log()` | 记录日志（委托给 AdminService） |
| `getHeaderFooterData()` / `mergeView()` | 视图融合 |

`AdminServiceInterface` 方法：

| 方法 | 说明 |
|---|---|
| `doCheckAccess($admin_id, $class, $method, $url): void` | 检查权限，无权限时抛异常 |
| `doIsSuper($admin_id): bool` | 判断是否超级管理员 |
| `doLog($admin_id, $string, $type): void` | 记录操作日志 |

---


## 完整示例

```php
// === UserApp.php — 子 App ===
namespace SampleUserProvider\System;
use DuckPhp\DuckPhp;
class UserApp extends DuckPhp
{
    public $options = [
        'class_user' => UserAction::class,
        'namespace' => __NAMESPACE__,
        'controller_url_prefix' => '',
    ];
}

// === GlobalUserAction.php ===
namespace SampleUserProvider\Controller;
use DuckPhp\GlobalUser\GlobalUserTrait;
use DuckPhp\GlobalUser\UserActionInterface;
class GlobalUserAction implements UserActionInterface
{
    use GlobalUserTrait;
    public function id($check_login = true) { return $_SESSION['user_id'] ?? null; }
    public function name($check_login = true): string { return $_SESSION['user_name'] ?? ''; }
    public function login(array $post): array { $_SESSION['user_id'] = 1; return ['success' => true]; }
    public function logout() { unset($_SESSION['user_id']); }
    public function regist(array $post): array { return ['success' => true]; }
    public function urlForLogin($url_back = null, $ext = null): string { return 'login'; }
    public function urlForLogout($url_back = null, $ext = null): string { return 'logout'; }
    public function urlForHome($url_back = null, $ext = null): string { return '/'; }
    public function urlForRegist($url_back = null, $ext = null): string { return 'regist'; }
    public function getHeaderFooterData(array $input): array { return ['header' => '', 'footer' => '']; }
    public function mergeView(array $data, $with_set_head_foot = true, $header = null, $footer = null): array { return $data; }
    protected function localService() { return new UserService(); }
}

// === GlobalUserBusiness.php ===
namespace SampleUserProvider\Business;
use DuckPhp\GlobalUser\UserServiceInterface;
class GlobalUserBusiness implements UserServiceInterface
{
    public function doLog(int $user_id, string $string, ?string $type = null): void {}
    public function doBatchGetUsernames(array $ids): array { return []; }
    public function doCheckAccess(int $id, string $class, string $method, ?string $url = null): void {}
}

// === App.php ===
namespace MyProject\System;
use DuckPhp\DuckPhp;
use SampleUserProvider\System\
class App extends DuckPhp
{
    public $options = [
        'app' => [
             UserApp::class => ['not_empty' => true] 
        ],
    ];
}

// === Controller ===
namespace MyProject\Controller;
class MainController extends Base
{
    public function index()
    {
        $userId = Helper::UserId(true);

        Helper::Show(['userId' => $userId], 'main');
    }
}
```

## 参考链接

- [DuckPhp\GlobalUser\GlobalUser](reference/GlobalUser-GlobalUser.md)
- [DuckPhp\GlobalUser\UserActionInterface](reference/GlobalUser-UserActionInterface.md)
- [DuckPhp\GlobalUser\UserServiceInterface](reference/GlobalUser-UserServiceInterface.md)
- [DuckPhp\GlobalAdmin\GlobalAdmin](reference/GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalAdmin\AdminActionInterface](reference/GlobalAdmin-AdminActionInterface.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](reference/GlobalAdmin-AdminServiceInterface.md)
