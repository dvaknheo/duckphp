# 3-4 Component sharing and cross-app communication

> What this solves: with several apps, what should be shared as one copy (database, logs, cache) and what must stay separate (views, language, config); how apps call each other, how events are broadcast, and where shared data goes.
> Prerequisites: [Chapter 3-1](advanced-phase.md), [Chapter 3-2](mount-app.md). About 20 minutes.
> Examples: the `/visit`, `/proxy` and `/orders` actions in `tests/data_for_tests/ZThirdDemo`, with the matching assertions in `ZThirdDemoTest`.

## One copy or one each: know the two destinies first

| Destiny | Who belongs to it | Behaviour |
|---|---|---|
| **Shared** | The components the root app initialises in the **root phase**: `Logger`, [`SystemWrapper`](../reference/Core-SystemWrapper.md), [`CoreHelper`](../reference/Core-CoreHelper.md), [`Console`](../reference/Core-Console.md), [`DbManager`](../reference/Component-DbManager.md), [`RedisManager`](../reference/Component-RedisManager.md) (plus [`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md)/[`GlobalUser`](../reference/GlobalUser-GlobalUser.md)/[`GlobalEvent`](../reference/Component-GlobalEvent.md) when enabled) | `::_()` returns the same instance from **any** phase |
| **One per app** | The components and extensions each app `init`s itself: [`Route`](../reference/Core-Route.md), [`View`](../reference/Core-View.md), [`Lang`](../reference/Component-Lang.md), [`Configer`](../reference/Component-Configer.md), custom extensions… | One instance per phase (options may differ too) |

Measured (`ZThirdDemoTest.php`):

```php
$root_logger = spl_object_id(Logger::_());
$root_lang   = spl_object_id(Lang::_());
App::_()->toThisChild(ThirdApp::class);              // enter the child app's phase
spl_object_id(Logger::_()) === $root_logger;         // true  -> shared
spl_object_id(Lang::_())   !== $root_lang;           // true  -> one each
App::Phase('');
```

Why the design: logs/databases are only sensible as "one per process", whereas each app's **language and wording, route table and view directory** are naturally independent.

### Making something shared, or making it independent

```php
// shared: declare it in the root app's ext (the framework marks it as shared)
'ext' => [ \DuckPhp\Component\GlobalEvent::class => true ],

// independent: create a fresh copy in the current phase instead of using the shared one
$this->createLocalObject(DbManager::class);          // this is what the framework does for local_database internally
```

The lazy switch: a child app's `local_database => true` / `local_redis => true` (with `database_list`/`redis_list`) means "this app gets its own connection".

```php
ThirdApp::class => [
    'local_database' => true,
    'database_list' => [['dsn' => 'sqlite:' . __DIR__ . '/shop.db']],
],
```

## Talking between apps: three ways

### ① Switch phase directly (the plainest)

```php
// ZThirdDemo/src/Controller/MainController.php :: visit()
$phase_before = App::Phase();
App::_()->toThisChild(ThirdApp::class);
$greet = ShopBusiness::_()->greetWho();     // <- this singleton belongs to the child app's phase
App::Phase($phase_before);                  // <- you must switch back
```

The point: inside the child phase, `ShopBusiness::_()` is **another instance** (its options and resolved config are the child app's too).

### ② The phase proxy `PhaseProxy` (without leaving the current phase)

```php
// ZThirdDemo :: proxy()
$child_phase = App::_()->options['app'][ThirdApp::class]['__phase__'];   // ':shop'
$proxy = PhaseProxy::CreatePhaseProxy($child_phase, ShopBusiness::class);
$ret = $proxy->placeOrder(2002);      // runs inside the :shop phase; the phase is unchanged afterwards
```

[`PhaseProxy::__call()`](../reference/Component-PhaseProxy.md) temporarily switches to the target phase, makes the call, then switches back. It suits "call once and leave".

> ⚠️ When you pass a **class name** it creates the object with `new` (**not** that phase's `::_()` singleton). To work on the singleton inside the phase, pass the object: `PhaseProxy::CreatePhaseProxy($phase, ShopBusiness::_())`, or just use approach ①.

### ③ Getting hold of the root app (from a child app's point of view)

```php
$root = App::Root();          // the root app instance (no phase switch)
$root = App::Root(true);      // fetch the root instance and switch the current phase back to root
$root->options['namespace'];
```

## The event bus: `GlobalEvent`

**It is off by default** (`EXT_DISABLE`); enable it in the root app first:

```php
'ext' => [ \DuckPhp\Component\GlobalEvent::class => true ],
```

```php
// the main app (root phase) listens for an event from the child app: ZThirdDemo/src/System/MainApp.php :: onInit()
GlobalEvent::_()->globalOn('third.ordered', '', function ($order_id) {
    self::$orders[] = $order_id;          // in a real project this might log or push to a message queue
});

// the child app (child phase) broadcasts: ZThirdDemo/third/Business/ShopBusiness.php :: placeOrder()
GlobalEvent::_()->fire('third.ordered', $order_id);
```

API at a glance:

| Method | What it does |
|---|---|
| `on($event, $callback)` | Bind to the **current phase** |
| `globalOn($event, $phase, $callback)` | Bind to a given phase (`''` = root) |
| `fire($event, ...$args)` | Trigger; **the callback runs in the phase it was registered in**, and the phase is switched back afterwards |
| `all()` | See every listener (for troubleshooting) |
| `remove($event, $phase = null, $callback = null)` | Remove a listener |

Naming convention: event names use an "in progress / done" suffix (`registering` / `registered`, `logining` / `logined`), matching the framework's built-in events (Chapter 2-13).

## Where shared data goes: a decision table

| Data | Where | Notes |
|---|---|---|
| Read-only config (one copy per app) | each app's `config/<name>.php` | The per-phase override described in Chapter 3-5 applies here too |
| Global settings (passwords, environment) | `DuckPhpSettings.config.php` / `.env` → `Setting()` | Chapter 1-5 |
| Options that must be **rewritable at runtime** (e.g. `is_debug`) | [`ExtOptionsLoader`](../reference/Component-ExtOptionsLoader.md) (`data_file_enable`) | Its file is `DuckPhpApps.config.php`; `data_file_bump_allowed`/`data_file_bump_keys` decide which keys can be written back |
| Request-scoped temporary data | [`Runtime`](../reference/Core-Runtime.md) | Cleared with the request |
| State shared across requests | [`Cache`](../reference/Component-Cache.md) / [`RedisCache`](../reference/Component-RedisCache.md), or your own table | The only reliable way to share when several apps run on several machines |

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| A child app's events are never received | `GlobalEvent` is not enabled (default `EXT_DISABLE`) | Declare it in the root app's `ext` |
| `::_()` inside an event callback grabs the wrong instance | `fire()` switches to "the phase it was registered in" before running the callback | Pick the right phase when registering (the second argument of `globalOn`) |
| An object called through `PhaseProxy` has no state from that phase | Passing a class name makes it `new` a fresh object | Pass an object, or switch phases with `toThisChild()` |
| A child app changing `options` affects the main app | You changed the options of a **shared instance** | Shared components are one object; isolate with `local_*` or `createLocalObject()` |
| Two apps each write their own log file | They use the same `Logger` (shared) | The log path comes from `path_log`; to split files per child app give it independent options or an independent [Logger](../reference/Core-Logger.md) |

## Next steps

- [Chapter 3-5 Overriding and replacement](overriding.md): swap someone else's behaviour without editing their code.
- [Chapter 3-7 A worked example: front end + back office + API](case-multi-app.md): every mechanism in this volume combined.
