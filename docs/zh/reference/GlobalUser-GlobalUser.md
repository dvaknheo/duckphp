# DuckPhp\GlobalUser\GlobalUser

全局用户组件。

## 简介

`GlobalUser` 是内置的 `UserActionInterface` 实现，通过**回调配置**提供用户系统的完整功能。你可以直接使用 `GlobalUser::class` 作为 `class_user` 的值，也可以继承它。

## 选项
### 注册为提供者

    'class_user' => GlobalUser::class,  // 或你的 MyUser implements UserActionInterface

### 回调选项

`GlobalUser` 的方法由以下回调驱动，配置回调后即可使用：

| 选项 | 对应方法 | 默认值说明 |
|---|---|---|
| `user_callback_get_id` | `id()` | 获取当前用户 ID，参数 `(bool $check_login)` |
| `user_callback_get_name` | `name()` | 获取当前用户名 |
| `user_callback_get_data` | `data()` | 获取当前用户数据数组 |
| `user_callback_get_service` | `localService()` | 返回 `UserServiceInterface` 实例 |
| `user_callback_url_home` | `urlForHome()` | 自定义首页 URL 生成 |
| `user_callback_url_regist` | `urlForRegist()` | 自定义注册页 URL 生成 |
| `user_callback_url_login` | `urlForLogin()` | 自定义登录页 URL 生成 |
| `user_callback_url_logout` | `urlForLogout()` | 自定义登出页 URL 生成 |

### 直接 URL 选项

如果未设置对应的 `url_*` 回调，则使用以下固定 URL：

| 选项 | 说明 |
|---|---|
| `user_url_home` | 首页 URL |
| `user_url_regist` | 注册页 URL |
| `user_url_login` | 登录页 URL |
| `user_url_logout` | 登出页 URL |

### 视图选项

| 选项 | 说明 |
|---|---|
| `user_view_file_header` | 用户界面 header 视图文件路径 |
| `user_view_file_footer` | 用户界面 footer 视图文件路径 |

### 回调单例模式

| 选项 | 默认值 | 说明 |
|---|---|---|
| `user_enable_callback_singleton` | `true` | 启用后，回调数组 `[ClassName::class, 'method']` 自动转为 `ClassName::_()->method()` |

---

## 使用方式

### 作为子应用

当你需要把自己的用户系统封装给其他应用使用时：
比如 

下面的 `MyUserProvider\System\UserApp` 是你自己的用户系统。
下面的 `MainApp\System\App` 是主系统。

```php
namespace MainApp\System;
use MyUserProvider\System\UserApp;
class App extends DuckPhp
{
    public $options = [
        'app' =>[
            UserApp::class => [
                'controller_url_prefix' =>'user/',
                'user_url_home' => '/',  // 登录之类的成功后到网站首页
            ],
        ]
    ];
}
```

---


### 例子

```php
namespace MainApp\Controller;
class managerController
{
    public function dashboard()
    {
        
        $data =[];
        $data['user_name'] = Helper::UserName();
        $data['url_logout'] = Helper::User()->urlForLogout();

        $data = Helper::User()->mergeViewData($data);
        Helper::Show($data,'dashboard');
    }
}
```

```php
<?php
// view/dashboard.php
if(isset($data['__view_data']['header'])){
    echo $data['__view_data']['header'];
}
?>
以上是来自 GlobalUser 提供者的页眉 <br>

你好  <span> <?=__h($user_name)?></span> <a href="<?=$url_logout">登出</a>

以下是来自 GlobalUser 提供者的页脚 <br>
<?php
if(isset($data['__view_data']['footer'])){
    echo $data['__view_data']['footer'];
}
?>

```
这个案例，对应的 url 是
`/manager/dashboard`

我们通常通过 Controller 层的 Helper::User 来使用 GlobalUser

当没登录的时候；会被子应用处理。通常情况下会跳转到登录页面

当成功登录，会显示当前用户名。和登出链接。

同时会把子应用设定的页眉页脚数据附加到视图里。

### URL 生成

```php
$url = Helper::User()->urlForLogin('/back');  // 登录后跳回 /back
$url = Helper::User()->urlForLogout();
$url = Helper::User()->urlForHome();
$url = Helper::User()->urlForRegist();
```
### 视图融合
```php
$data = GlobalUser::_()->mergeViewData($input);
// $data['__view_data']['header'] 和 ['footer'] 已填充
```
这个方法用在编写用户后台时，主应用通过 `mergeViewData()` 获取提供者的页眉/页脚：


### 配置回调

在子 App 中配置 `user_callback_*` 指向自己的实现类。回调数组 `[ClassName::class, 'method']` 在 `user_enable_callback_singleton` 开启时会自动实例化为单例：

```php
use MyUserProvider\System;
class UserApp extends DuckPhp
{
    public $options = [
        'user_callback_get_id' => [UserAction::class, 'id'],
        'user_callback_get_name' => [UserAction::class, 'name'],
        'user_callback_get_data' => [UserAction::class, 'data'],
        'user_callback_get_service' => [UserAction::class, 'service'],
        
        'user_url_login' => 'login',
        'user_url_logout' => 'logout',
        'user_url_home' => 'home',
        'user_url_regist' => 'regist',
    ];
}
```
> `user_url_*` 的 URL 一般写成相对路径。


回调说明：
- `user_callback_get_id`/`get_name`/`get_data`：指向你的 `UserAction` 类，从 Session/Token 读取当前用户信息
- `user_callback_get_service`：指向 `UserAction::service()`，返回 `UserServiceInterface` 实例
- `user_callback_url_*`：可选的 URL 生成回调，不设置时走 `user_url_*` 固定 URL

### UserAction 实现示例

```php
use MyUserProvider\Controller;
use MyUserProvider\Business\UserBusiness;
class UserAction
{
    // 会被 user_callback_get_id 调用
    public function id($check_login = true)
    {
        $id = $_SESSION['user_id'] ?? null;
        if ($check_login && !$id) {
            throw new \Exception('未登录');
        }
        return $id;
    }
    public function name($check_login = true): string
    {
        return $_SESSION['user_name'] ?? '';

    }
    public function data($check_login = true): array
    {
        return $_SESSION['user_data'] ?? [];
    }
    // 会被 user_callback_get_service 调用
    public function service()
    {
        return UserBusiness::_();  // UserBusiness implements UserServiceInterface
    }
}
```
对应的，当你设置 `user_callback_get_id' => [UserAction::class, 'id']` 后，主应用或其他应用调用 `Helper::UserId()` 时，实际执行的就是 `UserAction::id()`。


### 所有应用的 Controller 中调用

所有应用的 Controller 通过 `Helper::User()` 即可获得 `UserActionInterface` 实例，无需关心底层实现。

```php
$userId = Helper::UserId(true);             // => GlobalUser::_()->id(); => UserAction::_()->id();（未登录抛异常）
$userName = Helper::UserName();             // => GlobalUser::_()->name(); => UserAction::_()->name()
$user = Helper::User();                     // => GlobalUser 实例
$userService = Helper::UserService();       // => GlobalUser::_()->service()
```

### 服务委托

`GlobalUser::_()->service()` 返回 `UserServiceInterface` 的 PhaseProxy，可在 Business 层安全调用：

```php

$service = Helper::User()->service();
$service->checkAccess($userId, __CLASS__, __METHOD__);
$service->log($userId, '操作', 'audit');
$usernames = $service->batchGetUsernames([1, 2, 3]);
```

---
## 全部选项

        'user_url_home' => null,
        'user_url_regist' => null,
        'user_url_login' => null,
        'user_url_logout' => null,
        
        'user_view_file_header' => null, // 'inc-head',
        'user_view_file_footer' => null, // 'inc-foot',
        
        'user_enable_callback_singleton' => true,
        'user_callback_get_id' => null, //[UserAction::class,'id'],
        'user_callback_get_name' => null, //[UserAction::class,'name'],
        'user_callback_get_data' => null, //[UserAction::class,'data'],
        'user_callback_get_service' => null, //[UserAction::class,'service'],

        'user_callback_url_home' => null,
        'user_callback_url_regist' => null,
        'user_callback_url_login' => null,
        'user_callback_url_logout' => null,

## 方法列表

| 方法 | 说明 |
|---|---|
| `id(bool $check_login = true)` | 获取当前用户 ID。`true` 且未登录时抛异常 |
| `name(bool $check_login = true): string` | 获取当前用户名 |
| `data(bool $check_login = true): array` | 获取当前用户数据 |
| `service()` | 返回 `UserServiceInterface` 的 PhaseProxy |
| `localService()` | 返回本地 `UserServiceInterface` 实例 |
| `urlForHome(?string $url_back, ?array $ext): string` | 首页 URL |
| `urlForRegist(?string $url_back, ?array $ext): string` | 注册页 URL |
| `urlForLogin(?string $url_back, ?array $ext): string` | 登录页 URL |
| `urlForLogout(?string $url_back, ?array $ext): string` | 登出页 URL |
| `mergeViewData(array $input): array` | 融合视图头尾数据到 `$input['__view_data']` |
| `checkAccess($class, $method, $url)` | 检查权限，委托给 `localService()->checkAccess()` |
| `log($string, $type, $ext)` | 记录操作日志，委托给 `localService()->log()` |
| `batchGetUsernames(array $ids): array` | 批量获取用户名，委托给 `localService()->batchGetUsernames()` |


### 公共方法
    public function id(bool $check_login = true)
获取当前用户 ID。`true` 且未登录时抛异常

//... {待AI补充完整，引用格式都和代码里一致，方便脚本自动化}

### 受保护方法

    protected function run_callback_by_key(string $key, ...$args)
回调 $options[$key]

//... {待AI补充完整，引用格式都和代码里一致，方便脚本自动化}

## 相关链接

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md)
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md)
