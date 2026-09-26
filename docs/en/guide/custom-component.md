# 4-2 Developing components and extensions

> What this solves: how to write a component/extension of your own (declaring options, initialising, hooking into routing) and assemble it into an app through the `ext` option; and how to replace a component the framework ships.
> Prerequisites: [Chapter 2-2 The request lifecycle](lifecycle.md), [Chapter 4-1 Containers and phases, inside out](container-phases.md). About 20 minutes.
> Every framework extension referenced here comes from `src/Ext/` and `src/Component/`; the assembly examples come from `tests/data_for_tests/ZThirdDemo` and `tests/data_for_tests/Ext/PermissionMenu`.

## Minimal example

A minimal extension (illustrative; the structure is copied from `src/Ext/RouteHookWebInstaller.php`):

```php
namespace MyProj\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;

class HelloBanner extends ComponentBase
{
    public $options = [
        'hello_banner_text' => 'Hello',
    ];

    protected function initContext(object $context): void
    {
        Route::_()->addRouteHook([static::class, 'Hook'], 'append-outter');
    }
    public static function Hook($path_info)
    {
        echo static::_()->options['hello_banner_text'];
        return false;   // return false so routing carries on
    }
}
```

The minimum contract a component satisfies is [`Core\ComponentInterface`](../reference/Core-ComponentInterface.md): the three methods `_()` / `init()` / `isInited()`. Note that the framework does **not** make `ComponentBase` actually `implements` it (`src/Core/ComponentBase.php` line 12 is the commented-out `// implements ComponentInterface`) — it is a duck-typed contract, so align with it when writing your own component without declaring it.

Mounting it into an app (the same shape as `tests/data_for_tests/ZThirdDemo/src/System/MainApp.php` lines 52-55):

```php
'ext' => [
    \DuckPhp\Component\GlobalEvent::class => true,   // switch on a framework extension
    \MyProj\Ext\HelloBanner::class => true,          // switch on your own extension
],
```

## How it works

### Components and extensions: one skeleton, two identities

Both [`extend DuckPhp\Core\ComponentBase`](../reference/Core-ComponentBase.md), and both are assembled by `::_(new Xxx())->init($options, $context)`. The only differences are **who initialises them and whether they are on by default**:

|      | Components `DuckPhp\Component\*`                                                                                                      | Extensions `DuckPhp\Ext\*`                                                                                                                     |
| ---- | ----------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| Examples   | `DbManager`, [`Configer`](../reference/Component-Configer.md), [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md) | `JsonView`, [`PermissionMenu`](../reference/Ext-PermissionMenu.md), [`RouteHookWebInstaller`](../reference/Ext-RouteHookWebInstaller.md) |
| How they load | the framework loads them from a built-in table in `initComponents()` (some are marked shared, see Chapter 4-1)                                                                          | loaded only when you declare them in `options['ext']`                                                                                                            |
| Default state | most are enabled by default                                                                                                                        | all are off by default                                                                                                                                 |

Note that an "extension" is just an ordinary class too: [`JsonView extends View`](../reference/Core-View.md), [`MyMiddlewareManager extends ComponentBase`](../reference/Ext-MyMiddlewareManager.md) — an extension may simply be a subclass of another component.

### `ComponentBase::init()` and the option allowlist

`ComponentBase::init()` (`src/Core/ComponentBase.php` lines 35-48) is a template method:

```php
$this->options = array_intersect_key(array_replace_recursive($this->options, $options), $this->options);
$this->initOptions($options);
if ($context !== null) { $this->initContext($context); }
```

The first line is the allowlist mechanism: `array_intersect_key(..., $this->options)` **trims the incoming options down to the keys the component itself declares**. Therefore:

- a component **must declare its keys in `public $options = [...]`** for an outside value to take effect; keys it does not declare are dropped even when passed.
- this is what makes `EXT_FOLLOW_APP` (passing the whole app's `$options` straight in) safe — the component only claims its own handful of keys.
- to force re-initialisation use `reInit()` (lines 52-56); while `init_once` is true a repeated `init()` is ignored unless you pass `__force__`.

A subclass has only two hook points: `initOptions(array $options)` to handle options (as `RouteHookRewrite` merges `rewrite_map`, `src/Component/RouteHookRewrite.php` lines 28-31), and `initContext(object $context)` for initialisation side effects (such as attaching a route hook). **Do not override `init()` itself** — `RouteHookWebInstaller` does override it, but the first thing it does is `parent::init()` (`src/Ext/RouteHookWebInstaller.php` lines 84-93), purely to add one [`Lang::_()->importDefaultSentences()`](../reference/Component-Lang.md) after initialisation.

### Values of the `ext` option (including the `EXT_*` modes)

`ext` is a `[class name => value]` table. The value decides how it loads (the decision logic is in `src/Core/KernelTrait.php` lines 375-421):

| Value                       | Constant (value)            | Behaviour                                       | When to use                                                                                                                           |
| ------------------------ | ---------------- | ---------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| `false` / `null` / `0`   | `EXT_DISABLE`(0) | not loaded (such falsy values are filtered out by `array_filter()` first)        | switching off an extension the framework enables by default (e.g. [`GlobalEvent`](../reference/Component-GlobalEvent.md) is off by default)                                               |
| `true`                   | `EXT_DEFAULT`(1) | **equivalent to `EXT_FOLLOW_APP` below: initialise with the whole option set of *this level's* app, `init($this->options, $this)`** | the most common "switch it on" — the extension and the app **share one set of app options**, so put whatever keys it needs into the app's `$options`                                                                    |
| an array                       | —                | `init(array, $this)`                        | pass only this extension's own options                                                                                                                   |
| `'@method'`                 | —                | call that method on this app, take its return value and process it recursively                  | options that must be decided at runtime (`RouteHookRewrite::class => '@myRewriteOptions'` in `overriding.md`)                                                    |
| an option-key string                  | —                | take `$this->options[that key]` and process it recursively           | switch an extension on/off with a boolean option                                                                                                                  |
| `App::EXT_FOLLOW_APP`(2) | —                | `init($this->options, $this)`: initialise with every app option | the explicit form of `true`; the framework passes it the same way for [`Console`](../reference/Core-Console.md)/[`Route`](../reference/Core-Route.md) (`src/Core/KernelTrait.php` lines 329, 335) |
| `App::EXT_ROOT_HOLD_POSISION_ONLY`(0) | —    | its value is `EXT_DISABLE`: **register it as a shared class only, do not init**; the first `::_()` creates it | the framework's root-component stage (`DbManager`/`RedisManager`/`Admin`/`User`/`GlobalEvent`, see [Chapter 2-2](lifecycle.md)) |
| `App::EXT_SKIP_INIT`(-1) | —                | just `::_()` to get the instance, **no init**                 | when you want lazy initialisation, or only a singleton placeholder                                                                                                                  |
| `App::EXT_RENEW`(3)      | —                | take the old instance's options and **re-init a new object**                  | rebuild on every request (`prepareServe()` runs dynamic extensions with `$default=EXT_RENEW`, `src/Core/KernelTrait.php` lines 501-506)                                      |

> **What exactly does `true` mean?** The decision is one sentence: both `true` and `EXT_DEFAULT` mean "use the `$default` the caller passed in". And the `$default` `initComponents()` passes for the **app's `ext` table** is `EXT_FOLLOW_APP` (`src/Core/KernelTrait.php` lines 339-340), so writing `true` inside an app's own `ext` means **follow this level's app options** (`init($this->options, $this)`), **not** "use the extension's own default options". Write an array to give the extension only its own options; write `'option-key'` to take that key's value from the app and decide recursively (the framework's default table uses `RouteHookPathInfoCompat => 'path_info_compact_enable'` for exactly this).

The framework's own real usage (copy it): in [`DuckPhp`](../reference/DuckPhp.md)'s default `ext` table (`src/DuckPhp.php` lines 38-43), `Lang`/`RouteHookRewrite`/[`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md)/[`RouteHookResource`](../reference/Component-RouteHookResource.md) are `true` (= follow the app options), [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) is the option key `'path_info_compact_enable'`; and at the root-component stage `DbManager`/[`RedisManager`](../reference/Component-RedisManager.md)/[`Admin`](../reference/GlobalAdmin-Admin.md)/[`User`](../reference/GlobalUser-User.md)/`GlobalEvent` use **`EXT_ROOT_HOLD_POSISION_ONLY` (placeholder only, see [Chapter 2-2](lifecycle.md))** (`src/DuckPhp.php` lines 106-112).

### The extension lifecycle hook: `initContext()`

An extension has no separate lifecycle: its "startup" is `init()`, and `init()` is called during the app's `initComponents()` stage (the timeline in [Chapter 2-2](lifecycle.md)). To take part in request handling, attach a route hook inside `initContext()` — that is the standard stance for framework extensions:

```php
// src/Ext/RouteHookWebInstaller.php lines 75-78
protected function initContext(object $context): void
{
    Route::_()->addRouteHook([static::class, 'Hook'], 'prepend-inner');
}
```

```php
// src/Ext/MyMiddlewareManager.php lines 31-35 (the equivalent, through RouteHookManager)
protected function initContext(object $context): void
{
    RouteHookManager::_()->attachPreRun()->append([static::class, 'Hook']);
}
```

The hook's static method receives `$path_info`; returning a truthy value means "I handled this request" (a short-circuit), and returning `false` lets later hooks and the default route pass. The four attachment positions and the execution order are in [Chapter 2-4 Route hooks](route-hooks.md).

### Replacing framework components

Two verified paths:

1. **Swap the singleton** (`::_(new instance)` from Chapter 4-1): in `JsonView::init()`, `View::_(static::_())` (`src/Ext/JsonView.php` line 34) — from then on `View::_()` returns [JsonView](../reference/Ext-JsonView.md). Any class `extends View` can take over the view this way.
2. **Point an option at an implementation class**: `DbManager`'s `database_class` option (`src/Component/DbManager.php` lines 172-177) — when non-empty, `new $class()` replaces the default [`DuckPhp\Db\Db`](../reference/Db-Db.md). Implement [`DuckPhp\Db\DbInterface`](../reference/Db-DbInterface.md) in your own database wrapper class and put it in that option.

## Common patterns

```php
// 1) give one extension only its own options (array value)
'ext' => [
    \DuckPhp\Ext\JsonView::class => ['json_view_skip_vars' => ['debug_info']],
],

// 2) return the extension options dynamically from a method of this app ('@method' value)
'ext' => [
    \DuckPhp\Component\RouteHookRewrite::class => '@myRewriteOptions',
],
// in the app class: protected function myRewriteOptions() { return ['rewrite_map' => [...]]; }

// 3) initialise an extension by hand in a test (bypassing app assembly)
MyExt::_(new MyExt())->init(['my_option' => 1], App::_());

// 4) switch off an extension the framework enables by default
'ext' => [\DuckPhp\Component\RouteHookRewrite::class => false],

// 5) for a real assembly see tests/data_for_tests/Ext/PermissionMenu/System/PermissionMenuApp.php
'ext' => [\DuckPhp\Ext\PermissionMenu::class => true],
```

## Common errors

| Symptom                              | Cause                                   | Fix                                                                                                 |
| ------------------------------- | ------------------------------------ | -------------------------------------------------------------------------------------------------- |
| Options passed to an extension have no effect                     | The keys are not declared in that extension's `public $options`, so the allowlist drops them | Add the keys (with defaults) to the extension class                                                                                    |
| An extension's hook never runs                       | `initContext()` did not attach a hook, or the position/return value is wrong     | Follow `RouteHookWebInstaller::initContext()`; make sure you return `false` to pass through                                         |
| `'@method'` in `ext` reports a missing method     | The method must live on **this app class** (it may be protected)         | Add the method to your [App](../reference/Core-App.md) subclass, or use an array value                                                  |
| `App::_()` inside an extension returns a different app         | The extension was init'ed in a child phase, and `App::_()` is "the current app"      | Use the `$context` you were passed (the second argument of `init`) — it is the host app                                                               |
| You wanted to replace `Db` but extended `DbManager`       | Wrong layer                                  | Implement `DbInterface` and set the `database_class` option; only touch [DbManager](../reference/Component-DbManager.md) when you need different connection management |
| You overrode `init()` and forgot `parent::init()` | Neither the option allowlist nor `is_inited` ran               | The first statement must be `parent::init($options, $context)`                                                           |

## Next steps

- [Chapter 4-3 Replacing framework behaviour](replace-behavior.md): the complete list of replacement techniques such as `::_(new instance)` and `database_class`.
- [Chapter 3-5 Overriding and replacement](overriding.md): the `ext` table and `EXT_*` from the consumer's point of view.
- Reference manual: [DuckPhp\Core\ComponentBase](../reference/Core-ComponentBase.md), [DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md), [DuckPhp\Ext\RouteHookWebInstaller](../reference/Ext-RouteHookWebInstaller.md), [DuckPhp\Ext\MyMiddlewareManager](../reference/Ext-MyMiddlewareManager.md), [DuckPhp\Ext\JsonView](../reference/Ext-JsonView.md)
