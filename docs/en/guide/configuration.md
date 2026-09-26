# 1-5 Options and Settings

> What this solves: separating "application options" from "application settings" — what goes in `App.php`, what goes in `config/`, and who is affected when you change a key.
> Prerequisites: [Chapter 1-2](install.md). About 15 minutes.

## The one-sentence difference

|     | Application options                                                | Application settings                                                                                       |
| --- | ----------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| Where written | The application class's `public $options` (can be further overridden by arguments to `init()`/`RunQuickly()`)    | `config/DuckPhpSettings.config.php`, `.env`, or an array given in the `setting` option                                      |
| What it holds | **Behavior switches** of the framework and components (paths, error pages, routing rules, extension list…)                           | **Sensitive / environment-specific** key-values (database, Redis passwords…)                                                                       |
| Who reads it | Each component picks from its own whitelist in its own `init()`                                  | The root app's `_Setting()`; components read it **explicitly** (e.g. [`DbManager`](../reference/Component-DbManager.md) reads `database_list`) |
| Scope | One set per app (a child app can inject different values via the `app` option)                               | Loaded **only by the root app**, and what is read is the root app's (`static::Root()->setting`)                                                     |
| How to inspect | [`App::_()->options`](../reference/Core-App.md) (can dump the whole thing) | `App::_Setting()` (no argument returns the whole array), `Setting('key', $default)`                                              |

## Application options

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',            // project root: view/ config/ runtime/ are all relative to it
        'namespace' => 'MyProj',                  // common prefix of Controller/Business/Model below
        'error_404' => '_sys/error_404',          // view name (relative to view/)
        'error_500' => '_sys/error_500',
        'is_debug' => true,                       // switch to false for production
        'controller_method_prefix' => '',         // empty by default: the method name is the URL segment
    ];
}
```

**Merge order** (later overrides earlier, happens in `App::__construct()`):

```
KernelTrait::$kernel_options → App::$core_options → 入口类的 $common_options → 你子类的 $options
                                                                                     ↓
                                               init($options) / RunQuickly($options) 传入的再覆盖一层
```

So "temporarily change one option" doesn't require editing the class file:

```php
\MyProj\System\App::RunQuickly(['is_debug' => true, 'path_info_compact_enable' => true]);
```

> ⚠️ **App-level options are not whitelist-filtered** — a mistyped key name gives no warning; but **component-level options are whitelisted** (`array_intersect_key` in [`ComponentBase::init()`](../reference/Core-ComponentBase.md)), so handing a component a key it never declared gets **silently dropped**. This is the most common cause of "I changed it and nothing happened".
>
> Example: [`GlobalUser`](../reference/GlobalUser-GlobalUser.md) declares `user_default_exception_class`; setting `admin_default_exception_class` has no effect (that is [`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md)'s key).

Three ways to inspect options:

```php
var_dump(App::_()->options);                       // all options currently in effect
var_dump(App::_()->options['error_404']);          // one key
// the demo app's /files page (demo/view/files.php) dumps the options / singleton container exactly like this
```

## Application settings

```php
<?php
// config/DuckPhpSettings.config.php — for sensitive and environment-specific things
return [
    'duckphp_is_debug' => true,        // equivalent to the is_debug option (see below)
    //'duckphp_platform' => 'web-01',  // identifies the current machine in multi-machine deployments
    //'duckphp_is_maintain' => false,  // maintenance mode (serves the error_maintain page)

    'database_list' => [
        ['dsn' => 'mysql:host=127.0.0.1;dbname=demo;charset=utf8mb4;', 'username' => 'root', 'password' => 'secret'],
    ],
    'redis_list' => [
        ['host' => '127.0.0.1', 'port' => 6379, 'auth' => 'secret', 'select' => 0],
    ],
];
```

The options that control it:

| Option                           | Default                                    | Description                                    |
| ---------------------------- | ------------------------------------- | ------------------------------------- |
| `setting_file`               | `'config/DuckPhpSettings.config.php'` | Settings file path (relative to `path`)                     |
| `setting_file_enable`        | `true`                                | Turn off to skip loading the settings file                            |
| `setting_file_ignore_exists` | `true`                                | No error when the file doesn't exist                             |
| `use_env_file`               | `false`                               | When true, loads `.env` from the project root (in `parse_ini_file` format) |
| `setting`                    | `[]`                                  | Give a settings array directly in the options (lowest priority)                  |
|                              |                                       |                                       |

Load timing: the **root app**'s `onPrepare()` phase (`loadSetting()`), in the order `options['setting']` → `.env` (if enabled) → the settings file.

Three keys starting with `duckphp_` are recognized by the framework itself:

| Setting key                   | Read by               | Effect                                   |
| --------------------- | ----------------- | ------------------------------------ |
| `duckphp_is_debug`    | `App::IsDebug()`  | **OR**ed with the root app's `is_debug` option: either being true means debug mode |
| `duckphp_is_maintain` | `prepareServe()`  | When true, enters the maintenance page (the `error_maintain` option)        |
| `duckphp_platform`    | `App::Platform()` | Multi-machine deployment identifier                               |

**Key insight**: settings do **not** automatically become "every component's options". Only places that **explicitly read settings** are affected by them; the ones with built-in support so far:

- `DbManager`: `database_list_reload_by_setting` (default true) → the `database_list` in settings takes effect;
- [`RedisManager`](../reference/Component-RedisManager.md): same idea for `redis_list`;
- `App` itself: the three `duckphp_*` above;
- Your own components/business code: read actively with `Setting('key', $default)`.

Any other key placed in the settings file has **no** effect (for example, writing `error_404` into the settings file is useless — that is an option).

## Per-environment setup (local / production)

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        'is_debug' => false,          // safe by default; override to true locally via env var / entry file
    ];
}
```

```php
// local development entry (or .env / a per-machine settings file)
\MyProj\System\App::RunQuickly(['is_debug' => true]);
```

Production checklist (Chapter 1-7 has the full version): `is_debug=false`, error pages in place, sensitive information only in the settings file / `.env`, `runtime/` writable.

## Common errors

| Symptom         | Cause                                                               | Fix                                                                |
| ---------- | ---------------------------------------------------------------- | ----------------------------------------------------------------- |
| Changed an option, "no effect"  | The key doesn't belong to the component you changed it for (component options are whitelisted)                                           | Check [the reference manual](../reference/index.md) for which component owns the key; use `App::_()->options` to see the value actually in effect |
| A key in the settings file does nothing | Settings ≠ options; only code that explicitly reads settings honors it                                               | Behavior switches go in options; only things like `database_list`/`redis_list`/`duckphp_*` go in settings        |
| A child app can't read settings   | Settings are loaded only by the **root app**, and what is read is the root's copy                                           | Use `App::_Setting()`; to give a child app different config, use the child app's options (injected via `app`)                  |
| `.env` didn't take effect | Forgot `'use_env_file' => true`, or the format isn't `parse_ini_file`'s `key=value` | Enable the option; check the file is in the project root                                                     |
| A missing settings file raises an error | `setting_file_ignore_exists` was turned off                                 | Keep the default `true`, or use an empty array as a placeholder                                               |

## Next steps

- [Chapter 1-6 Debugging, Logging, and a First Taste of the CLI](debugging.md): what you can see once `is_debug` is on.
- [Chapter 1-7 Minimal Go-Live Checklist](deployment.md): how to configure production.
- The reference manual: [DuckPhp\Core\App](../reference/Core-App.md) (all core options), [options cheat sheet](../reference/options.md)
