# DuckPhp\Core\CoreHelper

核心辅助组件。

## 简介

`CoreHelper` 提供了一组全局可用的辅助方法，对应框架中的全局函数（`src/Core/Functions.php`）。它封装了 HTML 转义、多语言、URL 生成、视图渲染、调试输出、异常抛出、相位调用等常用操作。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

`CoreHelper` 本身没有独立的选项，但会读取当前应用 `App` 的选项，例如 `html_handler`、`exception_for_business`、`exception_for_controller` 等。

## 使用方式

### 全局函数

```php
__h('<script>');              // HTML 转义
__l('hello');                  // 多语言翻译
__hl('hello');                 // 翻译 + HTML 转义
__json(['a' => 1]);            // JSON 编码
__url('user/profile');         // 生成 URL
__domain();                    // 当前域名
__res('css/style.css');        // 生成资源 URL

__display('view_name', $data); // 渲染视图片段
__var_dump($var);              // 调试输出（仅调试模式）
__var_log($var);               // 调试日志（仅调试模式）
__trace_dump();                // 输出调用栈（仅调试模式）
__debug_log('message');        // 调试日志（仅调试模式）
__logger();                    // 获取 Logger 实例
__is_debug();                  // 是否调试模式
__platform();                  // 平台标识
```

### 通过 CoreHelper 组件

```php
use DuckPhp\Core\CoreHelper;

$html = CoreHelper::H($str);
$text = CoreHelper::L('hello', ['name' => 'Duck']);
$url = CoreHelper::Url('user/profile');
CoreHelper::ShowJson(['code' => 0, 'data' => $data]);
CoreHelper::Show302('/home');
```

### 条件抛出异常

```php
use DuckPhp\Core\CoreHelper;

CoreHelper::BusinessThrowOn($user === null, '用户不存在', 404);
CoreHelper::ControllerThrowOn(!Helper::UserId(), '未登录', 401);
```

### 跨相位调用

```php
use DuckPhp\Core\CoreHelper;

$ret = CoreHelper::PhaseCall(\OtherApp\System\OtherApp::class, function () {
    return OtherService::_()->doSomething();
});
```

## 全局函数列表

| 函数 | 对应方法 | 说明 |
|---|---|---|
| `__h($str)` | `H()` | HTML 转义 |
| `__l($str, $args = [])` | `L()` | 多语言翻译 |
| `__hl($str, $args = [])` | `Hl()` | 翻译后 HTML 转义 |
| `__json($data, $options = 0)` | `Json()` | JSON 编码 |
| `__url($url)` | `Url()` | 生成 URL |
| `__domain($use_scheme = false)` | `Domain()` | 获取当前域名 |
| `__res($url)` | `Res()` | 生成资源 URL |
| `__display(...)` | `Display()` | 渲染视图片段 |
| `__var_dump(...)` | `var_dump()` | 调试输出 |
| `__var_log($var)` | `VarLog()` | 调试日志 |
| `__trace_dump()` | `TraceDump()` | 输出调用栈 |
| `__debug_log($str, $args = [])` | `DebugLog()` | 调试日志 |
| `__logger()` | `Logger()` | 获取 Logger 实例 |
| `__is_debug()` | `IsDebug()` | 是否调试模式 |
| `__is_real_debug()` | `IsRealDebug()` | 是否真实调试模式 |
| `__platform()` | `Platform()` | 平台标识 |

## 方法列表

### 公共方法

    public static function H($str)
HTML 转义。支持字符串和数组递归转义

    public static function L($str, $args = [])
多语言翻译，委托给 `App::lang()`

    public static function Hl($str, $args = [])
先翻译再 HTML 转义

    public static function Json($data, $flags = 0)
JSON 编码，自动开启 `JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK`，调试模式下美化

    public static function Url($url = null)
生成 URL

    public static function Domain($use_scheme = false)
获取当前域名

    public static function Res($url = null)
生成资源 URL

    public static function Display($view, $data = null)
渲染视图片段

    public static function var_dump(...$args)
调试模式下输出 `var_dump`

    public static function VarLog($var)
调试模式下记录变量日志

    public static function TraceDump()
调试模式下输出调用栈

    public static function DebugLog($message, array $context = [])
调试模式下记录日志

    public static function Logger($object = null)
获取或设置 Logger 实例

    public static function IsDebug()
是否调试模式

    public static function IsRealDebug()
是否真实调试模式

    public static function Platform()
获取平台标识

    public static function IsAjax()
判断是否为 AJAX 请求

    public static function ShowJson($ret, $flags = 0)
输出 JSON 响应并设置响应头

    public static function Show302($url)
302 跳转，不跳转到外部域名

    public static function Show404()
触发 404 处理

    public static function XpCall($callback, ...$args)
捕获异常的回调执行，返回结果或异常对象

    public static function PhaseCall($phase, $callback, ...$args)
在指定相位下执行回调，完成后恢复原始相位

    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
条件为真时抛出业务异常

    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
条件为真时抛出控制器异常

    public static function PathOfProject()
获取项目根路径

    public static function PathOfRuntime()
获取运行时路径

### 实例方法

    public function _H(&$str)
HTML 转义内部实现

    public function _L($str, $args = [])
翻译内部实现

    public function _Hl($str, $args)
翻译后转义内部实现

    public function _Json($data, $flags = 0)
JSON 编码内部实现

    public function _VarLog($var)
调试日志内部实现

    public function _var_dump(...$args)
调试输出内部实现

    public function _TraceDump()
调用栈输出内部实现

    public function _DebugLog($message, array $context = array())
调试日志内部实现

    public function _IsDebug()
调试模式判断内部实现

    public function _IsRealDebug()
真实调试模式判断内部实现

    public function _Platform()
平台标识内部实现

    public function _IsAjax()
AJAX 判断内部实现

    public function _ShowJson($ret, $flags = 0)
JSON 响应内部实现

    public function _Show302($url)
302 跳转内部实现

    public function _XpCall($callback, ...$args)
捕获异常回调执行内部实现

    public function _PhaseCall($phase, $callback, ...$args)
跨相位调用内部实现

    public function _BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
业务异常抛出内部实现

    public function _ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
控制器异常抛出内部实现

    public function _PathOfProject()
项目路径内部实现

    public function _PathOfRuntime()
运行时路径内部实现

    public function recursiveApps(&$arg, $callback, ?string $app_class = null, $auto_switch_phase = true)
递归遍历所有子应用

    public function getAllAppClass()
获取所有应用类

    public function getAppClassByComponent(string $class)
根据组件类名查找所属应用类

    public function regExtCommandClass(string $class)
注册扩展命令类

## 相关链接

- [DuckPhp\Core\Functions](Core-Functions.md)
- [DuckPhp\Core\App](Core-App.md)
- [DuckPhp\Core\Logger](Core-Logger.md)
- [DuckPhp\Core\Route](Core-Route.md)
- [DuckPhp\Core\View](Core-View.md)
