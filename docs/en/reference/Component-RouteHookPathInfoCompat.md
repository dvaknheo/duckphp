# DuckPhp\Component\RouteHookPathInfoCompat

Compat route hook for environments without PATH_INFO / with compact URLs: when the server does not provide a canonical PATH_INFO, the route path is carried in a query key (e.g. `?_r=…`); URL generation also encodes back into this query form.

## Introduction

When enabled (`path_info_compact_enable` not false at init), `RouteHookPathInfoCompat extends ComponentBase` installs on `Route`:

- An **incoming hook** (`prepend-outter` · `Hook`): in environments without PATH_INFO, reconstructs path_info from the request's `action_key`/class key and writes it back into Route (keeping a copy as `PATH_INFO_OLD`);
- A **URL handler** (`Url`): generated "relative URLs" are replaced with **basepath + `?{action_key}=route path`** (if `path_info_compact_class_key` is set, module and action each get their own key).

So in environments like `?…index.php?_r=/foo/bar`, you still get human-like clean routing, and generated links come out in the same compact query format.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class RouteHookPathInfoCompat extends ComponentBase`
- Subject target: `Route` (App's default hook).

## Options

`RouteHookPathInfoCompat::$options`:

| Option | Default | Description |
|---|---|---|
| `path_info_compact_enable` | true | Switch (hook/url handler are installed only at init). |
| `path_info_compact_action_key` | `'_r'` | The query key carrying the action route. |
| `path_info_compact_class_key` | `''` | Optional query key for the "module (class path segment)"; leave empty to put the whole path in the action key. |

## Usage

Enabling (usually DuckPhp's built-in ext already installs PathInfoCompat; otherwise do it manually):

```php
use DuckPhp\Component\RouteHookPathInfoCompat;
RouteHookPathInfoCompat::_()->init([
  'path_info_compact_enable' => true,     // this is the default
], App::_());
```

Then a request to /old-host/index.php?_r=user/detail routes normally as path `/user/detail` via Hook; links emitted by `Url('user/detail')` use the same query form.

## Caveats

- When PATH_INFO already exists you don't need it; this component only fills in "compact / no-path_info URLs" while keeping the API stable.
- Both Hook/Url still return false/or the original (no rewriting of absolute URLs), leaving the rest to Route's other hooks.

## Methods

### Public methods

    public static function Url($url = null)
Turn a relative url into `base + query(_r=…)` (absolute `/` or unhandled cases are returned as-is).

    public function onUrl(?string $url = null): string
URL generation implementation (supports a REQUEST_URI base, honors the option keys, merges the current query).

    public static function Hook($path_info)
Static shell: forwards to instance `_Hook`.

    public function _Hook($path_info)
The wrapper: reads the path from the (context or global) request's class/action keys and calls `Route::PathInfo(...)`; returns `false` (routing continues).

    (Note: the module parameter key `path_info_compact_class_key` may be an empty string.)

### Protected methods

    protected function initContext(object $context): void
When `enable=true`: `Route::addRouteHook([static::class,'Hook'],'prepend-outter')` and `Route::setUrlHandler([static::class,'Url'])`.

    protected function filteRewrite(string $url, &$ret = false): ?string
A capability hook reserved for external rewriting (currently returns the url as-is).


## Related links

- [DuckPhp\Core\Route](Core-Route.md) the mounting surface
- Siblings: RouteHookRewrite / RouteHookRouteMap / RouteHookResource
