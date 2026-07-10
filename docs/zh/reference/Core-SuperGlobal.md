# DuckPhp\Core\SuperGlobal

超级全局变量管理组件。

## 简介

`SuperGlobal` 封装了 PHP 的超级全局变量（`$_GET`、`$_POST`、`$_REQUEST`、`$_SERVER`、`$_COOKIE`、`$_SESSION`、`$_FILES`）。它允许在测试或特定场景下切换全局变量的上下文，并封装了安全读写这些全局变量的方法。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `superglobal_auto_define` | `false` | 初始化时是否自动定义超级全局上下文并加载所有全局变量。 |

## 使用方式

### 定义超级全局上下文

```php
use DuckPhp\Core\SuperGlobal;

SuperGlobal::DefineSuperGlobalContext();
```

### 加载与保存全局变量

```php
use DuckPhp\Core\SuperGlobal;

SuperGlobal::LoadSuperGlobalAll();  // 从 PHP 全局变量加载到组件
SuperGlobal::SaveSuperGlobalAll();  // 将组件中的值写回 PHP 全局变量
```

### 单个变量加载与保存

```php
use DuckPhp\Core\SuperGlobal;

SuperGlobal::LoadSuperGlobal('_SERVER');
SuperGlobal::SaveSuperGlobal('_SERVER');
```

### 安全读取全局变量

```php
use DuckPhp\Core\SuperGlobal;

$all = SuperGlobal::_()->_GET();              // 获取全部 $_GET
$id = SuperGlobal::_()->_GET('id', 0);        // 获取 id，默认 0
$name = SuperGlobal::_()->_POST('name', '');
$method = SuperGlobal::_()->_SERVER('REQUEST_METHOD', 'GET');
```

### 操作 Cookie 和 Session

```php
use DuckPhp\Core\SuperGlobal;

SuperGlobal::_()->_SessionSet('user_id', 42);
$userId = SuperGlobal::_()->_SessionGet('user_id');
SuperGlobal::_()->_SessionUnset('user_id');

SuperGlobal::_()->_CookieSet('theme', 'dark', 3600);
$theme = SuperGlobal::_()->_CookieGet('theme');
```

## 配置示例

### 自动加载全局变量

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'superglobal_auto_define' => true,
    ];
}
```

## 注意事项

1. 当定义了 `__SUPERGLOBAL_CONTEXT` 常量后，框架内部会优先从该上下文读取超级全局变量。
2. `_CookieSet()` 通过 `SystemWrapper` 调用 `setcookie`，便于测试和替换。
3. `_SessionSet()` 会同时更新上下文和 `$_SESSION`。
4. 初始化选项 `superglobal_auto_define` 为 `true` 时，会调用 `DefineSuperGlobalContext()` 并加载所有全局变量。

## 全部选项

```php
    'superglobal_auto_define' => false,
```

## 方法列表

### 公共方法

    public static function DefineSuperGlobalContext()
定义 `__SUPERGLOBAL_CONTEXT` 常量，指向当前类

    public static function LoadSuperGlobalAll()
加载所有超级全局变量到组件实例

    public static function SaveSuperGlobalAll()
将组件实例中的超级全局变量写回 PHP 全局变量

    public function _LoadSuperGlobalAll()
内部实现：复制全部超级全局变量到组件属性

    public function _SaveSuperGlobalAll()
内部实现：将组件属性写回 PHP 超级全局变量

    public static function LoadSuperGlobal($key)
加载单个全局变量到组件

    public static function SaveSuperGlobal($key)
保存单个组件属性到全局变量

    public function _LoadSuperGlobal($key)
内部实现：加载单个全局变量

    public function _SaveSuperGlobal($key)
内部实现：保存单个全局变量

    public function _GET($key = null, $default = null)
获取 `$_GET` 全部或单个值

    public function _POST($key = null, $default = null)
获取 `$_POST` 全部或单个值

    public function _REQUEST($key = null, $default = null)
获取 `$_REQUEST` 全部或单个值

    public function _COOKIE($key = null, $default = null)
获取 `$_COOKIE` 全部或单个值

    public function _SERVER($key = null, $default = null)
获取 `$_SERVER` 全部或单个值

    public function _SESSION($key = null, $default = null)
获取 `$_SESSION` 全部或单个值

    public function _FILES($key = null, $default = null)
获取 `$_FILES` 全部或单个值

    public function _SessionSet($key, $value)
设置 Session 值

    public function _SessionUnset($key)
删除 Session 值

    public function _CookieSet($key, $value, $expire = 0)
设置 Cookie，过期时间以秒为单位，大于 0 时会自动加上当前时间

    public function _SessionGet($key, $default = null)
获取 Session 值

    public function _CookieGet($key, $default = null)
获取 Cookie 值

### 受保护方法

    protected function initOptions(array $options): void
初始化选项，若开启 `superglobal_auto_define` 则定义上下文并加载全局变量

    protected function getSuperGlobalData(string $superglobal_key, ?string $key, $default)
从上下文或全局变量读取指定键的数据

## 相关链接

- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md)
- [DuckPhp\Core\Route](Core-Route.md)
