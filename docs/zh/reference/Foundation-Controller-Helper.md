# DuckPhp\Foundation\Controller\Helper

控制器层 Helper 类。

## 简介

`DuckPhp\Foundation\Controller\Helper` 聚合了 `DuckPhp\Helper\ControllerHelperTrait`，为控制器层提供统一的静态方法入口，用于访问路由、视图、请求参数、分页、Cookie、Session、事件以及全局用户与管理员信息等。

## 选项

无。

## 使用方式

### 静态调用

```php
use DuckPhp\Foundation\Controller\Helper;

// 获取 GET/POST 参数
$id = Helper::GET('id', 0);
$name = Helper::POST('name', '');

// 渲染视图
Helper::Render('index', ['name' => $name]);

// 跳转
Helper::Show302('/login');

// JSON 输出
Helper::ShowJson(['code' => 0, 'data' => []]);

// 获取分页 HTML
$pager = Helper::PageHtml($total);
```

### 在 Controller 类中使用

控制器类通常使用 `DuckPhp\Foundation\SimpleControllerTrait`，也可以直接通过 `Helper` 类访问这些能力。

## 注意事项

1. 该类没有任何自有方法，所有方法均来自 `DuckPhp\Helper\ControllerHelperTrait`。
2. 部分方法依赖 `DuckPhp\Core\Route`、`DuckPhp\Core\View`、`DuckPhp\Core\SuperGlobal` 等组件，需确保应用已初始化。

## 方法列表

### 公共方法

| 方法 | 说明 |
|---|---|
| `Setting($key = null, $default = null)` | 读取应用设置 |
| `XpCall($callback, ...$args)` | 执行回调并捕获异常 |
| `Config($file_basename, $key = null, $default = null)` | 读取配置文件 |
| `getRouteCallingClass()` | 获取当前路由调用的类 |
| `getRouteCallingMethod()` | 获取当前路由调用的方法 |
| `PathInfo()` | 获取当前 PATH_INFO |
| `Url($url = null)` | 生成 URL |
| `Domain($use_scheme = false)` | 获取当前域名 |
| `Res($url = null)` | 生成资源 URL |
| `Parameter($key = null, $default = null)` | 获取路由参数 |
| `Render($view, $data = null)` | 渲染视图 |
| `Show($data = [], $view = '')` | 显示视图 |
| `setViewHeadFoot($head_file = null, $foot_file = null)` | 设置视图头尾文件 |
| `assignViewData($key, $value = null)` | 赋值视图数据 |
| `IsAjax()` | 判断是否为 Ajax 请求 |
| `Show302($url)` | 302 跳转 |
| `Show404()` | 显示 404 页面 |
| `ShowJson($ret, $flags = 0)` | 输出 JSON 响应 |
| `header($output, bool $replace = true, int $http_response_code = 0)` | 发送 HTTP 头 |
| `setcookie(...)` | 设置 Cookie |
| `exit($code = 0)` | 终止程序 |
| `assignExceptionHandler($classes, $callback = null)` | 分配异常处理器 |
| `setMultiExceptionHandler(array $classes, $callback)` | 设置批量异常处理器 |
| `setDefaultExceptionHandler($callback)` | 设置默认异常处理器 |
| `ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)` | 控制器异常断言 |
| `GET($key = null, $default = null)` | 获取 GET 参数 |
| `POST($key = null, $default = null)` | 获取 POST 参数 |
| `REQUEST($key = null, $default = null)` | 获取 REQUEST 参数 |
| `COOKIE($key = null, $default = null)` | 获取 COOKIE |
| `SERVER($key = null, $default = null)` | 获取 SERVER 参数 |
| `Pager($new = null)` | 获取分页对象 |
| `PageNo($new_value = null)` | 获取/设置页码 |
| `PageWindow($new_value = null)` | 获取/设置分页窗口 |
| `PageHtml($total, $options = [])` | 生成分页 HTML |
| `FireEvent($event, ...$args)` | 触发事件 |
| `OnEvent($event, $callback)` | 注册事件监听 |
| `Admin()` | 获取管理员对象 |
| `AdminId($check_login = true)` | 获取管理员 ID |
| `AdminName($check_login = true)` | 获取管理员名称 |
| `AdminService()` | 获取管理员服务 |
| `User()` | 获取用户对象 |
| `UserId($check_login = true)` | 获取用户 ID |
| `UserName($check_login = true)` | 获取用户名称 |
| `UserService()` | 获取用户服务 |

## 相关链接

- [DuckPhp\Helper\ControllerHelperTrait](Helper-ControllerHelperTrait.md)
