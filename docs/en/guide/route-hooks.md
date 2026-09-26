# 2-4 Route hooks

> What this solves: you need to intercept a request **before** it reaches a controller, provide a fallback when nothing matches, or collect statistics while the request winds down — use a route hook instead of extending a controller base class.
> Prerequisites: [Chapter 2-2 The request lifecycle](lifecycle.md) (where hooks sit in the timeline), [Chapter 2-3 Routing in depth](routing.md). About 20 minutes.
> Examples: `demo/src/System/App.php` (real wiring), `tests/Ext/MyMiddlewareManagerTest.php` (middleware really running, including the onion order).
> To run it: `wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/Ext/MyMiddlewareManagerTest.php"` (**from the repository root**)

## Minimal example

Attach a hook that "takes `/health` away" from the controller:

```php
use DuckPhp\Core\Route;

Route::_()->addRouteHook(function (string $path_info) {
    if ($path_info !== 'health') {
        return false;          // not handling it; leave it to later hooks and the default route
    }
    header('Content-Type: text/plain');   // Helper::header() works too (a replaceable system wrapper, easier to test)
    echo 'ok';
    return true;               // hit -> routing stops here, the controller will not run
}, 'prepend-outter');
```

A request to `/health` prints `ok` and the controller never runs; any other path changes nothing. That is the minimal form of "hooks instead of inheritance" — no need to modify a controller base class for two special cases.

## How it works

### 1. Six positions and the short-circuit semantics

`Route::addRouteHook($callback, $position = 'append-outter', $once = true)`:

| Position | Which list | Relative to the default route | Semantics |
|---|---|---|---|
| `prepend-outter` | pre (**first**) | before | Runs first: state checks, URL rewriting, auth interception |
| `prepend-inner` | pre (last) | before | Close to routing: route mapping |
| `append-inner` | post (first) | after | First in line for fallbacks |
| `append-outter` | post (last) | after | The last fallback (static resources) |
| `finally-inner` | finally (first) | request wind-down | Runs during `Route::clear()` |
| `finally-outter` | finally (last) | request wind-down | Same, runs last |

**Short-circuiting is the core semantics of pre hooks**: as soon as any hook in the pre chain returns a truthy value, [`Route::_()->run()`](../reference/Core-Route.md) returns immediately and the controller does not run. Returning a falsy value (`false`/`null`) means "I am not handling it, carry on".

Two more switches (both on `Route`):

```php
Route::_()->defaultToggleRouteCallback(false); // turn off the default route callback (take over routing yourself)
Route::_()->forceFail();                       // force this routing to count as failed (let the caller do 404/fallbacks)
```

Child apps often use `App::_()->skip404Handler()`: it stops a child app that did not match from racing to output a 404, leaving the decision to the parent app.

### 2. Adding, removing and inspecting hooks

```php
use DuckPhp\Core\Route;

Route::_()->addRouteHook($cb, 'prepend-inner');          // attach directly (default is append-outter)
Helper::addRouteHook($cb, 'prepend-inner');              // the app/wiring-layer Helper (System\SystemHelper)
echo Route::_()->dumpAllRouteHooksAsString();            // * troubleshooting: print all three chains
```

Suggested troubleshooting order: dump first to see which hooks are on the chain and in what order, and only then suspect that your callback was never called.

> **To attach by name, move or remove hooks** (`append()`, `insertBefore()`, `moveBefore()`, `removeAll()`) use [`Ext\RouteHookManager`](../reference/Ext-RouteHookManager.md) — it is an `Ext\*` extension, so it is assembled only when you write it into the app's `ext`; see [Chapter 4-13](ext-classes.md) §3.

### 3. Where the built-in hooks sit

The framework's own routing abilities are hooks too, at these positions (all visible with `dump()`):

| Hook | Position | What it does |
|---|---|---|
| [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) | `prepend-outter` | PATH_INFO compatibility (the `?_r=` form, [Chapter 2-3](routing.md)) |
| [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md) | `prepend-outter` | URL rewriting |
| [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) | `prepend-inner` + `append-outter` | Route mapping (front-section matching + back-section fallback) |
| [`RouteHookResource`](../reference/Component-RouteHookResource.md) | `append-outter` | Serving static resources ([Chapter 3-3](static-resources.md)) |

> These four live in `common_options['ext']` and **work out of the box** (along with `Lang`). `Ext\` has four more hooks — `RouteHookApiServer`, `RouteHookWebInstaller`, `RouteHookFunctionRoute`, `RouteHookDirectoryMode` — which are **not assembled automatically**; write them into the app's `ext` to attach them (see [Chapter 4-13](ext-classes.md) §9). Note this is a different question from "can the class be loaded": AutoLoader only brings class files in on demand, whereas **assembling into the current phase** needs an `ext` declaration.

### 4. Choosing your intervention point

| Need | First choice | Why |
|---|---|---|
| Handle or intercept a few special URLs | Route hook (pre) | It has short-circuit semantics, so it really can stop the request |
| Symmetric logic around a request | Middleware ([`Ext\MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md), see [Chapter 4-13](ext-classes.md) §4) | The onion structure suits "before/after" naturally |
| Change one controller's behaviour | Override the controller class / `controller_class_map` ([Chapter 3-5](overriding.md)) | Precise down to the class, and it takes effect by configuration |
| Broadcast "something happened" | Global events ([Chapter 2-13](events.md)) | One-to-many, no return value, works across phases |
| Replace one framework ability | Override an extension / replace the singleton | Solve it at the assembly layer ([Chapter 4-3 Replacing framework behaviour](replace-behavior.md)) |
| Add something to every controller | **Think hooks first**, base-class inheritance second | Inheritance turns "a changeable ability" into "unchangeable bloodline" |

### 5. What about middleware? (`Ext\MyMiddlewareManager`)

Middleware is not DuckPHP's recommended way: the default is "route hooks + layered Helper", and middleware is just a compatibility layer for people used to Laravel / ThinkPHP (or PSR-15) style. To use it, attach it in the app's `ext` and list your middleware in the `middleware` option (the first entry is the outermost):

```php
$options = [
    'ext' => [\DuckPhp\Ext\MyMiddlewareManager::class => true],
    'middleware' => [\MyProj\Middleware\AuthMiddleware::class . '@handle'],
];
```

Inside a middleware, **not calling `$next` and returning a response directly** is the short-circuit: the manager emits that response and declares "the request is handled" — the controller will not run. Note that **`return null`/`false` means "I did not handle it"**, and that path is passed on to the default route as before. So:

- **Intercepting a request** (return/redirect when auth fails) → the middleware short-circuit `return 'content'`, or a route hook `prepend-outter` returning `true` (see §1 and §2 of this chapter);
- **Symmetric work around a request** (timing, logging, uniform response headers) → middleware;
- The three boundaries of short-circuit responses (string output only, no further processing once the inner layer ran, `null`/`false` passes through), the wiring details, the measured onion order, and the overridable `getRequest()`/`getResponse()`/`runSelfMiddleware()`/`outputResponse()` are in [Chapter 4-13](ext-classes.md) §4.

## Common patterns

**① Intercepting with a pre hook** (IP allowlists, forced redirection, canary routing):

```php
Route::_()->addRouteHook(function (string $path_info) {
    if (strpos($path_info, 'admin/') !== 0) { return false; }   // only the back office
    $ip = App::_()->isCli() ? '' : ($_SERVER['REMOTE_ADDR'] ?? '');
    if (preg_match('/^10\./', $ip)) { return false; }            // internal network passes, carry on to the default route
    header('HTTP/1.1 403 Forbidden', true, 403);
    echo 'Forbidden';
    return true;                                                 // intercepted: the controller will not run
}, 'prepend-outter');
```

> "Maintenance mode" needs no hook of your own: the framework ships it — the `is_maintain` option or the `duckphp_is_maintain` setting, with `error_maintain` pointing at an error view (`demo/view/_sys/error_maintain.php` is a ready-made one).

**② Using append/finally hooks for fallbacks and wind-down**: the post chain suits custom 404s and dynamic resources; the `finally` chain suits statistics and cleanup that must happen "whatever the outcome", for example:

```php
Route::_()->addRouteHook(function () {
    // request wind-down: it runs whether we matched, 404ed or threw (the finally in Chapter 2-2)
    MyMetrics::_()->flush();
    return false;
}, 'finally-outter');
```

**③ Troubleshooting hook order**:

```php
echo Route::_()->dumpAllRouteHooksAsString();   // prints all three chains
```

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| The hook does `return;` yet the controller still runs | A pre hook must return a **truthy** value to count as a hit | Write `return true;` explicitly |
| A hook is attached twice and the log appears twice | `addRouteHook()` was called repeatedly | Use the third argument `$once = true` (on by default), or remove it by name first (`Ext\RouteHookManager`, [Chapter 4-13](ext-classes.md) §3) |
| A middleware returned a response but the controller still runs | It returned `null`/`false` (or no value at all), so it counts as "not handled" and passes through | A short-circuit must return the response itself or `true`; see also the boundary table in [Chapter 4-13](ext-classes.md) §4 |
| A hook under `Ext\` does nothing at all | `Ext\` components are not assembled automatically | Declare it in the app's `ext` (e.g. `'ext' => [RouteHookFunctionRoute::class => true]`) |
| Someone else's 404 gets output before your post-hook 404 view | A child app fell back first | Use `App::_()->skip404Handler()` in the child app and let the parent app decide |
| You want to know "who actually handled this request" | All three chains are dynamic | Run `Route::_()->dumpAllRouteHooksAsString()` first, and only then suspect your own callback |
| The hook also ran under CLI | Hooks live on routing, and the `execute()` branch can trigger routing too | Tell them apart with `App::_()->isCli()` ([Chapter 2-16](cli.md)) |

## Next steps

- [Chapter 2-2 The request lifecycle](lifecycle.md): which step hooks are inserted at, and why `finally` always runs.
- [Chapter 2-3 Routing in depth](routing.md): how `route_map`/`route_map_important` relate to hooks.
- [Chapter 2-13 The event system](events.md): the broadcast-style intervention point, and how it divides work with hooks.
- [Chapter 3-5 Overriding and replacement](overriding.md): replacing one controller's implementation without writing a hook.
- Reference manual: [DuckPhp\Core\Route](../reference/Core-Route.md); for the `Ext\` ones (`RouteHookManager`/`MyMiddlewareManager`/`HookChain`) see [Chapter 4-13](ext-classes.md).
