# Helper 助手类

DuckPHP 在各层提供了 `Helper` 静态助手类，用于封装对框架组件的访问。每个层的 `Helper` 只能访问该层允许使用的功能。当你掌握了应用结构之后，看懂这些助手函数就基本能完成日常开发了。

在学习 Helper 之前，建议先阅读 [项目结构与编码规则](project-structure.md) 了解各层职责。

## Helper 类的分层

| 命名空间 | 使用层级 | 主要职责 |
|---|---|---|
| `DuckPhp\Foundation\Controller\Helper` | Controller 层 | 请求输入、视图输出、路由参数、HTTP 响应 |
| `DuckPhp\Foundation\Business\Helper` | Business 层 | 业务异常、缓存、配置读取、事件触发 |
| `DuckPhp\Foundation\Model\Helper` | Model 层 | 数据库连接、SQL 分页 |
| `DuckPhp\Foundation\System\Helper` | System 层 | 路由钩子、Session、Redis、CLI 参数、系统函数 |

> **注意**：不同层级的 `Helper` 类功能不同，不要混淆使用。例如 Controller 层应使用 `DuckPhp\Foundation\Controller\Helper`，Model 层应使用 `DuckPhp\Foundation\Model\Helper`。

> **命名约定**：大写开头的方法名是常用方法；小写开头的方法名是非常用方法。Controller 助手类里还有一些和 PHP 全局函数同名的全小写方法（如 `header()`、`setcookie()`、`exit()`），用于替代同名全局函数以保证兼容性。

## Controller Helper

Controller 层的 `Helper` 处理 HTTP 请求、视图渲染和响应。

### 请求数据

```php
use DuckPhp\Foundation\Controller\Helper;

$id = Helper::GET('id', 0);              // 获取 $_GET['id']，默认 0
$name = Helper::POST('name', '');        // 获取 $_POST['name']，默认空字符串
$page = Helper::REQUEST('page', 1);      // 获取 $_REQUEST['page']
$host = Helper::SERVER('HTTP_HOST');     // 获取 $_SERVER['HTTP_HOST']
$token = Helper::COOKIE('token');        // 获取 $_COOKIE['token']
```

> 建议调用这些助手方法，而不是直接使用 PHP 原生的超全局变量，以便兼容不同的运行环境（如 Swoole、WorkerMan 等）。

### 视图渲染

```php
// 渲染视图（自动包含页眉页脚）
Helper::Show(get_defined_vars(), 'user/profile');

// 渲染视图片段（不包含页眉页脚）
Helper::Display('user/profile', $data);

// 渲染为字符串
$html = Helper::Render('user/profile', $data);

// 设置页眉页脚
Helper::setViewHeadFoot('_sys/header', '_sys/footer');

// 赋值视图变量
Helper::assignViewData('site_name', 'MySite');
```

- `Show()` 用于控制器输出，第二个参数为 `null` 时会自动查找 `{控制器}/{方法}` 对应的视图文件。
- `Render()` 常用于某个区块的特殊输出处理。
- `assignViewData()` 一般用于基类中，为页眉页脚提供公共数据。

### URL 与路由

```php
$url = Helper::Url('user/login');        // 生成 URL
$res = Helper::Res('css/style.css');     // 生成资源 URL
$domain = Helper::Domain(true);          // 获取当前域名
$pathInfo = Helper::PathInfo();          // 获取 PATH_INFO
$param = Helper::Parameter('id');        // 获取路由参数
```

### HTTP 响应

```php
Helper::Show302('user/login');           // 302 重定向
Helper::Show404();                       // 显示 404
Helper::ShowJson(['code' => 0]);        // 输出 JSON
Helper::header('Content-Type: application/json');
Helper::setcookie('name', 'value', 3600);
Helper::exit();
```

> `header()`、`setcookie()`、`exit()` 等全小写方法用于替代 PHP 同名全局函数，保证在不同运行环境下的兼容性。

### 异常与事件

```php
// 条件抛 Controller 异常
Helper::ControllerThrowOn(!$user, '请先登录', 403);

// 注册异常处理器
Helper::assignExceptionHandler(MyException::class, function ($ex) {
    // 处理异常
});

// 触发事件
Helper::FireEvent('user_login', $userId);
Helper::OnEvent('user_login', function ($userId) {
    // 监听事件
});
```

### 分页

```php
$total = UserModel::_()->getTotal();
$html = Helper::PageHtml($total, ['page_size' => 20]);
$pageNo = Helper::PageNo();
```

### 全局用户/管理员

```php
$userId = Helper::UserId();              // 当前用户 ID
$user = Helper::User();                  // 当前用户对象
$userName = Helper::UserName();          // 当前用户名
$userService = Helper::UserService();    // 用户服务

$adminId = Helper::AdminId();            // 当前管理员 ID
$admin = Helper::Admin();                // 当前管理员对象
$adminService = Helper::AdminService();  // 管理员服务
```

> 全局用户/管理员属于高级话题，通常在接入第三方管理系统或 `GlobalUser`/`GlobalAdmin` 扩展时使用。

## Business Helper

Business 层的 `Helper` 提供无状态的业务辅助功能。

### 配置与异常

```php
use DuckPhp\Foundation\Business\Helper;

$dbConfig = Helper::Setting('database_list');
$appConfig = Helper::Config('app', 'key', 'default');

// 条件抛 Business 异常
Helper::BusinessThrowOn($balance < $amount, '余额不足', 1001);
```

### 缓存与事件

```php
$cache = Helper::Cache();
$cache->set('key', 'value', 3600);
$value = $cache->get('key', 'default');

Helper::FireEvent('order_created', $orderId);
```

### 路径

```php
$projectPath = Helper::PathOfProject();  // 工程路径
$runtimePath = Helper::PathOfRuntime();  // 可写的运行时路径
```

### 安全调用

```php
$result = Helper::XpCall(function () {
    return SomeService::_()->riskyOperation();
});
```

## Model Helper

Model 层的 `Helper` 只提供数据库访问相关功能。

```php
use DuckPhp\Foundation\Model\Helper;

// 获取数据库连接
$db = Helper::Db(0);                     // 指定 tag
$dbRead = Helper::DbForRead();           // 读连接
$dbWrite = Helper::DbForWrite();         // 写连接

// 查询
$rows = Helper::DbForRead()->fetchAll("SELECT * FROM users WHERE status=?", 1);
Helper::DbForWrite()->execute("UPDATE users SET name=? WHERE id=?", 'foo', 1);

// 分页 SQL
$sql = Helper::SqlForPager("SELECT * FROM users", $page, 10);
$countSql = Helper::SqlForCountSimply("SELECT * FROM users");
```

## System Helper

System 层的 `Helper` 提供框架级别的系统功能，通常在 `App` 或系统配置中使用。以下内容偏高级，详细说明可参考源码注释和参考文档。

### 路由钩子

```php
use DuckPhp\Foundation\System\Helper;

Helper::addRouteHook(function ($path_info) {
    if ($path_info === '/special') {
        echo 'Special route';
        return true;
    }
    return false;
}, 'prepend-inner');

Helper::assignRoute('/hello', function () {
    echo 'Hello';
});

Helper::assignRewrite('article/123', 'blog/show?id=123');
```

### Session 操作

```php
Helper::session_start();
Helper::SessionSet('user_id', 123);
$userId = Helper::SessionGet('user_id', 0);
Helper::SessionUnset('user_id');
```

### 数据库与 Redis

```php
Helper::DbCloseAll();                    // 关闭所有数据库连接
$redis = Helper::Redis(0);               // 获取 Redis 连接
```

### 系统函数包装

```php
Helper::header('Content-Type: text/html');
Helper::setcookie('name', 'value', 3600);
Helper::exit(0);
Helper::register_shutdown_function(function () {
    // 清理工作
});
```

### CLI 参数

```php
$params = Helper::getCliParameters();
```

## Helper 使用原则

1. **按层使用**：Controller 用 `Controller\Helper`，Business 用 `Business\Helper`，Model 用 `Model\Helper`
2. **不跨层调用**：Business 层不应调用 `Controller\Helper`，Model 层不应调用 `Business\Helper`
3. **保持无状态**：Business 和 Model 层的 Helper 操作不应依赖请求上下文
4. **优先使用 Foundation Helper**：避免在 Controller/Business/Model 中直接调用 `DuckPhp` 命名空间下的类

## Helper 与全局函数的关系

很多全局函数实际上是通过 `CoreHelper` 代理的，与 Helper 类访问的是同一套组件：

```php
// 以下两者等价
Helper::Url('user/login');
__url('user/login');

// 以下两者等价
Helper::ShowJson($data);
__json($data);
```

全局函数更适合在视图中使用，而 Helper 类更适合在控制器和业务类中使用。
