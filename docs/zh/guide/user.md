# 2-18 使用用户系统

> 解决什么问题：在你的控制器 / 业务代码里回答「现在是谁」「他能不能做这件事」「这几个 id 分别是谁」。
> 前置：[第 2-3 章 控制器](controllers.md)、[第 2-9 章 会话](session.md)、[第 2-11 章 异常与错误处理](exception.md)。预计 15 分钟。
> 本章只讲**怎么用**；「用户系统是怎么接进来的」（三个实现 + 选项 + `ext` 挂载）见[第 4-12 章 实现用户系统](impl-user.md)。**没接之前**这些入口会直接抛 `DuckPhpSystemException`（不会静默返回 0）。
> 可跑资产：`tests/Foundation/Controller/UserControllerBaseTest.php`、`tests/GlobalUser/GlobalUserTest.php`。

## 最小示例

控制器里就问这三样：

```php
// MyProj\Controller\UserController
public function center()
{
    $userId = Helper::UserId();          // 当前用户 id；未登录 → 302 到登录页并结束请求
    $name   = Helper::UserName();        // 当前用户名
    $url    = Helper::User()->urlForLogin();   // 需要登录页地址时

    Helper::Show(get_defined_vars(), 'user/center');
}
```

业务层（Business）拿不到「当前是谁」，但能拿到**用户服务**：

```php
// MyProj\Business\NoteService
public function listWithAuthors(int $userId): array
{
    $notes = NoteModel::_()->listByUser($userId);
    $names = Helper::UserService()->batchGetUsernames(array_column($notes, 'user_id'));

    foreach ($notes as &$note) {
        $note['author'] = $names[$note['user_id']] ?? '';
    }
    return $notes;
}
```

> 这里两个 `Helper::` 是**不同层**的类：控制器里是 `MyProj\Controller\Helper`（继承 [`ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md)），业务里是 `MyProj\Business\Helper`（继承 [`BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md)）。控制器层有「人」，业务层只有「服务」——见下一节。

## 机制说明

### 1. 两个入口：控制器拿「人」，业务拿「服务」

| 你在哪一层 | 入口 | 拿到什么 | 能做什么 |
|---|---|---|---|
| Controller | `Helper::User()` | [`UserActionInterface`](../reference/GlobalUser-UserActionInterface.md)（当前用户对象） | 见第 2 节的表 |
| Controller | `Helper::UserId()` / `Helper::UserName()` | `int\|string` / `string` | 只要 id 或名字时的快捷方式 |
| Controller | `Helper::UserService()` | [`UserServiceInterface`](../reference/GlobalUser-UserServiceInterface.md) | `canAccess()` / `log()` / `batchGetUsernames()` |
| Business | `Helper::UserService()` | 同上，**同一个服务** | 同上 |
| 任何地方 | [`User::_()`](../reference/GlobalUser-User.md) | 当前用户对象（`Helper::User()` 就是它） | 同上，少一层转发 |

为什么业务层没有 `User()` / `UserId()`：**「现在是谁」是请求上下文**（会话 + 当前路由），属于控制器该关心的事。业务方法要用户信息就**显式传参**（`function listWithAuthors(int $userId)`），这样业务也能被 CLI、定时任务、队列复用——这正是[第 2-1 章](layers.md)那条分层规则。

### 2. `Helper::User()` 能做什么

| 方法 | 返回 | 说明 |
|---|---|---|
| `id(bool $check_login = true)` | `int\|string` | 当前用户 id；未登录见第 3 节 |
| `name(bool $check_login = true)` | `string` | 当前用户名 |
| `data(bool $check_login = true)` | `array` | 当前用户整条数据（会话里存什么就给什么） |
| `canAccess(?string $url = null, ?string $class = null, ?string $method = null)` | `bool` | 权限判断；**不传参就用当前路由**的类/方法/URL；未登录直接 `false` |
| `log(string $string, ?string $type = null, array $ext = [])` | — | 记一条用户操作日志，落到你的 `UserServiceInterface::log()` |
| `urlForHome()` / `urlForLogin($url_back = null)` / `urlForLogout()` / `urlForRegister()` | `string` | 站内 URL；`urlForLogin('/order/1')` 会带上 `?b=` 回跳参数 |
| `service()` | `UserServiceInterface` | 等价于 `Helper::UserService()` |
| `batchGetUsernames(array $ids)` | `array` | 等价于 `Helper::UserService()->batchGetUsernames()`（`[id => 名字]`） |

> 每个方法的完整签名与契约见[参考手册](../reference/GlobalUser-UserActionInterface.md)，这里只是「调用方视角」的摘要。

### 3. 未登录时会发生什么

`id()` / `name()` / `data()` 的 `$check_login` 默认是 `true`，这时**未登录不会返回 0**，而是按你的配置三选一，**三条路最后都结束请求**（`exit()`）：

| 你的配置 | 表现 |
|---|---|
| 配了 `globaluser_need_login_callback` | 跑你的回调（例如输出 401 JSON），然后结束 |
| 没配、且不是 Ajax | `302` 到 `urlForLogin(当前 REQUEST_URI 的 path)`，浏览器回登录页 |
| 没配、且是 Ajax | 输出 `{"error_code":-1,"error_message":"NEED_LOGIN"}` |

所以「调用方拿不到返回值」是**故意**的：用默认行为就是「未登录 → 跳登录页」，你什么都不用写。要让代码继续往下走、由你判断，就传 `false`：

```php
$userId = Helper::UserId(false);          // 未登录返回 0，不跳转、不结束请求
if (!$userId) {
    Helper::Show302(Helper::User()->urlForLogin('user/center'));   // 回跳地址自己给
    return;
}
```

`data()` 的返回值取决于你的会话实现；用自带的 [`UserSessionTrait`](../reference/GlobalUser-UserSessionTrait.md) 时是 `['id' => …, 'name' => …]`。

### 4. `Helper::UserService()`：控制器与业务都能用

服务是「以 `$user_id` 显式传参」的纯逻辑，不读会话，所以两层都能安全调用（内部经 [`PhaseProxy`](../reference/Component-PhaseProxy.md) 包装，跨子应用相位也对）：

```php
Helper::UserService()->canAccess($userId, $url, $class, $method);   // 参数顺序：$url 在前
Helper::UserService()->log($userId, '导出了报表', 'export', ['rows' => 120]);
Helper::UserService()->batchGetUsernames([3, 5, 8]);                // [3 => '张三', …]
```

控制器里如果想「拿当前用户判当前请求」，用不带参数的 `Helper::User()->canAccess()` 更省事（它会替你补上当前路由的类/方法/URL 和当前用户 id）。

### 5. 登录 / 注册 / 登出三个动作

```php
public function login()
{
    if (Helper::POST()) {
        Helper::User()->login(Helper::POST());     // 校验+落库交给登录服务；成功后 302 到 globaluser_url_home
    }
    Helper::Show([], 'user/login');
}
public function register()
{
    if (Helper::POST()) {
        Helper::User()->register(Helper::POST());  // 同上，成功后也 302 到 globaluser_url_home
    }
    Helper::Show([], 'user/register');
}
public function logout()
{
    Helper::User()->logout();                      // 清会话，302 到 globaluser_url_login
}
```

三个动作都会**自动 302**（由 `globaluser_is_authed_redirect` 控制，默认开）；关掉它就可以自己决定跳哪。想监听「登录成功」，用事件而不是改这些方法：`EVENT_ACTION_USER_LOGINED` 等那组（[第 2-12 章](events.md)）。

### 6. 用户页面的头尾：两个视图开关

用户页面（用户中心、个人资料）通常要带用户头尾，并在视图里拿到 `__logined_id` / `__logined_name` / `__logined_url_home` / `__logined_url_logout` / `__logined_data`。开关是**视图数据**：

| 视图数据键 | 作用 |
|---|---|
| `__use_logined_view_data` | 为真才注入上面这些 `__logined_*`（否则就是普通渲染） |
| `__use_logined_header_footer_file` | 为真才把用户页头/尾模板套上 |
| `__logined_render_header_footer` | 缺省视为真；置 `false` 可「只注入数据、不套头尾模板」 |

**继承 [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md) 时前两个键已经自动置真**，你不用写；自己写的基类想开就照抄：

```php
Helper::assignViewData('__use_logined_view_data', true);
Helper::assignViewData('__use_logined_header_footer_file', true);
```

判定与渲染都在 [`DuckPhp::_Show()`](../reference/DuckPhp.md) 里：当前路由的控制器实现 [`UserControllerInterface`](../reference/GlobalUser-UserControllerInterface.md) 时才接管，头尾模板来自 `globaluser_view_file_header/footer`（**相位可覆盖**，第三个应用能换掉它，见[第 3-5 章](overriding.md)）。

> 头尾模板本身怎么配属于「实现」侧：[第 4-12 章第 7 节](impl-user.md)。

## 常见写法

**① 需要登录的页面（自己接管跳转）**

```php
public function profile()
{
    $userId = Helper::UserId(false);
    if (!$userId) {
        Helper::Show302(Helper::User()->urlForLogin('user/profile'));
        return;
    }
    Helper::Show(get_defined_vars(), 'user/profile');
}
```

**② 不需要登录、但登录了要显示名字**

```php
$name = Helper::UserName(false);     // 未登录是空串，不会跳转
Helper::Show(['name' => $name], 'home');
```

**③ 判权限（当前请求）**

```php
if (!Helper::User()->canAccess()) {      // 无参：取当前路由 + 当前用户
    Helper::Show302(Helper::Url('/'));   // 或者交给 UserControllerBase 的 onNeedPermission()
    return;
}
```

**④ 视图里用登录信息**

```php
<?php if (!empty($__logined_id)): ?>
    你好，<?= __h($__logined_name) ?> · <a href="<?= __h($__logined_url_logout) ?>">退出</a>
<?php endif; ?>
```

**⑤ 批量取名字（业务层）**

```php
$names = Helper::UserService()->batchGetUsernames($userIds);   // [id => 名字]，一次查完，别在循环里查
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 调用 `Helper::UserId()` 页面直接 302 走了 | `$check_login` 默认为 `true`，未登录就跳登录页并结束请求 | 要自己判断就传 `false`（第 3 节） |
| 业务层里写 `Helper::UserId()` 报「方法不存在」 | 业务层 Helper 没有这个方法（只有 `UserService()`） | 由控制器把 `$userId` 传进业务方法 |
| `DuckPhpSystemException: No GlobalUser Provider.` | 用户系统还没接（`ext` 没挂） | 见[第 4-12 章](impl-user.md) |
| `Helper::User()->canAccess()` 总返回 `false` | 未登录，或你的 `UserServiceInterface::canAccess()` 规则如此 | 先确认 `Helper::UserId(false)`，再查服务实现 |
| 页面没有用户头尾 | 控制器没实现 `UserControllerInterface`，或两个视图数据键没置真 | 继承 `UserControllerBase`，或自己 `assignViewData`（第 6 节） |
| 视图里 `$__logined_name` 未定义 | `__use_logined_view_data` 没开（走的是普通渲染） | 开这个键，或改用 `Helper::UserName(false)` |

## 下一步

- [第 2-19 章 使用管理员系统](admin.md)：后台那套入口，与本章同构。
- [第 4-12 章 实现用户系统](impl-user.md)：本项目的用户系统由谁提供、选项怎么配。
- [第 2-12 章 事件系统](events.md)：`EVENT_ACTION_USER_*`（注册/登录/登出前后）怎么监听。
- [第 2-11 章 异常与错误处理](exception.md)：登录失效、权限不够怎么变成跳转或错误页。
- 参考手册：[User](../reference/GlobalUser-User.md)、[UserActionInterface](../reference/GlobalUser-UserActionInterface.md)、[GlobalUser](../reference/GlobalUser-GlobalUser.md)、[UserLoginActionInterface](../reference/GlobalUser-UserLoginActionInterface.md)、[UserServiceInterface](../reference/GlobalUser-UserServiceInterface.md)、[UserSessionTrait](../reference/GlobalUser-UserSessionTrait.md)
