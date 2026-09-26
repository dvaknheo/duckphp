# DuckPhp\Core\Functions

A reference for the global functions.

## Introduction

`src/Core/Functions.php` defines a set of global functions whose names start with a double underscore `__`. These functions are shortcut mappings onto `DuckPhp\Core\CoreHelper`, so that view templates and controllers can call them directly.

> Note: this file is pulled in by the framework entry points through `require_once __DIR__ . '/Functions.php'` (see the top of the `App`/`DuckPhp` files), so business code never has to require it manually. Because every function is wrapped in `if ( ! function_exists(...) )`, requiring it twice is safe.

## The complete set of source functions and their signatures

Every function on this page comes from `src/Core/Functions.php`; the complete set with signatures (and mapping targets) is:

| Function | Signature (source) | Maps to |
|---|---|---|
| `__h` | `($str)` | `CoreHelper::H` |
| `__l` | `($str, $args = [], $fallback = null)` | `CoreHelper::L` |
| `__langtext` | `($desc, $args = [])` | `CoreHelper::LangText` |
| `__hl` | `($str, $args = [])` | `CoreHelper::Hl` |
| `__json` | `($data, int $options = 0)` | `CoreHelper::Json` |
| `__url` | `($url)` | `CoreHelper::Url` |
| `__domain` | `($use_scheme = false)` | `CoreHelper::Domain` |
| `__res` | `($url)` | `CoreHelper::Res` |
| `__display` | `(...$args)` | `CoreHelper::Display` |
| `__var_dump` | `(...$args)` | `CoreHelper::var_dump` |
| `__var_log` | `($var)` | `CoreHelper::VarLog` |
| `__trace_dump` | `()` | `CoreHelper::TraceDump` |
| `__debug_log` | `($str, $args = [])` | `CoreHelper::DebugLog` (expands `...$args` when forwarding) |
| `__logger` | `()` | `CoreHelper::Logger` |
| `__is_debug` | `()` | `CoreHelper::IsDebug` |
| `__is_real_debug` | `()` | `CoreHelper::IsHiddenDebug` |
| `__platform` | `()` | `CoreHelper::Platform` |

(The walk-through by group follows below.)

## The functions by group

### Output and escaping

#### `__h($str)`

HTML entity escaping. Maps to `CoreHelper::H()`.

```php
$name = '<script>alert(1)</script>';
echo __h($name);  // outputs &lt;script&gt;alert(1)&lt;/script&gt;
```

#### `__l($str, $args = [], $fallback = null)`

Multi-language translation. Maps to `CoreHelper::L()`. The third argument `$fallback` is the text used when no translation is found.

```php
echo __l('hello');                              // hello
echo __l('welcome, {name}', ['name' => 'Duck']); // …
echo __l('missing_key', ['a'=>1], 'fallback text');
```

#### `__langtext($desc, $args = [])`

Translates the `[[lang_key|fallback]]` fragments inside a piece of text. Maps to `CoreHelper::LangText()`. Good for longer messages that contain placeholder keys.

```php
echo __langtext('Notice: [[notice.success|done]] #{id}', ['id' => 7]);
```

#### `__hl($str, $args = [])`

Translates first, then escapes for HTML. Maps to `CoreHelper::Hl()`.

```php
echo __hl('welcome, {name}', ['name' => '<b>Duck</b>']);
```

#### `__json($data, int $options = 0)`

JSON encoding. Maps to `CoreHelper::Json()`. `JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK` are enabled by default, and in debug mode the output is pretty-printed.

```php
echo __json(['code' => 0, 'data' => $user]);
```

### URLs and domains

#### `__url($url)`

Generates a relative URL. Maps to `CoreHelper::Url()`.

```php
$url = __url('user/profile');  // /user/profile
```

#### `__res($url)`

Generates an asset URL. Maps to `CoreHelper::Res()`.

```php
$url = __res('css/style.css');
```

#### `__domain($use_scheme = false)`

Gets the current domain. Maps to `CoreHelper::Domain()`.

```php
$domain = __domain();       // //example.com
$domain = __domain(true);   // http://example.com
```

### View rendering

#### `__display(...$args)`

Renders a view fragment. Maps to `CoreHelper::Display()`.

```php
__display('partials/header', ['title' => 'Home']);
```

### Debugging

The functions below only take effect in debug mode (`is_debug` is `true`).

#### `__var_dump(...$args)`

Outputs variable information. Maps to `CoreHelper::var_dump()`.

```php
__var_dump($user, $posts);
```

#### `__trace_dump()`

Outputs the current call stack. Maps to `CoreHelper::TraceDump()`.

```php
__trace_dump();
```

#### `__debug_log($str, $args = [])`

Writes a debug log entry. Maps to `CoreHelper::DebugLog()`.

```php
__debug_log('query result: {result}', ['result' => $result]);
```

#### `__var_log($var)`

Records a variable as a log entry. Maps to `CoreHelper::VarLog()`.

```php
__var_log($complexData);
```

#### `__is_debug()`

Tells whether the current run is in debug mode. Maps to `CoreHelper::IsDebug()`.

```php
if (__is_debug()) {
    __var_dump($data);
}
```

#### `__is_real_debug()`

Tells whether this is real debug mode. Maps to `CoreHelper::IsHiddenDebug()`. It usually agrees with `__is_debug()`, and differs only under special environment configurations.

```php
if (__is_real_debug()) {
    // use with extreme caution
}
```

### Platform and logging

#### `__platform()`

Gets the current platform marker. Maps to `CoreHelper::Platform()`. It usually reads the `duckphp_platform` setting, for telling which machine this deployment is on.

```php
$platform = __platform();  // 'prod-server-01'
```

#### `__logger()`

Gets the logging object. Maps to `CoreHelper::Logger()` and returns a `Logger` instance.

```php
__logger()->info('user login: {id}', ['id' => $userId]);
```

> For the complete signatures of every function see the "The complete set of source functions and their signatures" table above.

## Caveats

1. The global functions start with a double underscore to avoid clashing with other functions in your project.
2. Each one forwards the call to the matching static method of `DuckPhp\Core\CoreHelper` (the mapping is the "The complete set of source functions and their signatures" table above).
3. The debug functions (`__var_dump`, `__trace_dump`, `__debug_log`, `__var_log`) only take effect in debug mode, so that information is not leaked into production.
4. These functions are pulled in automatically by the framework entry points (App / DuckPhp) with `require_once src/Core/Functions.php`, so business code needs no manual require; the `if ( ! function_exists())` wrapper makes requiring them twice safe.


## Related links

- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md)
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)
