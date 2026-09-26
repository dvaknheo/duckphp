# 4-3 Replacing framework behaviour

> What this solves: **when you want to change some framework behaviour, what do you touch** — swapping a class, a file, a singleton, a system call or a component: the boundaries and precedence of five levels, and how to investigate "I changed it and nothing happened".
> Prerequisites: [Chapter 3-5 Overriding and replacement](overriding.md) (file/class overrides), [Chapter 4-1 Containers and phases, inside out](container-phases.md). About 20 minutes.
> Examples: [`Ext\CallableView`](../reference/Ext-CallableView.md)/[`Ext\JsonView`](../reference/Ext-JsonView.md) (replacing the [View](../reference/Core-View.md) implementation), [`Ext\RedisCache`](../reference/Component-RedisCache.md) (replacing the [Cache](../reference/Component-Cache.md) implementation), `AppWithAllOptions.php` (the full option table).

## Minimal example

Three levels of replacement, one line each:

```php
// 1) replace one controller's implementation (class level)
$options = [
    'controller_class_map' => [
        'MyProj\Controller\UserController' => 'MyProj\Controller\UserControllerV2',
    ],
];
// it can be done at runtime too:
Helper::replaceController(\MyProj\Controller\UserController::class, \MyProj\Controller\UserControllerEx::class);
```

```php
// 2) replace a singleton (instance level) -- this is how View gets swapped
View::_(new \DuckPhp\Ext\JsonView());
// the option form is equivalent:
$options = ['ext' => [\DuckPhp\Ext\JsonView::class => true]];
```

```php
// 3) replace a system call (the lowest level) -- tests and long-running processes rely on it
Helper::system_wrapper_replace([
    'exit' => function ($code = 0) { throw new \RuntimeException('exit(' . $code . ')'); },
]);
```

## How it works

### 1. Five levels: what you want to change → what you touch

| Level        | Means                                                                                        | Scope       | Typical use                                     |
| --------- | ----------------------------------------------------------------------------------------- | ---------- | ---------------------------------------- |
| **Class level**    | the `override_class` option, `controller_class_map`, `Helper::replaceController()`                  | the whole app / a given controller | swapping in your own implementation (`override_class` turns the app into another class outright)  |
| **File level**   | the phase fallback of `getOverrideableFile()` (`view/`, `config/`, `res/`)                                   | layer by layer along the phases      | a child app overriding views/config/resources ([Chapter 3-5](overriding.md))  |
| **Singleton level**   | `Xxx::_($newObject)`                                                                      | the current phase       | swapping the View engine, the Cache implementation, test doubles                |
| **System-call level** | `Helper::system_wrapper_replace()` ([`SystemWrapper`](../reference/Core-SystemWrapper.md)) | the whole process        | intercepting `header`/`exit`/`session_start` for tests or daemons |
| **Component level**   | the component's own options: `database_class`, `view_*`, `*_skip_replace`                                        | that component        | swapping the [Db](../reference/Db-Db.md) implementation, switching auto-replacement off  |

### 2. Class level: `override_class` and `controller_class_map`

```php
// swap the whole app class at the entry point (the framework handles it at the very start of init())
App::RunQuickly(['override_class' => \MyProj\System\AppEx::class]);
```

What the framework does: it treats the value of `override_class` as the new class and runs `$class::_(new $class)->init($options, $context)` (the start of [`KernelTrait::init()`](../reference/Core-KernelTrait.md)), recording `override_from` as the original class — so an overriding class can learn "who I was swapped in for".

At controller level the more common tool is `controller_class_map` ([Chapter 2-3](routing.md)): it is a **class name → class name** map that takes effect after routing resolves a class name and before instantiation. `Helper::replaceController()` simply writes one entry into that map.

### 3. Singleton level: `Xxx::_($new)`

Every [`ComponentBase`](../reference/Core-ComponentBase.md) subclass goes through the container via [`SingletonExTrait::_($object = null)`](../reference/Core-SingletonExTrait.md): with no argument it fetches the instance, and **passing an object replaces the instance**. That is the unified stance extensions use to swap implementations:

```php
// View: Ext\CallableView / EmptyView / JsonView do this in init()
View::_(static::_());

// Cache: Ext\RedisCache does this in initContext()
Cache::_($this);

// your own component works the same way
Db::_($myDbImplementation);
```

Every extension ships a "do not auto-replace me" switch: `callable_view_skip_replace`, `json_view_skip_replace`, `empty_view_skip_replace`, `redis_cache_skip_replace` — set it when one app in a multi-app setup should keep the original implementation.

### 4. System-call level: `SystemWrapper`

`SystemWrapper` wraps these 10 functions as replaceable "providers" (`system_wrapper_get_providers()` shows the current table):

`header`, `setcookie`, `exit`, `set_exception_handler`, `register_shutdown_function`, `session_start`, `session_id`, `session_destroy`, `session_set_save_handler`, `mime_content_type`

```php
Helper::system_wrapper_replace([
    'header' => function ($output, $replace = true, $code = 0) { /* collect them for assertions */ },
    'mime_content_type' => fn ($f) => 'text/plain',
]);
```

There are two reasons for it: **testing** (output and redirects become assertable, [Chapter 2-17](testing.md)) and **porting** (replacing behaviour that differs per platform). Everything the framework outputs goes through it (`Helper::header()`, `Helper::setcookie()`, the `Html`/`Json` output), so replacing one place takes effect globally.

### 5. Component level: options are more framework-friendly than inheritance

| You want | Use the option | Not |
|---|---|---|
| Replace the Db implementation class | `database_class` | editing `Db.php` |
| Replace the whole session behaviour | `session_prefix` + a [`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) subclass | overriding [`SuperGlobal`](../reference/Core-SuperGlobal.md) |
| Switch an extension off | set `EXT_DISABLE` (`0`) in `ext` | removing code |
| Rebuild a component every request | set `EXT_RENEW` (`3`) in `ext` | calling `reInit()` by hand |

The five `EXT_*` values are in [Chapter 4-2](custom-component.md); [`DbManager`](../reference/Component-DbManager.md)'s `database_class` and [`App`](../reference/Core-App.md)'s `override_class` both belong to this level.

### 6. "Who wins", and investigating "my change had no effect"

Precedence, highest first (when one target is replaced in several places):

```
override_class (replace the whole app class)
  > an overriding method in a subclass
  > singleton replacement Xxx::_($new)
  > file-level override (the getOverrideableFile phase fallback)
  > the component option defaults
```

Three troubleshooting steps:

```php
\DuckPhp\Core\PhaseContainer::Dump();                 // which class's instance is actually in this phase right now
var_dump(App::_()->options['controller_class_map']);  // did the map really get written (the option allowlist! Chapter 1-5)
App::_()->getOverrideableFile('view', 'main.php');    // file level: which file is really hit
```

The two most common causes: **fetching the instance in a different phase** ([Chapter 4-1](container-phases.md)), and **the option did not survive** (the component filters with its own `$options` allowlist, [Chapter 4-2](custom-component.md)).

## Common patterns

**① Intercepting output and exit in tests**

```php
Helper::system_wrapper_replace([
    'header' => function (...$a) { TestRecorder::$headers[] = $a; },
    'exit'   => function ($code = 0) { throw new ExitCalled($code); },
]);
```

**② Swapping the view engine (function views / JSON / empty view)**

```php
$options = [
    'ext' => [\DuckPhp\Ext\CallableView::class => true],
    'callable_view_class' => \MyProj\View\Views::class,
];
```

**③ Letting one app keep the framework's original implementation** (in a multi-app setup)

```php
'ext' => [\DuckPhp\Ext\JsonView::class => true],
'json_view_skip_replace' => true,      // only this app does not replace it
```

**④ Replacing a controller without touching the original file**

```php
Helper::replaceController(\MyProj\Controller\AdminController::class, \MyProj\Admin\Controller\AdminController::class);
```

**⑤ A custom Db implementation class**

```php
$options = ['database_class' => \MyProj\Db\MyDb::class];
```

## Common errors

| Symptom                               | Cause                                  | Fix                                                                   |
| -------------------------------- | ----------------------------------- | -------------------------------------------------------------------- |
| A replacement has no effect at all                         | The call site used `new` instead of the container                    | Always use `Xxx::_()` ([Chapter 3-5](overriding.md))                               |
| Replaced inside a child app, the parent app is unchanged                    | Singletons are stored **per phase**                        | Do the replacement in the target phase, or switch to a shared component ([Chapter 3-4](component-sharing.md))                    |
| `system_wrapper_replace()` does not catch the output | The code calls native `header()`/`echo`+`exit` directly   | Go through `Helper::header()` / `Helper::exit()`                              |
| An option was written but has no effect                         | It is not in that component's `$options` allowlist              | See the allowlist mechanism in [Chapter 4-2](custom-component.md); or read it from `$hidden_options`        |
| `controller_class_map` does not work       | The key is a short name                              | The key must be the **fully qualified class name** (`MyProj\Controller\UserController`)                   |
| `override_class` makes the configuration look different       | The whole app class was replaced                            | Mind the new class's `$options` and `override_from`; options are still merged ([Chapter 1-5](configuration.md)) |
| An overridden view has no effect                          | The file name or phase name does not match                          | Use `getOverrideableFile()` to print the file actually hit ([Chapter 3-5](overriding.md))            |
| The `Cache` implementation was swapped but nothing is cached              | `RedisCache` also needs [`RedisManager`](../reference/Component-RedisManager.md) initialised | Declare both components together ([Chapter 2-14](cache.md))                                         |

## Next steps

- [Chapter 4-2 Developing components and extensions](custom-component.md): writing your own replaceable implementation.
- [Chapter 4-5 Long-running processes and the embedded HTTP server](http-server.md): what the system wrapper means in a long-running process.
- [Chapter 4-9 Performance tuning and troubleshooting](troubleshooting.md): walking "symptom → investigation path" when a replacement does not take effect.
- Reference manual: [DuckPhp\Core\SystemWrapper](../reference/Core-SystemWrapper.md), [DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md), [DuckPhp\Core\SingletonExTrait](../reference/Core-SingletonExTrait.md).
