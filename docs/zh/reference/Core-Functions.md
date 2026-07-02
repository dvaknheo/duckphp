# DuckPhp\Core\Functions

全局函数参考。

## 简介

`src/Core/Functions.php` 定义了一组以双下划线 `__` 开头的全局函数。这些函数是 `DuckPhp\Core\CoreHelper` 的快捷映射，便于在视图模板和控制器中直接调用。

> 注意：这些全局函数需要 `DuckPhp\Core\KernelTrait` 加载后才可以使用，框架默认会自动引入。

## 函数分组

### 输出与转义

#### `__h($str)`

HTML 实体转义。对应 `CoreHelper::H()`。

```php
$name = '<script>alert(1)</script>';
echo __h($name);  // 输出 &lt;script&gt;alert(1)&lt;/script&gt;
```

#### `__l($str, $args = [])`

多语言翻译。对应 `CoreHelper::L()`。

```php
echo __l('hello');                       // 你好
echo __l('welcome, {name}', ['name' => 'Duck']);  // 你好, Duck
```

#### `__hl($str, $args = [])`

先翻译再进行 HTML 转义。对应 `CoreHelper::Hl()`。

```php
echo __hl('welcome, {name}', ['name' => '<b>Duck</b>']);
```

#### `__json($data, int $options = 0)`

JSON 编码。对应 `CoreHelper::Json()`。默认启用 `JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK`，调试模式下会美化输出。

```php
echo __json(['code' => 0, 'data' => $user]);
```

### URL 与域名

#### `__url($url)`

生成相对 URL。对应 `CoreHelper::Url()`。

```php
$url = __url('user/profile');  // /user/profile
```

#### `__res($url)`

生成资源 URL。对应 `CoreHelper::Res()`。

```php
$url = __res('css/style.css');
```

#### `__domain($use_scheme = false)`

获取当前域名。对应 `CoreHelper::Domain()`。

```php
$domain = __domain();       // //example.com
$domain = __domain(true);   // http://example.com
```

### 视图渲染

#### `__display(...$args)`

渲染一个视图片段。对应 `CoreHelper::Display()`。

```php
__display('partials/header', ['title' => '首页']);
```

### 调试

以下函数仅在调试模式下生效（`is_debug` 为 `true`）。

#### `__var_dump(...$args)`

输出变量信息。对应 `CoreHelper::var_dump()`。

```php
__var_dump($user, $posts);
```

#### `__trace_dump()`

输出当前调用栈。对应 `CoreHelper::TraceDump()`。

```php
__trace_dump();
```

#### `__debug_log($str, $args = [])`

记录调试日志。对应 `CoreHelper::DebugLog()`。

```php
__debug_log('query result: {result}', ['result' => $result]);
```

#### `__var_log($var)`

将变量以日志形式记录。对应 `CoreHelper::VarLog()`。

```php
__var_log($complexData);
```

#### `__is_debug()`

判断当前是否处于调试模式。对应 `CoreHelper::IsDebug()`。

```php
if (__is_debug()) {
    __var_dump($data);
}
```

#### `__is_real_debug()`

判断是否为真实调试模式。对应 `CoreHelper::IsRealDebug()`。通常与 `__is_debug()` 一致，仅在特殊环境配置下有区别。

```php
if (__is_real_debug()) {
    // 极其谨慎地使用
}
```

### 平台与日志

#### `__platform()`

获取当前平台标识。对应 `CoreHelper::Platform()`。通常读取 `duckphp_platform` 设置，用于判断当前部署机器。

```php
$platform = __platform();  // 'prod-server-01'
```

#### `__logger()`

获取日志对象。对应 `CoreHelper::Logger()`，返回 `Logger` 实例。

```php
__logger()->info('user login: {id}', ['id' => $userId]);
```

## 全部函数索引

```php
function __h($str);
function __l($str, $args = []);
function __hl($str, $args = []);
function __json($data, int $options = 0);
function __url($url);
function __domain($use_scheme = false);
function __res($url);
function __display(...$args);
function __var_dump(...$args);
function __var_log($var);
function __trace_dump();
function __debug_log($str, $args = []);
function __logger();
function __is_debug();
function __is_real_debug();
function __platform();
```

## 注意事项

1. 全局函数以双下划线开头，避免与项目其他函数冲突。
2. 所有函数都直接映射到 `CoreHelper` 的同名静态方法。
3. 调试函数（`__var_dump`、`__trace_dump`、`__debug_log`、`__var_log`）在调试模式下才会生效，避免泄露信息到生产环境。
4. 这些函数在 `KernelTrait` 加载 `Functions.php` 后可用，不需要手动引入。

## 相关链接

- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md)
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)
