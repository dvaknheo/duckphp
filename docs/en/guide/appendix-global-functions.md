# Appendix: Global Functions Reference

The following global functions are defined via `src/Core/Functions.php` when the framework starts, ready to use out of the box.

## HTML / Internationalization

| Function | Description |
|---|---|
| `__h($str)` | HTML escape (`htmlspecialchars`), automatically recursive for arrays |
| `__l($str, $args = [])` | Internationalization translation |
| `__hl($str, $args = [])` | Internationalization + HTML escape |

## URL / Path

| Function | Description |
|---|---|
| `__url($url)` | Generate in-app URL |
| `__res($url)` | Generate resource URL (supports CDN) |
| `__domain($use_scheme = false)` | Get current domain |

## Output / Data

| Function | Description |
|---|---|
| `__json($data, $options = 0)` | JSON encoding (automatically adds `JSON_UNESCAPED_UNICODE`) |
| `__display($view, $data = null)` | Render view fragment |

## Debug (only effective in debug mode)

| Function | Description |
|---|---|
| `__var_dump(...$args)` | Page output var_dump |
| `__var_log($var)` | Log variable to log |
| `__trace_dump()` | Print call stack |
| `__debug_log($msg, $args = [])` | Write debug log |
| `__is_debug()` | Check if current mode is debug mode |
| `__is_real_debug()` | Check if real debug mode |
| `__platform()` | Get platform identifier |
| `__logger()` | Get logger instance |

## Usage

```php
// In views
<a href="<?= __url('user/login') ?>">Login</a>
<h1><?= __h($title) ?></h1>
<p><?= __l('welcome_message') ?></p>

// In controllers
__var_dump($data);
__debug_log('User {id} logged in', ['id' => $userId]);
```

> These functions are all proxied to actual components through `DuckPhp\Core\CoreHelper`, so they are only available after the framework initialization is complete.
