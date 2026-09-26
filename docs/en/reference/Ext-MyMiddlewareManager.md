# DuckPhp\Ext\MyMiddlewareManager

## Introduction

`MyMiddlewareManager` is the middleware-manager extension: it strings the middleware declared in `options['middleware']` into an onion-style call chain, runs the real routing at the innermost layer (`Route::defaultRunRouteCallback`), and fires `onPostMiddleware()` for the wrap-up once it finishes.

At initialisation it automatically hangs `Hook` on `RouteHookManager`'s pre-run hooks (`attachPreRun()->append([static::class,'Hook'])`), so the middleware chain runs before every route.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class MyMiddlewareManager extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Meaning |
|---|---|---|
| `middleware` | `[]` | The middleware list (executed from outside in). Each item may be a callable, or the string `Class@method` (the `_()` singleton) / `Class->method` (`new`). |

## Usage

```php
\DuckPhp\Ext\MyMiddlewareManager::_()->init([
    'middleware' => [
        AuthMiddleware::class.'@handle',   // authentication
        LogMiddleware::class.'->handle',   // logging
    ],
], $app);
// afterwards every routed request runs in the order authentication → logging → route
```

Short-circuiting inside a middleware (no `$next` call, return a response directly):

```php
function ($request, \Closure $next) {
    if (!is_logged_in()) {
        return 'please log in first';      // the manager outputs it and returns true ⇒ the controller no longer runs
    }
    return $next($request);    // let it through
}
```

## Caveats

- `doHook()`: it reverses `middleware` and builds nested callbacks with `array_reduce` (the onion model), whose innermost layer is `runSelfMiddleware()` (which really runs the default route and records `defaultResult`, marking this round as "the inner layer ran"); once the chain finishes it calls `onPostMiddleware()`, and then:
  - **a middleware short-circuited** (did not call `$next`) **and returned a response** (`null`/`false` do not count as a response) → the response is sent through `outputResponse()`, and `doHook()` returns `true` ⇒ `Route::run()` decides "the request has been handled", so **the controller does not run again**;
  - in every other case it returns `defaultResult` (the result of the inner default callback).
- A short-circuit response is handled as a string (`echo`); to hand it to another response object, override `outputResponse()`. **Once the inner layer has run, a middleware's return value takes no part in output** — the controller echoes as it goes, so by then the content is already out (to post-process a response, use an output buffer in the middleware yourself).
- Not calling `$next` yet returning `null`/`false`: treated as "not handled", keeping the old behaviour (`Route::run()` still runs the default route callback). A middleware that forgot to call `$next` therefore never turns into a blank page.
- `getRequest()`/`getResponse()` currently return an empty `\stdClass`/an empty string; a subclass may override them to pass request/response objects around.
- The return value of `Hook` is the return value of `doHook()`, which is the route hook's "hit" verdict.

## Methods

### Public methods

    public function __construct()
Initialises the empty `request`/`response` objects.

    public static function Hook($path_info)
The static hook entry point, forwarding to `doHook`.

    public function doHook($path_info = '')
Runs the middleware chain: it resolves each middleware and nests them onion-style, with the innermost layer running the default route; when a middleware short-circuits (no `$next` and a response returned) it outputs that response and returns `true`, otherwise it returns `defaultResult`.

### Protected methods

    protected function initContext(object $context): void
Appends `Hook` to `RouteHookManager`'s pre-run hooks.

    protected function runSelfMiddleware(): string
The innermost layer: runs `Route::defaultRunRouteCallback()` and records `defaultResult` (the "inner layer ran" mark is made by `doHook()`'s inner closure, so overriding this method cannot lose it).

    protected function isHandledResponse($response): bool
The short-circuit response test: `null`/`false` = not handled (pass it to the default route), anything else (including an empty string and `true`) = handled.

    protected function outputResponse($response): void
Sends the short-circuit response out: the base class only `echo`es a non-empty string, and a subclass may override it (for example to feed a response object of its own).

    protected function onPostMiddleware(): void
The callback after the middleware chain ends (a subclass may override it for teardown).

    protected function getResponse(): string
Gets the response (the base class returns an empty string).

    protected function getRequest()
Gets the request object (the base class returns `$this->request`).

## Related links

- [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) — the hook mounting point
- [DuckPhp\Core\Route](Core-Route.md) — where the default route callback comes from
