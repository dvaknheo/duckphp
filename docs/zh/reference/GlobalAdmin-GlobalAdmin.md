# DuckPhp\GlobalAdmin\GlobalAdmin

全局管理员组件。

## 简介

`GlobalAdmin` 是内置的 `AdminActionInterface` 实现，通过**回调配置**提供后台系统管理员功能的完整入口。你可以直接使用 `GlobalAdmin::class` 作为 `class_admin` 的值，也可以继承它。

## 选项

### 注册为提供者

    'class_admin' => GlobalAdmin::class,  // 或你的 MyAdmin implements AdminActionInterface

### 回调选项

`GlobalAdmin` 的方法由以下回调驱动，配置回调后即可使用：

| 选项 | 对应方法 | 说明 |
|---|---|---|
| `admin_callback_get_id` | `id()` | 获取当前管理员 ID，参数 `(bool $check_login)` |
| `admin_callback_get_name` | `name()` | 获取当前管理员名 |
| `admin_callback_get_data` | `data()` | 获取当前管理员数据数组 |
| `admin_callback_get_service` | `localService()` | 返回 `AdminServiceInterface` 实例 |
| `admin_callback_url_home` | `urlForHome()` | 自定义后台首页 URL 生成 |
| `admin_callback_url_login` | `urlForLogin()` | 自定义登录页 URL 生成 |
| `admin_callback_url_logout` | `urlForLogout()` | 自定义登出页 URL 生成 |

### 直接 URL 选项

如果未设置对应的 `url_*` 回调，则使用以下固定 URL：

| 选项 | 说明 |
|---|---|
| `admin_url_home` | 后台首页 URL |
| `admin_url_login` | 登录页 URL |
| `admin_url_logout` | 登出页 URL |

### 视图选项

| 选项 | 说明 |
|---|---|
| `admin_view_file_header` | 后台界面 header 视图文件 |
| `admin_view_file_footer` | 后台界面 footer 视图文件 |

### 回调单例模式

| 选项 | 默认值 | 说明 |
|---|---|---|
| `admin_enable_callback_singleton` | `true` | 启用后，回调数组 `[ClassName::class, 'method']` 自动转为 `ClassName::_()->method()` |

---

## 使用方式

### 作为子应用

当你需要把后台管理员系统封装给其他应用使用时：

下面的 `MyAdminProvider\System\AdminApp` 是你的管理员系统，`MainApp\System\App` 是主系统。

```php
namespace MainApp\System;
use MyAdminProvider\System\AdminApp;
class App extends DuckPhp
{
    public $options = [
        'app' =>[
            AdminApp::class => [
                'controller_url_prefix' =>'admin/',
                'admin_url_home' => '/admin/dashboard',
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
        $data['admin_name'] = Helper::AdminName();
        $data['url_logout'] = Helper::Admin()->urlForLogout();

        $data = Helper::Admin()->mergeViewData($data);
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
以上是来自 GlobalAdmin 提供者的页眉 <br>

你好 <span><?= __h($admin_name) ?></span> <a href="<?= $url_logout ?>">登出</a>

以下是来自 GlobalAdmin 提供者的页脚 <br>
<?php
if(isset($data['__view_data']['footer'])){
    echo $data['__view_data']['footer'];
}
?>
```

这个案例对应的 URL 是 `/admin/dashboard`。

我们通常通过 Controller 层的 `Helper::Admin` 来使用 GlobalAdmin。

当没登录时，会被子应用处理，通常会跳转到登录页面。

当成功登录，会显示当前管理员名和登出链接，同时把子应用设定的页眉页脚数据附加到视图里。

### URL 生成

```php
$url = Helper::Admin()->urlForLogin('/back');
$url = Helper::Admin()->urlForLogout();
$url = Helper::Admin()->urlForHome();
```

### 视图融合

```php
$data = Helper::Admin()->mergeViewData($input);
// $data['__view_data']['header'] 和 ['footer'] 已填充
```

### 配置回调

在子 App 中配置 `admin_callback_*` 指向自己的实现类：

```php
use MyAdminProvider\System;
class AdminApp extends DuckPhp
{
    public $options = [
        'admin_callback_get_id' => [AdminAction::class, 'id'],
        'admin_callback_get_name' => [AdminAction::class, 'name'],
        'admin_callback_get_data' => [AdminAction::class, 'data'],
        'admin_callback_get_service' => [AdminAction::class, 'service'],

        'admin_url_login' => 'admin/login',
        'admin_url_logout' => 'admin/logout',
        'admin_url_home' => 'admin/dashboard',
    ];
}
```

> `admin_url_*` 的 URL 一般写成相对路径。

回调说明：
- `admin_callback_get_id` / `get_name` / `get_data`：指向你的 `AdminAction` 类，从 Session/Token 读取当前管理员信息
- `admin_callback_get_service`：指向 `AdminAction::service()`，返回 `AdminServiceInterface` 实例
- `admin_callback_url_*`：可选的 URL 生成回调，不设置时走 `admin_url_*` 固定 URL

### AdminAction 实现示例

```php
use MyAdminProvider\Controller;
use MyAdminProvider\Business\AdminBusiness;
class AdminAction
{
    // 会被 admin_callback_get_id 调用
    public function id($check_login = true)
    {
        $id = $_SESSION['admin_id'] ?? null;
        if ($check_login && !$id) {
            throw new \Exception('未登录');
        }
        return $id;
    }
    public function name($check_login = true): string
    {
        return $_SESSION['admin_name'] ?? '';
    }
    public function data($check_login = true): array
    {
        return $_SESSION['admin_data'] ?? [];
    }
    // 会被 admin_callback_get_service 调用
    public function service()
    {
        return AdminBusiness::_();  // AdminBusiness implements AdminServiceInterface
    }
}
```

对应的，当你设置 `'admin_callback_get_id' => [AdminAction::class, 'id']` 后，主应用调用 `Helper::AdminId()` 时实际执行的就是 `AdminAction::id()`。

### 所有应用的 Controller 中调用

所有应用的 Controller 通过 `Helper::Admin()` 即可获得 `AdminActionInterface` 实例。

```php
$adminId = Helper::AdminId(true);             // => GlobalAdmin::_()->id(); => AdminAction::_()->id();（未登录抛异常）
$adminName = Helper::AdminName();             // => GlobalAdmin::_()->name(); => AdminAction::_()->name()
$admin = Helper::Admin();                     // => GlobalAdmin 实例
$adminService = Helper::AdminService();       // => GlobalAdmin::_()->service()
```

### 服务委托

`GlobalAdmin::_()->service()` 返回 `AdminServiceInterface` 的 PhaseProxy：

```php
$service = Helper::Admin()->service();
$service->checkAccess($adminId, __CLASS__, __METHOD__);
$service->isSuper($adminId);
$service->log($adminId, '操作', 'audit');
```

---
## 全部选项

        'admin_url_home' => null,
        'admin_url_login' => null,
        'admin_url_logout' => null,

        'admin_view_file_header' => null, // 'inc-head',
        'admin_view_file_footer' => null, // 'inc-foot',

        'admin_enable_callback_singleton' => true,
        'admin_callback_get_id' => null, //[AdminAction::class,'id'],
        'admin_callback_get_name' => null, //[AdminAction::class,'name'],
        'admin_callback_get_data' => null, //[AdminAction::class,'data'],
        'admin_callback_get_service' => null, //[AdminAction::class,'service'],

        'admin_callback_url_home' => null,
        'admin_callback_url_login' => null,
        'admin_callback_url_logout' => null,

## 方法列表

| 方法 | 说明 |
|---|---|
| `id(bool $check_login = true)` | 获取当前管理员 ID。`true` 且未登录时抛异常 |
| `name(bool $check_login = true): string` | 获取当前管理员名 |
| `data(bool $check_login = true): array` | 获取当前管理员数据 |
| `service()` | 返回 `AdminServiceInterface` 的 PhaseProxy |
| `localService()` | 返回本地 `AdminServiceInterface` 实例 |
| `urlForHome(?string $url_back, ?array $ext): string` | 后台首页 URL |
| `urlForLogin(?string $url_back, ?array $ext): string` | 登录页 URL |
| `urlForLogout(?string $url_back, ?array $ext): string` | 登出页 URL |
| `mergeViewData(array $input): array` | 融合视图头尾数据到 `$input['__view_data']` |
| `checkAccess($class, $method, $url)` | 检查权限，委托给 `localService()->checkAccess()` |
| `log($string, $type, $ext)` | 记录管理员操作日志，委托给 `localService()->log()` |
| `isSuper(): bool` | 判断是否超级管理员，委托给 `localService()->isSuper()` |


### 公共方法

    public function id(bool $check_login = true)
获取当前管理员 ID。`true` 且未登录时抛异常

    public function name(bool $check_login = true): string
获取当前管理员名

    public function data(bool $check_login = true): array
获取当前管理员数据

    public function service()
返回 AdminServiceInterface 实例（PhaseProxy）

    public function localService()
返回本地 AdminServiceInterface 实例

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
获取后台首页 URL

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
获取登录页 URL

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
获取登出页 URL

    public function mergeViewData(array $input): array
融合视图头尾数据到 input['__view_data']

    public function checkAccess(string $class, string $method, ?string $url = null)
检查权限，委托给 localService()->checkAccess()

    public function log(string $string, ?string $type = null, array $ext = [])
记录日志，委托给 localService()->log()

    public function isSuper(): bool
判断是否超级管理员，委托给 localService()->isSuper()

### 受保护方法

    protected function run_callback_by_key(string $key, ...$args)
回调 $options[$key]

    protected function go_url(string $key_callback, string $key_url, ?string $url_back, ?array $ext)
URL 生成路由：优先回调，回退固定 URL

## 相关链接

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md)
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md)
