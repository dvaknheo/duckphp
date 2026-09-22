# DuckPhp\Core\CoreHelper

供全局函数/视图模板直接使用的“Helper 集合”：常见 HTML/JSON/语言/URL 输出等一站式便捷包装。

## 简介

`CoreHelper` 是框架把“常用小功能”收纳为一组**静态便捷方法**的门面：`__h`、`__l`、`Clone…` 型全局函数（见 `Core-Functions`）多数最终走它；业务里也常直接 `CoreHelper::H()`、`::Json()` 等。

- 输出与转码：`H`（HTML 转义）、`Hl`（先翻译再转义）；
- 语言：`L`、`LangText`；
- JSON：`Json`（默认带 `JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK`，debug 再加美化）；
- URL/Domain/Res（转发给 `Route`）；
- 视图：`Display`（转发 `View`）；
- 调试：`var_dump/VarLog/TraceDump/DebugLog`（仅 debug 生效，转发 Logger）；
- 环境/Request：`IsDebug/IsHiddenDebug/Platform/IsAjax`；
- 业务友好：`ShowJson/Show302/XpCall/PhaseCall/BusinessThrowOn/ControllerThrowOn`。

大部分能设置为静态壳转发到**对应实例方法** `_Xxx`；改动可只用（覆盖）实例或其关的处理器（html_handler、lang_handler）。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class CoreHelper extends ComponentBase`
- 常被 `App` 用 `EXT_SKIP_INIT` 预建（见 Core-App 的 initComponentsOfRoot）。

## 选项

本类本身无自行 options（其行为依托 App 的这些设置）：

| 影响者 | 说明 |
|---|---|
| `html_handler` | 传入则 `_H` 使用它而不是默认 `htmlspecialchars`。 |
| `lang_handler` | 见 `App::lang`——`L/Hl/LangText` 经由它。 |
| `exception_for_business/controller/project` | `Business/ControllerThrowOn` 未指定时使用。 |
| `exception_map` | 抛异常前的类名映射（`原类 => 替换类`）；三个 `*ThrowOn` 均会先查它。 |
| debug(`App::_IsDebug()`) | 决定 Debug 输出族、IsDebug、Json 美化等。 |

## 使用方式

```php
use DuckPhp\Core\CoreHelper;

echo CoreHelper::H($username);            // HTML 转义
echo CoreHelper::L('hello');              // 翻译
echo CoreHelper::Json(['ok'=>1]);         // json 串（unicode/数值不转义+美化选择）
echo CoreHelper::Url('user/show');
CoreHelper::ShowJson(['ok'=>true]);       // 头 json + echo
CoreHelper::IsDebug();
```

对应的全局函数（Functions 层）见 [Core-Functions](Core-Functions.md)。

### Business/Controller 抛出

```php
CoreHelper::BusinessThrowOn($userId === null, 'no user');
CoreHelper::ControllerThrowOn($tokenNotOk, 'bad token', 400);
```

## 配置示例

```php
// 改变 HTML 处理或接管语言（app options）
$options['html_handler'] = function ($s) { return htmlspecialchars($s, ENT_QUOTES); };
$options['lang_handler'] = function ($s, $a) { return my_t($s, $a); };
```

## 注意事项

1. HTML 转义 `H` 为默认行为；有 html_handler 就用你给的。
2. Debug 工具（var_dump/VarLog/TraceDump/DebugLog/ShowJson 中 Json 美化）在非 debug 静默/不加美化，避免生产信息外泄（同时需要开发调试请打开 debug）。

## 方法列表

> 下述静态壳大多转手同名 `_Xxx` 实例层方法；业务用 Shell 即可。

### 公共静态方法

    public static function H($str)
返回 HTML 实体转义后的 $str（数组则递归 H）；有 html_handler 优先用 handler。

    public static function L($str, $args = [], $fallback = null)
翻译：委托 `App->_lang`（如果配置 lang_handler 则走 it）。

    public static function Hl($str, $args = [])
先 L 后 H 的组合转义壳。

    public static function LangText($desc, $args = [])
经 Lang 解析 `[[key|fallback]]` 段；委托 App::langText()。

    public static function Json($data, $flags = 0)
给 json 编码，附加 UNESCAPED_UNICODE|NUMERIC_CHECK；debug 加 JSON_PRETTY_PRINT。

    public static function Url($url = null)
返回`Route`生成的 站内 URL（委托 `Route::_()->_Url`）。

    public static function Res($url = null)
静态资源 URL（委托 `Route::_()->_Res`）。

    public static function Domain($use_scheme = false)
当前域名（委托 `Route::_()->_Domain`）。

    public static function Display($view, $data = null)
直接输出某视图（委托 `View::_()->_Display`）。

    public static function var_dump(...$args)
debug 时回显 `<pre>var_dump</pre>`。

    public static function VarLog($var)
debug 时把 `var_export` 记日志（Logger）。

    public static function TraceDump()
debug 时回显当前 exception 的 trace。

    public static function DebugLog($message, array $context = array())
debug 时把 message+context 记（Logger::debug）。

    public static function Logger($object = null)
返回（或用 $object 注入）日志器 — 直通 `Logger::_()`。

    public static function IsDebug()
是否 debug（App::_IsDebug）。

    public static function IsHiddenDebug()
真实 debug 判定。

    public static function Platform()
平台标识（App::_Platform）。

    public static function IsAjax()
检测 X-Requested-With: XMLHttpRequest。

    public static function ShowJson($ret, $flags = 0)
设 JSON 响应头后 echo Json($ret)；通常作为(面向 Ajax/API)输出。

    public static function Show302($url)
同站跳转：写 `Location`，跨域 url 直接忽略（PHP_URL_HOST）。

    public static function XpCall($callback, ...$args)
“限制异常”调用：抛 `Exception` 则返回 ex，否则返回结果。

    public static function PhaseCall($phase, $callback, ...$args)
在给定 Phase 下执行并对后返回，随后切回原 Phase。

    public static function ChildCall($phase, $callback, ...$args)
在指定“子应用 Phase”下执行回调，随后切回原 Phase。

    public static function ProjectThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
若 `$flag` 真则抛“项目异常”（类取 `exception_for_project`，并过 `exception_map` 映射）。

    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
若 $flag 真则丢 business 异常（或 exception_class）。

    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
同上但对 controller 层；取 exception_for_controller→project→Exception。

    public static function Show404()
转为“调 App::On404()”（根挂的 404 兜底）。

### 实例方法（_X 对应）

    public function _H(&$str)
HTML 转义/数组递归实现。

    public function _L($str, $args = [], $fallback = null)
翻译实际入口（App::lang）。

    public function _Hl($str, $args)
`_L(...)` 后 `_H(...)`。

    public function _LangText($desc, $args = [])
App::langText 转发。

    public function _Json($data, $flags = 0)
json_encode with flags 逻辑。

    public function _VarLog($var)
debug gate 下把 var_export($var,true) 交 Logger::debug。

    public function _var_dump(...$args)
debug gate 下回显 `<pre>var_dump</pre>`。

    public function _TraceDump()
debug gate 下重建空异常 trace 输出 `<pre>`。

    public function _DebugLog($message, array $context = array())
debug gate 下 logger debug（$context）。非 debug 返回 false。

    public function _IsDebug()
转 App::_IsDebug。

    public function _IsHiddenDebug()
转 App::_IsHiddenDebug。

    public function _Platform()
转 App::_Platform。

    public function _IsAjax()
取 `HTTP_X_REQUESTED_WITH`。

    public function _ShowJson($ret, $flags = 0)
header json + echo。

    public function _Show302($url)
仅同站跳转。

    public function _XpCall($callback, ...$args)
捕获 Exception 返回异常或结果实现。

    public function _PhaseCall($phase, $callback, ...$args)
phase 切换回调后可逆实现。

    public function _ChildCall($child_app, $callback, ...$args)
切到指定子应用（`App::toThisChild`）执行回调后切回原 Phase。

    public function _ProjectThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
project 异常路径（默认 `exception_for_project`，并过 `exception_map`）。

    public function _BusinessThrowOn(bool $flag, string $msg, int $code = 0, $exception_class = null)
impls;business 异常路径。

    public function _ControllerThrowOn(bool $flag, string $msg, int $code = 0, $exception_class = null)
controller 抛路径。

## 相关链接

- [DuckPhp\Core\Functions](Core-Functions.md) — 由 __xxx() 全局函数对应
- [DuckPhp\Core\App](Core-App.md) 提供 html/lang_handler/debug 环境
- [DuckPhp\Core\Logger](Core-Logger.md)、[DuckPhp\Core\View](Core-View.md)、[DuckPhp\Core\Route](Core-Route.md) 被转发目标
- guide：[helper](../guide/helper.md)
