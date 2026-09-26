# DuckPhp\Ext\RouteHookFunctionRoute

## Introduction

`RouteHookFunctionRoute` is the "function-style routing" extension: it maps the current PATH_INFO to a **callable function/callback name** and runs it, instead of taking the standard "controller class + action method" route. By default it sits on the `append-inner` hook position (it is only tried after the default route fails).

Mapping rule: strip the leading `/` from `PATH_INFO`, replace `/` with `_`, and use `index` for the empty string; then prepend the prefix (`function_route_method_prefix`, default `action_`) plus `controller_prefix_post` on POST (e.g. `do_`) to get the callback name; if it `is_callable`, call it and the hook has hit. On a 404 you can configure a fallback to `{prefix}index`.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class RouteHookFunctionRoute extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Description |
|---|---|---|
| `function_route` | `false` | Whether it is enabled (a reserved switch). |
| `function_route_method_prefix` | `'action_'` | The callback-name prefix. |
| `function_route_404_to_index` | `false` | Whether to fall back to calling `{prefix}index` when nothing matched. |

## Usage

```php
\DuckPhp\Ext\RouteHookFunctionRoute::_()->init([], $app);

// request /hello/world (GET) -> tries to call the callback action_hello_world
// request /hello/world (POST with controller_prefix_post='do_') -> tries action_do_hello_world first
function action_hello_world() { echo 'hello'; }
```

## Caveats

- Whether the callback name is "callable" is decided by `is_callable`: it may be a defined function, a closure or any other callable (registered up front).
- `_Hook` first uses the POST parameters to decide whether to add the `do_` prefix; if that misses with POST present, it tries once more without `do_`.
- This hook sits at `append-inner`: it only gets a chance when the default MVC route does not match (a "function-route fallback").

## Methods

### Public methods

    public static function Hook($path_info)
The static hook entry, forwarding to `_Hook`.

    public function _Hook($path_info = '/')
Builds the callback name from PATH_INFO and tries to call it; on a miss it falls back per the options (`index`).

### Protected methods

    protected function initContext(object $context): void
Attaches `Hook` to Route's `append-inner` hook position.

### Private methods

    private function runCallback($callback)
Runs the callback when it is callable and returns `true`.

## Related links

- [DuckPhp\Core\Route](Core-Route.md) — the hook host
- [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) — hook-list management
