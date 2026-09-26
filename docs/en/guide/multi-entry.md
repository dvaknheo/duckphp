# 4-6 Multiple Entry Points, Domains, and SAPIs

> What this solves: how the same `src/` is reused by multiple entry points (`index.php` / `demo.php` / `api.php` / `rpc.php` / `cli.php`); how to split applications by domain/subdirectory; where the web / cli / rpc SAPIs diverge.
> Prerequisites: [Chapter 1-7 The Minimal Deployment Checklist](deployment.md), [Chapter 2-3 Advanced Routing](routing.md), [Chapter 2-16 CLI and Scheduled Tasks](cli.md), [Chapter 3-1 Application Tree and Phase Basics](advanced-phase.md), [Chapter 3-2 Mounting an External App](mount-app.md). About 20 minutes.
> All examples come from `demo/public/` and `demo/src/System/`; start a server with `php -S 127.0.0.1:8080 -t demo/public` and visit each entry point.

## Minimal example

The entry points that really exist under `demo/public/`, each demonstrating one "one codebase, many entry points" posture:

| Entry point | What it demonstrates |
|---|---|
| `index.php` | The standard web entry: detect Composer → fall back to [AutoLoader](../reference/Core-AutoLoader.md) → `App::RunQuickly()` (`demo/src/System/App.php`) |
| `demo.php` | Five layers in a single file: [App](../reference/Core-App.md)/Controller/Business/Model/View all in one file (`namespace MySpace\…`), with [`CallableView`](../reference/Ext-CallableView.md) configured |
| `helloworld.php` | The minimal posture: one controller class + one `RunQuickly()` ([Chapter 4-4](embed.md)) |
| `traditional.php` | All-function mode: `action_*` functions + [`RouteHookFunctionRoute`](../reference/Ext-RouteHookFunctionRoute.md) + [`EmptyView`](../reference/Ext-EmptyView.md); the view is native PHP at the end of the file |
| `just-route.php` | Routing only: not even an application class — just `Route::RunQuickly()` |
| `api.php` | API server: [`Ext\RouteHookApiServer`](../reference/Ext-RouteHookApiServer.md) maps `/api.php/test.foo2?a=1&b=2` to `\Api\test::foo2(1,2)` and returns JSON |
| `rpc.php` | JSON-RPC both ends: [`JsonRpcExt`](../reference/Ext-JsonRpcExt.md) acts as server-side dispatcher (`onRpcCall`) and as client (`JsonRpc\` namespace autoloading) |
| `dbtest.php` | The full model/pagination/CRUD chain + mounted by `App.php` as a child app at `/db_test/` (see `onPrepare()` in `demo/src/System/App.php`) |
| `doc.php` | Documentation reader: reads the md/svg under `docs/` and renders them via marked.js (demonstrates "non-framework pages" coexisting) |

> `phpinfo()` files like `i.php` are just local probes, not example entry points, so they are not listed here.

These entry points **share the same `demo/src/`**: `index.php` goes through full layering, `demo.php` crams five layers into one file, `dbtest.php` is both a standalone entry point and a mounted child app — "one codebase, many entry points" is not a special case, it is a default capability.

## How it works

### The dispatch point of entry points: `KernelTrait::run()`

Every entry point ends up in two methods of [DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md) (`src/Core/KernelTrait.php`):

```
RunQuickly($options, $after_init = null)          // lines 89–100
  └─ init($options) → 调 $after_init() → 分流：  // calls $after_init(), then dispatches:
       PHP_SAPI === 'cli' && isRoot() && cli_enable → execute()   // Console
       其它                                            → serve()    // Web (其它 = "otherwise")

run()                                              // lines 468–475
  └─ cli_enable ? execute() : serve()

serve()   // lines 476–500: run Runtime + Route once; on failure runChildren(); finally _On404()
execute() // lines 534–545: Console::_()->run()
```

So the first decision of "who handles this request" is: **is the SAPI cli, and does the root app have `cli_enable` on**. Web requests always go through `serve()`; CLI requests go through `execute()` when `cli_enable=true`, otherwise they also go through `serve()` (you can "request" a URL from the command line — see [Chapter 2-16](cli.md)).

### What `cli_enable` does

- In `RunQuickly()` it decides whether a **CLI** request enters [Console](../reference/Core-Console.md) or the web flow (line 95 of `KernelTrait.php`).
- In `run()` it decides the direction for **any SAPI** (line 470).
- Child apps are not checked separately: when the root app turns on `cli_enable`, the CLI commands of the whole process (including all child apps) register into the same Console, prefixed by phase (`php cli.php shop-<command>` — see [Chapter 2-16](cli.md) and [Chapter 3-1](advanced-phase.md)).

### Multiple entry points reusing the same `src/`

An entry-point file itself does only three things: find the autoloader, pass a few options to `RunQuickly()`, and run. All business code lives in `demo/src/`:

- `demo/src/System/App.php`: the standard entry class; its `options` set exception layering and `controller_method_prefix => 'action_'`; `onPrepare()` mounts `dbtest.php` as a child app (`'controller_url_prefix' => 'db_test/'`).
- `demo/src/System/AppWithAllOptions.php`: a boilerplate listing **every available option** as comments (generated by `tests/genoptions.php`) — use it as an option dictionary.

Adding an entry point = adding a php file under `public/` that `require`s the same autoloader and calls `XxxApp::RunQuickly($options)`. `$options` can override the in-class defaults (that is what the comment "you can also adjust options here" at the end of `demo.php` means).

### Multiple domains / multiple sites

Two sanctioned ways to split applications by domain or subdirectory within one codebase:

**A. One entry point + change options by domain in `onPrepare()`** (same application class, different behavior per domain):

```php
protected function onPrepare(): void
{
    parent::onPrepare();
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === 'admin.example.com') {
        $this->options['controller_url_prefix'] = 'admin/';
        $this->options['path_view'] = 'view-admin';
    }
}
```

**B. Multiple entry points + child apps** (recommended, [Chapter 3-1](advanced-phase.md), [Chapter 3-2](mount-app.md)): one entry file per domain/subdirectory; each entry `RunQuickly`s the same root app, which mounts different child apps at different `controller_url_prefix`. `dbtest.php` mounted at `/db_test/` is a ready-made example.

The relationship between `path` and `controller_url_prefix`: `path` decides **where files are found** (views/config/controllers), `controller_url_prefix` decides **where the URL starts** matching (rule E001 in [Chapter 2-3](routing.md): a mismatched prefix fails outright, giving the parent app a chance to hand the request to other child apps). Neither replaces the other — in subdirectory deployments `path` often stays unchanged while `controller_url_prefix` becomes the subdirectory name.

### Subdirectory deployment

When the app lives under `/myapp/` instead of the domain root, three things must line up:

1. **URL generation**: always use `__url('about/me')` for internal links — it prepends the basepath (the subdirectory prefix) automatically; a hand-written `/about/me` 404s ([Chapter 2-3](routing.md)).
2. **Route resolution**: nginx/apache rewrite turns `/myapp/xxx` into the PATH_INFO of `index.php`; on servers where PATH_INFO is unavailable, set `'path_info_compact_enable' => true` to parse from the query string instead ([Chapter 1-7](deployment.md), [Chapter 2-3](routing.md)).
3. **Static resources**: resources served through the framework go through `controller_resource_prefix`, built as `'/' . controller_url_prefix . controller_resource_prefix` ([Chapter 3-3](static-resources.md)); in production, serving resources straight from docroot is recommended.

### Multiple SAPIs: web / cli / api / rpc

- **web**: `serve()`, through [Runtime](../reference/Core-Runtime.md) + [Route](../reference/Core-Route.md) ([Chapter 2-2](lifecycle.md)).
- **cli**: `execute()`, through Console ([Chapter 2-16](cli.md)).
- **api**: still `serve()`, but [DuckPhp\Ext\RouteHookApiServer](../reference/Ext-RouteHookApiServer.md) hangs at the `prepend-inner` position and **takes over before the default route**: `api.php/test.foo2?a=1&b=2` → calls `\Api\test::foo2(1, 2)`, picks arguments by reflected parameter names, and outputs the result as JSON. For options see `apiserver_namespace` / `apiserver_base_class` in `demo/public/api.php` (`~BaseApi` means the `BaseApi` under the current namespace; the service class must implement it, otherwise the request is treated as a miss).
- **rpc**: the single file `demo/public/rpc.php` plays both ends. On the server side, `onRpcCall($_POST)` of [DuckPhp\Ext\JsonRpcExt](../reference/Ext-JsonRpcExt.md) dispatches `Namespace.Service.method` to a local service class; on the client side, use `JsonRpcExt::Wrap(ServiceClass::class)` or `\JsonRpc\ServiceName::_()` (autoloaded via the `jsonrpc_namespace` prefix; the class extends [DuckPhp\Ext\JsonRpcClientBase](../reference/Ext-JsonRpcClientBase.md)) — the call is POSTed to the server through `jsonrpc_backend`.

## Common patterns

**1. Add an API entry point** (after `api.php`):

```php
$options = [
    'namespace' => '',
    'ext' => [
        \DuckPhp\Ext\RouteHookApiServer::class => [
            'apiserver_namespace' => '\\Api',
            'apiserver_base_class' => '~BaseApi',
            'apiserver_404_as_exception' => true,
        ],
    ],
];
\DuckPhp\DuckPhp::RunQuickly($options);
```

**2. Add an RPC entry point** (after `rpc.php`): in the server-side action call `JsonRpcExt::_()->onRpcCall($_POST)`; on the client side wrap with `CalcService::_(JsonRpcExt::Wrap(CalcService::class))` and then call as usual — it becomes remote automatically.

**3. CLI and Web sharing one entry point**:

```php
// public/index.php is both the web entry point and the CLI entry point
\MyProj\System\App::RunQuickly(['cli_enable' => true]);
```

`php public/index.php help` enters Console; `curl http://…/index.php` enters Web.

**4. Switch configuration by domain** (option A under "Multiple domains" above).

## Common errors

| Symptom                             | Cause                                                          | Fix                                                                     |
| ------------------------------ | ----------------------------------------------------------- | ---------------------------------------------------------------------- |
| `php cli.php` enters the web flow instead of the command list | `cli_enable` is `false` or not passed                                  | Pass `'cli_enable' => true` in the entry point ([Chapter 2-16](cli.md))                          |
| The API entry point 404s everything                    | `RouteHookApiServer` is not enabled, or `apiserver_namespace` does not match the actual namespace | Check the `ext` options against `demo/public/api.php`                                   |
| API class fails the base-class constraint → silent 404          | `apiserver_base_class` written wrong or missing                                  | Use the `~BaseApi` form (`~` = current `namespace` + `apiserver_namespace`)             |
| RPC client reports "class not found"                 | Autoloading for the `JsonRpc\` prefix is not registered                                       | Make sure `JsonRpcExt` is in `ext` and `jsonrpc_enable_autoload` is true                |
| After subdirectory deployment, all internal links 404               | Hand-written `/xxx` absolute paths                                             | Always use `__url()` ([Chapter 2-3](routing.md))                                      |
| After subdirectory deployment, all routes 404                  | The rewrite did not strip the subdirectory, or PATH_INFO is lost                              | The nginx/apache recipes in [Chapter 1-7](deployment.md); or enable `path_info_compact_enable` |
| With many entry points you cannot tell "who actually handled this request"            | Many entry points, many child apps, no decision order                                             | Walk the troubleshooting order below                                                            |

**Troubleshooting order for "who handles this request"**:

1. See which **entry file** the URL lands in (`/api.php/...` enters `api.php`, `/db_test/...` is taken over by the child-app prefix, everything else enters `index.php`).
2. Inside the entry point, look at the SAPI: `PHP_SAPI === 'cli'` with `cli_enable` → Console; otherwise `serve()`.
3. Inside `serve()`: default route → failure → `runChildren()` asks each child app in `options['app']` order → all fail → `_On404()`.
4. Still no answer: turn on `is_debug` and inspect `Route::_()->options['route_error']` (E001 = prefix mismatch), or temporarily `var_dump(App::Phase())` to confirm which phase you are in.

## Next steps

- [Chapter 4-7 Test Infrastructure and the Coverage Pipeline](coverage.md): turn multi-entry smoke checks into tests.
- Revisit [Chapter 3-2 Mounting an External App](mount-app.md): the combined punch of child apps + multiple entry points.
- The reference manual: [DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md), [DuckPhp\Ext\RouteHookApiServer](../reference/Ext-RouteHookApiServer.md), [DuckPhp\Ext\JsonRpcExt](../reference/Ext-JsonRpcExt.md), [DuckPhp\Ext\JsonRpcClientBase](../reference/Ext-JsonRpcClientBase.md)
