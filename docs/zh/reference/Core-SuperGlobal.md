# DuckPhp\Core\SuperGlobal

超全局变量（$_GET/POST/REQUEST/SERVER）的隔离存取层：可把超全局“搬进容器”、通过 read shell 取用、及提供 SESSION/COOKIE 便捷（写 cookie 走 SystemWrapper）。

## 简介

`SuperGlobal` 提供在不污染全局符号的前提下操作 HTTP 超全局的手段：它把 `$_GET/$_POST/$_REQUEST/$_SERVER/$_COOKIE/$_SESSION/$_FILES` 复制成自己的公开属性副本（`_GET…`），并可通过静态壳读取、或根据上下文读写真实超全局；同时它负责建立全局常量 `__SUPERGLOBAL_CONTEXT`（用来在全局函数/组件内向“上下文对象”取超全局，从而可测试隔离多请求环境）。

- 上下文法：`static::DefineSuperGlobalContext()` 定义 `__SUPERGLOBAL_CONTEXT` = `SuperGlobal::_`；此后引用 `( __SUPERGLOBAL_CONTEXT )()->_GET` 从对象副本取，而不是直接用 `$_GET`。
- 壳读取：`_GET('key',default)` 等走 `getSuperGlobalData()`：上下文启用取上下文对象数据，否则回退读 `$GLOBALS[$superglobal_key]`。
- 会话/文件便捷 + Cookie：`_Session… / _Cookie…`

源码里有一段被 `/*…*/` 注释的“静态 GET/POST/…SERVE”草案并未启用，不在此列（防止误用）。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class SuperGlobal extends ComponentBase`
- 公共属性：`$_GET,$_POST,$_REQUEST,$_SERVER,$_COOKIE,$_SESSION,$_FILES`

## 选项

`SuperGlobal` 只有：

| 选项 | 默认 | 说明 |
|---|---|---|
| `superglobal_auto_define` | `false` | init 时若为真：自动 `DefineSuperGlobalContext()` 并 `_LoadSuperGlobalAll()`（把当前超全局快照入属性）。 |

## 使用方式

```php
use DuckPhp\Core\SuperGlobal;

SuperGlobal::DefineSuperGlobalContext();      // 建立 __SUPERGLOBAL_CONTEXT（一次）
SuperGlobal::LoadSuperGlobalAll();            // 本体超全局→属性

SuperGlobal::_()->_GET('id');               // 读 id（上下文/OO）
SuperGlobal::_()->_POST('name','');
SuperGlobal::_()->_SessionSet('uid', 5);
SuperGlobal::_()->_SessionGet('uid');
SuperGlobal::_()->_CookieSet('theme','dark', 3600);   // 底层走 SystemWrapper::setcookie
```

框架会视 `superglobal_auto_define` 自动预置两相；业务一般直接用 `CoreHelper` 的 `GET()`等壳而不是绕过它。同实现以上下文（常量）与 global 两者降级处理。

## 注意事项

- 一次性快照：`_LoadSuperGlobalAll` 只在 init 或主动调用时 copy；后续改真实 `$_GET` 不会自动同步（需要哪层决定：调 again 或按需）。
- `_SessionUnset/_CookieGet` 等对 context 与否分别处理；`_CookieSet` 生命期合并到 SystemWrapper。
- 相关“读取超全局”历史脚本可能有手工在文件用 `defined('__SUPERGLOBAL_CONTEXT')?…` 三元：这是被隔离场景的两用，读到本组件时属官方封装。
- 被注释的静态 GET/POST/… 未生效，别对外文档引用为 API。

## 方法列表

### 公共静态方法

    public static function DefineSuperGlobalContext()
未定义时定义常量 `__SUPERGLOBAL_CONTEXT`（值为 `SuperGlobal::_`），返回是否新定义。

    public static function LoadSuperGlobalAll()
静态壳：转发实例 `_LoadSuperGlobalAll`。

    public static function SaveSuperGlobalAll()
静态壳：转发实例 `_SaveSuperGlobalAll`。

    public static function LoadSuperGlobal($key)
静态壳：转发实例 `_LoadSuperGlobal`。

    public static function SaveSuperGlobal($key)
静态壳：转发实例 `_SaveSuperGlobal`。

    public static function GET($key = null, $default = null)
读 `$_GET`（静态壳 → `_GET`）。

    public static function POST($key = null, $default = null)
读 `$_POST`。

    public static function REQUEST($key = null, $default = null)
读 `$_REQUEST`。

    public static function COOKIE($key = null, $default = null)
读 `$_COOKIE`。

    public static function SERVER($key = null, $default = null)
读 `$_SERVER`。

    public static function SESSION($key = null, $default = null)
读 `$_SESSION`。

    public static function FILES($key = null, $default = null)
读 `$_FILES`。

    public static function SessionSet($key, $value)
写 SESSION（静态壳 → `_SessionSet`）。

    public static function SessionUnset($key)
删除 SESSION 键。

    public static function SessionGet($key, $default = null)
读 SESSION 键。

    public static function CookieSet($key, $value, $expire = 0)
发 Cookie（静态壳 → `_CookieSet`，经 SystemWrapper）。

    public static function CookieGet($key, $default = null)
读 Cookie 键。

### 公共实例方法

    public function _LoadSuperGlobalAll()
把全局超全局快照赋到 `_GET…_FILES` 字段。

    public function _SaveSuperGlobalAll()
把字段写回对应全局超全局。

    public function _LoadSuperGlobal($key)
单键：字段 ← `$GLOBALS[$key]`。

    public function _SaveSuperGlobal($key)
单键：`$GLOBALS[$key]` ← 字段。

    public function _GET($key = null, $default = null)
读 `_GET` 容器（上下文优先，否则 `$GLOBALS`），取 key 或整体。

    public function _POST($key = null, $default = null)
读 POST 容器。

    public function _REQUEST($key = null, $default = null)
读 REQUEST 容器。

    public function _COOKIE($key = null, $default = null)
读 COOKIE 容器。

    public function _SERVER($key = null, $default = null)
读 SERVER 容器。

    public function _SESSION($key = null, $default = null)
读 SESSION 容器。

    public function _FILES($key = null, $default = null)
读 FILES 容器。

    public function _SessionSet($key, $value)
写 SESSION（上下文或全局分支）。

    public function _SessionUnset($key)
删除 SESSION 键。

    public function _SessionGet($key, $default = null)
读 SESSION 键。

    public function _CookieGet($key, $default = null)
从 COOKIE 读取。

    public function _CookieSet($key, $value, $expire = 0)
发 Cookie（经 `SystemWrapper::setcookie`，expire 计时）。

### 受保护方法

    protected function initOptions(array $options): void
`superglobal_auto_define` 为真时 `DefineSuperGlobalContext()` 并 `LoadSuperGlobalAll()`。

    protected function getSuperGlobalData(string $superglobal_key, ?string $key, $default)
实际取数：上下文优先取上下文对象属性，否则 `$GLOBALS[$key?下标:全部]`。

## 相关链接

- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) — Cookie 发底层
- [DuckPhp\Core\App](Core-App.md) 部署/Debug/平台读多用此
- 相关常量 `__SUPERGLOBAL_CONTEXT` 参考；
- [Core-SingletonExTrait]，Super global for 子请求隔离/测试 demo/tests.
