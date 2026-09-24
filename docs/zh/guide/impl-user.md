# 4-12 实现用户系统

> 解决什么问题：让[第 2-18 章 使用用户系统](user.md)里那些 `Helper::User*()` 真的有东西可用——你提供「会话 / 登录服务 / 本地服务」三件实现，再把组件挂进应用。
> 前置：[第 2-18 章](user.md)（先知道调用方怎么用）、[第 2-9 章 会话](session.md)、[第 4-2 章 开发组件与扩展](custom-component.md)。预计 25 分钟。
> 可跑资产：`tests/GlobalUser/GlobalUserTest.php`（`UserTestApp` / `UserTestSession` / `UserTestService`，58 断言）、`tests/Foundation/Controller/UserControllerBaseTest.php`（控制器侧 12 断言）。

## 最小可跑接入

一个子类 + 三个实现 + 一段选项，就这一套：

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;
use MyProj\UserSystem\MyUser;        // extends \DuckPhp\GlobalUser\GlobalUser
use MyProj\UserSystem\UserService;   // 实现 UserServiceInterface + UserLoginServiceInterface
use MyProj\UserSystem\UserSession;   // 实现 UserSessionInterface

class App extends DuckPhp
{
    public $options = [
        'globaluser_url_home'     => 'user/center',
        'globaluser_url_login'    => 'user/login',
        'globaluser_url_logout'   => 'user/logout',
        'globaluser_url_register' => 'user/register',

        'globaluser_login_session' => [UserSession::class, '_'],   // 当前是谁
        'globaluser_login_service' => [UserService::class, '_'],   // 注册/登录/登出
        'globaluser_local_service' => [UserService::class, '_'],   // canAccess/log/batchGetUsernames

        'ext' => [
            MyUser::class => true,
        ],
    ];
}
```

```php
namespace MyProj\UserSystem;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalUser\UserSessionInterface;
use DuckPhp\GlobalUser\UserSessionTrait;

class UserSession implements UserSessionInterface
{
    use SessionTrait;          // 带前缀的会话读写（第 2-9 章）
    use UserSessionTrait;      // getCurrentUserId/Name、getCurrentUser、setCurrentUser、unsetCurrentUser
}
```

```php
namespace MyProj\UserSystem;

use DuckPhp\GlobalUser\UserLoginServiceInterface;
use DuckPhp\GlobalUser\UserServiceInterface;

class UserService implements UserServiceInterface, UserLoginServiceInterface
{
    public function canAccess($user_id, ?string $url, string $class, string $method): bool
    {
        return $user_id > 0;                       // 你的权限规则
    }
    public function log($user_id, string $string, ?string $type = null, array $ext = [])
    {
        MyLogModel::_()->add($user_id, $string, $type, $ext);
    }
    public function batchGetUsernames(array $ids): array
    {
        return MyUserModel::_()->namesByIds($ids);   // [id => 名字]
    }
    public function register(array $post)
    {
        // 校验 → 落库 → 返回用户数组（会被写进会话）
        return MyUserModel::_()->create($post);
    }
    public function login(array $post)
    {
        $user = MyUserModel::_()->findByLogin($post['username'], $post['password']);
        return $user ?: [];                          // 返回用户数组；返回空数组表示没登录成功
    }
    public function logout($id)
    {
        // 需要的话在这里记账（清会话由组件负责）
    }
}
```

> 上面这套（应用级 `globaluser_*` 选项 + `ext` 挂载 + 三个实现）已实测：`Helper::UserId()` 读会话、`Helper::User()->login()` 写会话后 302 到 `globaluser_url_home`、`Helper::UserService()` 能批量取用户名、未登录时 `Helper::UserId()` 302 到 `globaluser_url_login`。

## 1. 组件与「键」：`User` 是键，`GlobalUser` 是实现

全框架（含 `Helper::User*()` 与 [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md)）读的都是父类名 [`User`](../reference/GlobalUser-User.md) 这个容器键。`User` 自己是**桩**：没挂实现时任何能力调用都直接抛 `DuckPhpSystemException`（`id()`/`name()` 抛 `No GlobalUser Provider.`，其余抛 `Need Provider`），不会静默返回空值。

[`GlobalUser`](../reference/GlobalUser-GlobalUser.md) 是完整实现，`init()` 里把自己经 [`PhaseProxy`](../reference/Component-PhaseProxy.md) 注册到这个键上——这就是「挂上去」的全部含义。

`ext` 的三种写法（见[第 4-2 章](custom-component.md)）：

| 写法 | 效果 |
|---|---|
| `MyUser::class => true` | 跟随应用选项（上面最小示例用的就是它） |
| `MyUser::class => ['globaluser_url_login' => 'u/login']` | 只给这个组件这批选项 |
| `GlobalUser::class => true` | 不写子类，直接用框架实现（选项仍写在应用里） |

**不写子类也行**：`MyUser` 只是「工程自己的类型」，方便你加方法或在 `$options` 里写默认值；`'ext' => [\DuckPhp\GlobalUser\GlobalUser::class => true]` 同样能跑。

应用选项 `user_provider_enable`（默认 `true`）控制这次注册：设为 `false` 就不注册，`Helper::User*()` 随即退回桩（等于「本项目没有用户体系」）。

## 2. 全部选项

| 选项 | 默认 | 作用 |
|---|---|---|
| `globaluser_login_session` | `null` | 会话实现；**必需** |
| `globaluser_login_service` | `null` | 登录服务（注册/登录/登出）；**必需** |
| `globaluser_local_service` | `null` | 本地 Service（`canAccess()`/`log()`/`batchGetUsernames()`）；**必需** |
| `globaluser_url_home` / `globaluser_url_login` / `globaluser_url_logout` / `globaluser_url_register` | `null` | 站内首页 / 登录 / 退出 / 注册 URL（`__url()` 生成，未配则 `'/'`） |
| `globaluser_view_file_header` / `globaluser_view_file_footer` | `null` | 用户页面的头/尾视图文件（见第 7 节） |
| `globaluser_enable_callback_singleton` | `true` | 回调写成 `[类名, 方法]` 时先换成 `类名::_()` 单例；写 `false` 就用静态调用 |
| `globaluser_ext_view_data_callback` | `null` | 追加视图数据的回调（可选） |
| `globaluser_need_login_callback` | `null` | 「未登录怎么办」的自定义处理（可选，见第 6 节） |
| `globaluser_is_authed_redirect` | `true` | 注册/登录/登出成功后自动 302 |

三个**必需**键没配时抛 `DuckPhpSystemException: need ext options 'globaluser_login_session'`；两个可选键用 `isset()` 判过，没配只是跳过。

另外两个**不在组件 `$options` 里**、但会盖住组件值的应用级选项（`DuckPhp` 的隐藏选项）：

- `url_user_home`：优先于 `globaluser_url_home`；
- `url_user_logout`：优先于 `globaluser_url_logout`。

## 3. 会话实现：`UserSessionTrait`

用户系统**不碰 `$_SESSION`**，`globaluser_login_session` 要求的是一个 [`UserSessionInterface`](../reference/GlobalUser-UserSessionInterface.md) 实现——五个方法：`getCurrentUserId()` / `getCurrentUserName()` / `getCurrentUser()` / `setCurrentUser($user)` / `unsetCurrentUser()`。

框架自带 [`UserSessionTrait`](../reference/GlobalUser-UserSessionTrait.md) 把用户存进会话键 **`user`**（数组，含 `id`/`name`），配上第 2-9 章的 [`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) 就是上面最小示例里的写法。要换成 JWT / Redis / 单点登录，就替换这一层——`id()`/`name()` 只认它的返回值。

> `$_SESSION` 里的键名要跟[第 2-18 章第 1 节](user.md)的调用方约定一致：`Helper::UserId()` 拿到的就是 `getCurrentUserId()` 的返回值，未登录时它返回 `0`（`UserSessionTrait` 里 `$user['id'] ?? 0`）。

## 4. 本地服务：`UserServiceInterface`

[`UserServiceInterface`](../reference/GlobalUser-UserServiceInterface.md) 三个方法，都是「以 `$user_id` 为第一个参数」的显式传参，**不读会话**：

| 方法 | 什么时候被调 |
|---|---|
| `canAccess($user_id, ?string $url, string $class, string $method): bool` | `Helper::User()->canAccess()`（参数顺序与 Action 侧一致：`$url` 在前） |
| `log($user_id, string $string, ?string $type = null, array $ext = [])` | `Helper::User()->log()` |
| `batchGetUsernames(array $ids): array` | `Helper::UserService()->batchGetUsernames()` |

它同时是[第 2-18 章第 4 节](user.md)里「业务层怎么拿用户信息」的答案：业务层只有 `UserService`，所以要用「谁的权限/谁的名字」，就把 `$userId` 从控制器传进来。

## 5. 登录服务：`UserLoginServiceInterface`

[`UserLoginServiceInterface`](../reference/GlobalUser-UserLoginServiceInterface.md) 负责真正的校验与落库，用户侧比管理员侧多一个注册：

| 方法 | 被谁调用 | 返回什么 |
|---|---|---|
| `register(array $post)` | `Helper::User()->register($post)` | 用户数组（会被 `setCurrentUser()` 写进会话） |
| `login(array $post)` | `Helper::User()->login($post)` | 用户数组（同样会被写进会话） |
| `logout($id)` | `Helper::User()->logout()`（`$id` 是 `id(false)` 的结果，未登录可能是 `0`） | 无 |

⚠️ **组件不判断「登录成功没有」**：它把 `login()` 的返回值**原样** `setCurrentUser($user)` 写进会话，然后照常发完成事件、照常按 `globaluser_is_authed_redirect` 302。所以「用户名密码不对」这件事由**你的登录服务**决定表现——最常见的是返回空数组（会话里就是「没有用户」，[第 2-18 章第 1 节](user.md)的那些 Helper 于是走未登录分支），也可以自己抛异常或直接 `Show302` 回登录页。

组件会把「先后顺序 + 事件」串好（`EVENT_ACTION_USER_REGISTERING` → 服务 → `setCurrentUser()` → `EVENT_ACTION_USER_REGISTERED` → 可选的 302），所以**登录服务的实现里不要再发这几个事件**；要发的是 `EVENT_SERVICE_USER_*` 那一组（[第 2-12 章第 3 节](events.md)）。

## 6. 未登录的自定义处理

`Helper::UserId()` 这类调用在未登录时走 `GlobalUser::throwLoginOn()`，三选一（细节见[第 2-18 章第 2 节](user.md)）。默认是「302 到登录页」或「Ajax 出 JSON」，想改就配回调：

```php
'globaluser_need_login_callback' => function () {
    SystemWrapper::_()->_header('HTTP/1.1 401 Unauthorized', true, 401);
    echo json_encode(['error' => 'USER_NEED_LOGIN']);
},
// 回调返回后组件会 exit()，请求到此为止
```

## 7. 视图数据回调与头尾文件

- `globaluser_ext_view_data_callback`：在 `mergeViewData()` 里被调用一次，参数是当前视图数据数组，返回改过的数组——用来补「登录后视图」里你额外要的变量；
- `globaluser_view_file_header` / `globaluser_view_file_footer`：头/尾视图文件，值按 `App::getOverrideableFile('view', …)` 解析——**相对路径落在 `<应用 path>/view/` 下**（不是 `path_view`），而且**相位可覆盖**：第三个应用在自己的 `view/` 里放同名文件就能换掉用户页头尾（[第 3-5 章](overriding.md)）。

两个开关是视图数据，不是应用选项：`__use_logined_view_data`（要不要走登录后视图）与 `__use_logined_header_footer_file`（要不要套头尾文件），继承 `UserControllerBase` 时已经自动置真。

## 8. 怎么验证自己接对了

最快是照抄仓库里的可跑实现，然后跑测试：

```bash
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/GlobalUser/GlobalUserTest.php"
```

自己接完可以照这四条自检（前三条是调用方视角，第四条是「没接好」的兜底）：

1. `Helper::UserId()` 在未登录时返回 `0`（用 `Helper::UserId(false)` 试），登录后是你在会话里放的那个 `id`；
2. `Helper::UserService()->batchGetUsernames([1, 2])` 返回 `[id => 名字]`；
3. `Helper::User()->login($post)` 之后 `Helper::UserId()` 有值，且响应 302 到 `globaluser_url_home`；
4. 把 `ext` 里的那一行注释掉，`Helper::UserId()` 应该抛 `DuckPhpSystemException: No GlobalUser Provider.`——**吵出来**才说明「没接就是没接」，而不是静默放行。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| ` need ext options 'globaluser_login_session'` | 三个必需回调没配齐（或键名拼错） | 按第 2 节对照表补齐 |
| `DuckPhpSystemException: No GlobalUser Provider.` | `User::_()` 是桩：`ext` 没挂，或 `user_provider_enable` 被关 | 挂 `'ext' => [MyUser::class => true]` |
| 会话里有用户，`Helper::UserId()` 还是 0 | 会话键名/前缀与 `UserSessionTrait` 不一致 | 用 `UserSessionTrait`（键 `user`），或让你的实现返回正确的 `id` |
| 头尾文件找不到 | 值按 `<应用 path>/view/` 解析，不是 `path_view` | 用相对 `<path>/view/` 的名字，或给绝对路径 |
| 回调写成 `[Class::class, 'method']` 报「非静态方法」 | `globaluser_enable_callback_singleton` 被设成 `false` | 设回 `true`（默认），或把方法写成静态的 |
| 登录成功但没跳转 | `globaluser_is_authed_redirect` 被关，或 URL 选项没配 | 打开该选项，并配 `globaluser_url_home` / `globaluser_url_login` |

## 下一步

- [第 4-13 章 实现管理员系统](impl-admin.md)：后台那套的同构实现（少注册、多 `isSuper()`）。
- [第 2-18 章 使用用户系统](user.md)：本章面向调用方的那一半。
- 参考手册：[GlobalUser](../reference/GlobalUser-GlobalUser.md)、[User](../reference/GlobalUser-User.md)、[UserSessionInterface](../reference/GlobalUser-UserSessionInterface.md)、[UserServiceInterface](../reference/GlobalUser-UserServiceInterface.md)、[UserLoginServiceInterface](../reference/GlobalUser-UserLoginServiceInterface.md)、[UserSessionTrait](../reference/GlobalUser-UserSessionTrait.md)
