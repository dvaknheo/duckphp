# 2-13 The Event System

> What this solves: letting the parts of an application (even components across applications) react to "something happened" **without changing each other's code**.
> Prerequisites: [Chapter 2-4 Route Hooks](route-hooks.md), [Chapter 2-9 Helpers and Global Functions](helper.md). About 15 minutes.
> Examples come from `tests/data_for_tests/ZThirdDemo` and can be run for real with `wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` (**run it from the repository root**).

## Minimal example

In `ZThirdDemo`, a child app broadcasts an event after placing an order, and the main app listens to it in its own phase:

```php
// the main app (root phase) listens: tests/data_for_tests/ZThirdDemo/src/System/MainApp.php :: onInit()
GlobalEvent::_()->globalOn('third.ordered', '', function ($order_id) {
    self::$orders[] = $order_id;   // in a real project this might write a log or push to a message queue
});

// the child app (child phase) broadcasts: tests/data_for_tests/ZThirdDemo/third/Business/ShopBusiness.php :: placeOrder()
GlobalEvent::_()->fire('third.ordered', $order_id);
```

**Works out of the box, nothing to "turn on" first**: `GlobalEvent::_()` is a lazy singleton; with no `ext` configuration at all, `GlobalEvent::_()->on(...)` + `fire(...)` still fire as usual (same for `Helper::OnGlobalEvent()` / `Helper::FireGlobalEvent()`).

```php
// tests/data_for_tests/ZThirdDemo/src/System/MainApp.php
'ext' => [
    GlobalEvent::class => true,
],
```

> The `GlobalEvent::class => self::EXT_DISABLE` at line 117 of `src/DuckPhp.php` is easily misread as "off by default": it only means that **the assembly phase does not pre-place this component into the current phase** (such `EXT_DISABLE` entries don't remain in `options['ext']` after init either). The component is created on demand, so whether events work has nothing to do with this line; the explicit line in ZThirdDemo is an "optional line for clarity", not a requirement.

## How it works

### Event callbacks are bound to a **phase**

The core design of `GlobalEvent`: every listener is recorded as a `[event name, phase, callback]` triple. When `fire()` dispatches, it **first switches the current phase to the phase at registration time, executes the callback, then switches back** (`src/Component/GlobalEvent.php` lines 27–39). This means:

- Every `::_()` inside the callback gets the singleton **in the phase where it was registered**;
- The listener and the listened-to side can live in different phases (the typical scenario: the main app listens to a child app's events) without manually switching phases.

### API at a glance

| Method | Purpose |
| --- | --- |
| `on($event, $callback)` | Bind to the **current phase** (internally calls [`globalOn($event, App::Phase(), $callback)`](../reference/Core-App.md)) |
| `globalOn($event, $phase, $callback)` | Bind to a specified phase (`''` = the root phase) |
| `fire($event, ...$args)` | Call the callbacks one by one in registration order; **no return value** |
| `all()` | Return the whole registry (for troubleshooting) |
| `remove($event, $phase = null, $callback = null)` | Without the last two, clears that event; otherwise filters by phase+callback |

### Helper-side entries

The project's `Helper` classes ([`System\SystemHelper`](../reference/Foundation-System-SystemHelper.md) / [`Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md) / [`Controller\ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md)) all provide same-named static methods with identical semantics:

```php
Helper::OnGlobalEvent('third.ordered', function ($order_id) { /* … */ });  // = GlobalEvent::_()->on(...)
Helper::FireGlobalEvent('third.ordered', $order_id);                       // = GlobalEvent::_()->fire(...)
Helper::RemoveEvent('third.ordered');                                      // = GlobalEvent::_()->remove(...)
```

### Event name convention: the "in progress / completed" suffixes

The framework's built-in event names are **constants**, defined in two places, with values equal to the constant names themselves:

- [`User`](../reference/GlobalUser-User.md): `EVENT_ACTION_USER_REGISTERING` / `_REGISTERED` / `_LOGINING` / `_LOGINED` / `_LOGOUTING` / `_LOGOUTED` (values like `'ACTION_USER_LOGINED'`) and `EVENT_SERVICE_USER_*` (the same six);
- [`Admin`](../reference/GlobalAdmin-Admin.md): `EVENT_ACTION_ADMIN_LOGINING` / `_LOGINED` / `_LOGOUTING` / `_LOGOUTED` plus `EVENT_SERVICE_ADMIN_LOGINED` and three others (admins have no registration).

The division of labor between the two groups:

| Group | Who fires it | Purpose |
|---|---|---|
| `EVENT_ACTION_*` (Action) | The framework fires it: `register()/login()/logout()` of [`GlobalUser`](../reference/GlobalUser-GlobalUser.md), `login()/logout()` of [`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md) | Listen to "what happened in this login/registration/logout", e.g. send an in-site message after login |
| `EVENT_SERVICE_*` (Service) | **The framework does not fire it** — these are names your login service fires itself | For subdividing stages like "before validation / after persistence" in your Service layer (`src/Foundation/Business/BusinessHelper.php` only provides same-named alias constants) |

Listening at the Action layer (cleanup after a successful login):

```php
Helper::OnGlobalEvent('ACTION_USER_LOGINED', function ($post) {
    // $post is exactly the data passed to login()
});
Helper::OnGlobalEvent('ACTION_ADMIN_LOGOUTED', function ($admin_id) {
    // after logout you get the admin id
});
```

Firing yourself at the Service layer (the framework only gives the names):

```php
use DuckPhp\Component\GlobalEvent;
use DuckPhp\GlobalUser\User;

GlobalEvent::_()->fire(User::EVENT_SERVICE_USER_REGISTERING, $post);
// …validate, persist…
GlobalEvent::_()->fire(User::EVENT_SERVICE_USER_REGISTERED, $post);
```

> Event names are **string constants**; the literal `'ACTION_USER_LOGINED'` works too, but prefer constants like `User::EVENT_ACTION_USER_LOGINED` (renames are caught at all reference points immediately).

**Convention**: `xxxING` means "in progress" (you can still intervene), `xxxED` means "completed" (do cleanup). Custom events should follow the same suffixes, e.g. `order.creating` / `order.created`.

### Division of labor with Chapter 2-4 route hooks

| | Hooks ([Route](../reference/Core-Route.md) Hook) | Events ([GlobalEvent](../reference/Component-GlobalEvent.md)) |
|---|---|---|
| Structure | **Single chain**: one position, one string of callbacks | **Broadcast**: one event, many listeners |
| Return value | **Has a return value**; a truthy return short-circuits later hooks | **No return value**; `fire()` always calls every listener |
| Typical use | Intercept/rewrite/map requests (e.g. [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md)) | Notify that "something happened" (e.g. an order was placed) |
| Cross-phase | No (executes within the route's current phase) | Yes (the callback executes in the phase at registration time) |
| Configuration entry | `Route::addRouteHook()` / the `ext` option | Turn on `GlobalEvent::class => true` in the `ext` option |

In one sentence: **to intercept requests use hooks; to broadcast state use events**. See [Chapter 2-4](route-hooks.md) for details.

### `Ext\EventManager` (**not recommended**)

⚠️ **Don't use it in new code**: the API is similar to `GlobalEvent` but **has no concept of phase** (cross-phase events can't be handled), and the framework has no internal references to it. For events use `GlobalEvent` (the rest of this chapter) — full reasoning in [Chapter 4-13](ext-classes.md) §11.

## Common patterns

```php
// 1) Listen in the current phase (the most common)
Helper::OnGlobalEvent('order.created', function ($order) {
    Logger::_()->info('order created: ' . $order['id']);
});

// 2) Listen with an explicit phase (cross-application scenarios)
GlobalEvent::_()->globalOn('third.ordered', '', function ($order_id) {
    // this callback always executes in the root phase
});

// 3) Broadcast an event
Helper::FireGlobalEvent('order.created', ['id' => 42, 'amount' => 199.00]);

// 4) Troubleshooting: see what listeners are currently registered
var_dump(GlobalEvent::_()->all());

// 5) Remove a listener
Helper::RemoveEvent('order.created');                    // clear all listeners of that event
GlobalEvent::_()->remove('order.created', '', $callback); // remove only the specified phase+callback
```

## Common errors

| Symptom | Cause | Fix |
| --- | --- | --- |
| No events are received at all | `GlobalEvent` is not turned on (default `EXT_DISABLE`) | Declare `GlobalEvent::class => true` in the root app's `ext` |
| `::_()` inside an event callback gets the wrong instance | `fire()` switches the phase to "the phase at registration time" before running the callback | Pick the right phase at registration (the second argument of `globalOn`) |
| Duplicate registration makes a callback run multiple times | `globalOn` skips when the same event+phase+callback triple exists; but different closures count as different callbacks | For dedup use the same callable (e.g. `[Class::class, 'method']`); don't `on` before every `fire` |
| You want the event to "intercept" later flow | Events are broadcasts: no return value, no short-circuit | Use a route hook ([Chapter 2-4](route-hooks.md)) or branch directly in business code |
| `remove($event, $phase, $callback)` doesn't remove | The source's filter condition is "keep only when phase and callback **both** differ" | Pass exactly the same phase and callback as at registration |

## Next steps

- [Chapter 2-4 Route Hooks](route-hooks.md): the single-chain/short-circuit semantics of hooks, and their division of labor with events.
- [Chapter 2-14 Cache and Redis](cache.md): if an event callback needs to write shared state, the cache is a reliable place.
- The reference manual: [DuckPhp\Component\GlobalEvent](../reference/Component-GlobalEvent.md), [DuckPhp\Ext\EventManager](../reference/Ext-EventManager.md).
