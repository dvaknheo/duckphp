# DuckPhp\Component\RouteHookRouteMap

Route map hook: lets certain URLs (exact, `*`-suffix, or `^…$` regex) jump straight to a specific "callback/controller", split into an "important" group (`route_map_important`, probed first up front) and a "normal" group (`route_map`, trailing cleanup).

## Introduction

`RouteHookRouteMap extends ComponentBase` works through two `Route` hooks:

- `prepend-inner` (PrependHook): runs `route_map_important` first;
- `append-outter` (AppendHook): tries `route_map` after the default resolution misses.

Map syntax (options `route_map`/`route_map_important` are `pattern => target`):

Patterns may be:
- A fixed full path (starts with `/`, compared exactly; `controller_url_prefix` may be stripped);
- `^…$` (a full regex; the `x` modifier allows a trailing `#…` comment);
- Starting with `@` (compiled into a regex with named captures from `{name(:regex)?}` segments — handled by `compile()`);
- A trailing `*` = prefix match, with the remainder fed to `Route::Parameter` as para.

Targets (callbacks) may be `Class@method` (singleton `_()`), `Class->method` (new), or a real callback invoked via `setParameters` and executed.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class RouteHookRouteMap extends ComponentBase`
- Target: URLs handled immediately by the map (the default Route is short-circuited).

## Options

`RouteHookRouteMap::$options`:

| Option | Default | Description |
|---|---|---|
| `controller_url_prefix` | `''` | A url prefix stripped uniformly before matching. |
| `route_map_important` | `[]` | The important group (probed first). |
| `route_map` | `[]` | The normal group (probed later). |

At runtime you can also append dynamically with `assignRoute`/`assignImportantRoute`; `getRouteMaps()` reads both groups back.

## Usage

```php
RouteHookRouteMap::_()->init([
  'route_map_important' => [
     '/login' => 'Public@action_login',
     '@page/{id:\d+}'  => 'Post@showPage',
  ],
  'route_map' => [
     '/go/*'   => 'Legacy@forward',   // the * segment goes into Parameter
  ],
], App::_());
```

## Details

- Logic order: prepend → important map → "default routing" → append → normal map (so the normal map acts as the fallback).
- compileMap replaces `~` in callbacks with the controller namespace (so you can write `~Post@…`).
- On a hit, `($callback)()` runs (when a real callable); string `Class@method` / `Class->method` are resolved and their method is put into `${attributes} CallingMethod`.
- matchRoute also supports (regex) and wild*.

## Methods

### Public methods

    public static function PrependHook($path_info)
Static shell: forwards to instance `doHook($path_info, false)` (prepend group).

    public static function AppendHook($path_info)
Static shell: forwards to instance `doHook($path_info, true)` (append group).

    public function compile(string $pattern_url, array $rules = []): string
Compile a pattern of the form `{name(:regex)?(optional?)}` into a full regex (`~^…$ #comment~x`).

    public function assignRoute($key, $value = null)
Dynamically append a route mapping (arrays batch-add).

    public function assignImportantRoute($key, $value = null)
Dynamically append an important route mapping (higher priority).

    public function getRouteMaps()
Returns both groups (normal/important) of the current map config.

    public function doHook($path_info, $is_append)
Main entry: picks the map group per `$is_append` and tries to match (on hit, invokes and returns `true`).

### Protected methods

    protected function initContext(object $context): void
Attach at the two hook slots `prepend-inner` + `append-outter`.

    protected function compileMap(array $map, string $namespace_controller): array
Map preprocessing: `@` compilation / `~namespace` expansion.

    protected function matchRoute(string $pattern_url, string $path_info, &$parameters): bool
The pattern matcher: fixed string / `^` regex / `*` wildcard, exporting parameters.

    protected function getRouteHandelByMap(array $routeMap, string $path_info)
Walk a map group, take the first matching pattern and return its target.

    protected function adjustCallback($callback, array $parameters)
Resolve `@`/`->`/callable callbacks, write Route parameters, and return the callable target.

    protected function doHookByMap(string $path_info, array $route_map): bool
Find a handler with the given map; on hit, invoke it and return `true`.

## Related links

- [DuckPhp\Core\Route](Core-Route.md)
- Same route-hook family as rewrite/resource; the Lister lists the mappings on its address — see Component-RouteLister.
