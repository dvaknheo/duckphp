# DuckPhp\Foundation\Helper

通用 Helper 类。

## 简介

`DuckPhp\Foundation\Helper` 是一个综合型 Helper 类，同时聚合了 `ModelHelperTrait`、`BusinessHelperTrait`、`ControllerHelperTrait` 和 `AppHelperTrait`。它通过方法冲突裁决，优先暴露适合通用场景的方法组合，既能在任意层使用，又避免同名方法冲突。

## 选项

无。

## 使用方式

### 静态调用

```php
use DuckPhp\Foundation\Helper;

// 数据库
$db = Helper::Db();

// 配置
$config = Helper::Config('app');

// 渲染视图
Helper::Render('index', ['name' => 'DuckPhp']);

// 路由 URL
$url = Helper::Url('/home');

// 获取请求参数
$id = Helper::GET('id', 0);
```

## 注意事项

1. 该类没有自有方法，所有方法均来自其引入的 Trait。
2. 引入多个 Trait 时存在方法冲突，已用 `insteadof` 裁决：
   - `Setting`、`Config`、`XpCall`、`FireEvent`、`OnEvent`、`PathOfProject`、`PathOfRuntime` 优先使用 `BusinessHelperTrait`。
   - `header`、`setcookie`、`exit` 优先使用 `ControllerHelperTrait`。
   - `AdminService`、`UserService` 优先使用 `ControllerHelperTrait`（覆盖 `BusinessHelperTrait`）。
3. 由于功能覆盖较广，在明确分层时建议优先使用 `Business\Helper`、`Controller\Helper`、`Model\Helper` 等专用 Helper。

## 方法列表

### 公共方法

来自各 Trait 的公共方法均可用，主要包括：

| 方法 | 说明 |
|---|---|
| `Db($tag = null)` | 获取数据库对象 |
| `DbForRead()` | 获取读库对象 |
| `DbForWrite()` | 获取写库对象 |
| `SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string` | 分页 SQL |
| `SqlForCountSimply(string $sql): string` | 计数 SQL |
| `Setting($key = null, $default = null)` | 读取设置 |
| `Config($file_basename, $key = null, $default = null)` | 读取配置 |
| `XpCall($callback, ...$args)` | 执行回调并捕获异常 |
| `BusinessThrowOn(...)` | 业务异常断言 |
| `Cache($object = null)` | 获取缓存 |
| `PathOfProject()` | 项目根目录 |
| `PathOfRuntime()` | 运行时目录 |
| `FireEvent($event, ...$args)` | 触发事件 |
| `OnEvent($event, $callback)` | 注册事件 |
| `AdminService()` | 管理员服务 |
| `UserService()` | 用户服务 |
| `getRouteCallingClass()` | 当前路由调用类 |
| `getRouteCallingMethod()` | 当前路由调用方法 |
| `PathInfo()` | 当前 PATH_INFO |
| `Url($url = null)` | URL 生成 |
| `Domain($use_scheme = false)` | 域名 |
| `Res($url = null)` | 资源 URL |
| `Parameter($key = null, $default = null)` | 路由参数 |
| `Render($view, $data = null)` | 渲染视图 |
| `Show($data = [], $view = '')` | 显示视图 |
| `IsAjax()` | 是否 Ajax |
| `Show302($url)` | 302 跳转 |
| `Show404()` | 404 页面 |
| `ShowJson($ret, $flags = 0)` | JSON 输出 |
| `GET/POST/REQUEST/COOKIE/SERVER($key, $default)` | 获取超全局变量 |
| `Pager(...)` / `PageNo(...)` / `PageWindow(...)` / `PageHtml(...)` | 分页相关 |
| `CallException($ex)` | 调用异常管理器 |
| `SESSION/FILES/SessionSet/SessionGet/SessionUnset/CookieSet/CookieGet(...)` | 会话与 Cookie |
| `system_wrapper_replace/get_providers/header/setcookie/exit/session_*` | 系统包装函数 |
| `Redis($tag = 0)` | Redis 对象 |
| `getRouteMaps/assignRoute/assignImportantRoute/assignRewrite/getRewrites` | 路由映射与重写 |
| `getCliParameters()` | 获取 CLI 参数 |
| `recursiveApps/getAllAppClass/getAppClassByComponent/regExtCommandClass` | 核心辅助方法 |

## 相关链接

- [DuckPhp\Helper\AppHelperTrait](Helper-AppHelperTrait.md)
- [DuckPhp\Helper\BusinessHelperTrait](Helper-BusinessHelperTrait.md)
- [DuckPhp\Helper\ControllerHelperTrait](Helper-ControllerHelperTrait.md)
- [DuckPhp\Helper\ModelHelperTrait](Helper-ModelHelperTrait.md)
