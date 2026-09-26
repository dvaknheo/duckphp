# DuckPhp\Core\Route

The routing-resolution and URL-generation component: maps the current URL (PATH_INFO) to "controller class + method name", and executes/generates routes.

## Introduction

`Route` is DuckPHP's default routing core. It does two things:

1. **Inbound routing**: resolves the current request URL (i.e. PATH_INFO) into the controller and action to call, finds the callable via reflection, and finally executes it;
2. **Outbound routing**: provides the unified `Url()/Res()/Domain()` to generate in-app URLs, static-resource URLs and the domain, so views and code no longer hand-assemble URLs.

Its behavior is controlled almost entirely by `$options`: the namespace prefix, controller postfix, method prefix, welcome class/welcome method, the optional URL prefix and resource prefix, the controller map, etc. Routing also supports 4 + finally layers of "route hooks", letting you insert custom logic before and after the default resolution.

By default `Route` is wired up by `DuckPhp\DuckPhp` through `ext` (the `RouteHook*` family are the extra side-mounted hook components; `Route` itself carries the default MVC matching). To use it standalone without App/Thread: `Route::RunQuickly([...])`.

Implementation-wise, `class Route extends ComponentBase` also `use`s two inline traits — `Route_Helper` (helpers for PATH_INFO/parameters/current route-call info) and `Route_UrlManager` (`_Url/_Res/_Domain`, the URL generator) — and their methods are merged into `Route`'s method list below.

## Options

The full `Route::$options` (defaults from the source):

| Option | Default | Description |
|---|---|---|
| `namespace` | `''` | Project namespace (without `\\`). Final controller full name = `namespace + namespace_controller + path class name + controller_class_postfix`. |
| `namespace_controller` | `'Controller'` | The sub-namespace holding controllers; starting with `\\` means an absolute sub-namespace (no `namespace` prepended). |
| `controller_path_ext` | `''` | Path extension filter (e.g. `.html`). When set, only paths carrying this suffix match, and the suffix is stripped after a successful match. Empty means no check. |
| `controller_welcome_class` | `'Main'` | The "welcome controller": the controller class-name segment used when the URL is very short / the path blocks are empty. |
| `controller_welcome_class_visible` | `false` | Whether the path may explicitly write the `Main/…` segment. With `false`, an explicit welcome-controller name is judged `E009` and 404s. |
| `controller_welcome_method` | `'index'` | The default method name when the trailing "method segment" of the URL is empty. |
| `controller_class_adjust` | `''` | Class/method name **normalization rules**. The string may hold several semicolon-separated directives: `uc_class` (ucfirst the last path block), `uc_method` (ucfirst the method name), `uc_full_class` (ucfirst every segment). An array is also accepted. |
| `controller_class_base` | `''` | Controller base-class constraint. When set, the target controller must be `is_subclass_of` that base class, else `E004`. The string may use `~` as a placeholder, replaced with the controller namespace prefix at check time. |
| `controller_class_postfix` | `'Controller'` | Class-name postfix (appended to the block read from the path). |
| `controller_method_prefix` | `''` | Method-name prefix. Empty means the path method segment is called as-is; many hosts (e.g. `DuckPhp` apps) set it to `action_`. |
| `controller_prefix_post` | `'do_'` | POST-only secondary prefix. On POST requests, `prefix + do_ + method name` (i.e. `action_do_*`) is tried first and replaces the call on a hit. Empty disables this logic. |
| `controller_class_map` | `[]` | Controller class-name map: `old full name => new full name`. Configurable statically; also writable at runtime via `replaceController()`. |
| `controller_resource_prefix` | `''` | Static-resource prefix. Used by `_Res()/Res()` to build resource URLs (can be `https://cdn…` / `//cdn…` / relative `res/`). |
| `controller_url_prefix` | `''` | URL path prefix. Participates in URL validation and generation (see `pathToClassAndMethod()` and `getUrlBasePath()`). |
| `controller_fix_mistake_path_info` | `true` | When there is no PATH_INFO and the script is `/index.php`, automatically fills PATH_INFO from the path of `REQUEST_URI` and writes it back. |

The default error codes (used by route_error; see caveat 4): `E001` url prefix mismatch / `E002, E003` controller-class resolution or reflection failure / `E004` does not extend controller_class_base / `E005` hidden method (`__…`) not callable / `E006` static method not callable / `E007` target method not found by reflection / `E008` path extension mismatch / `E009` explicitly written invisible welcome controller.

## Usage

### Standalone instance (without App)

```php
use DuckPhp\Core\Route;
Route::RunQuickly([
    'namespace' => 'MyApp\\Sub',
    'controller_class_postfix'  => 'Controller',
    'controller_method_prefix'  => 'action_',
    'controller_url_prefix'     => 'api',
]);
```

### Mid-level test / advanced bind path (no real request needed)

```php
Route::_()->bind('/foo/bar', 'GET');   // set path & request method
$ok = Route::_()->run();              // start resolving; returns success
if (!$ok) { echo Route::_()->getRouteError(); }
```

### Reading parameters and current call info

```php
$all = Route::Parameter();        // all route parameters (including bound/path blocks)
$id  = Route::Parameter('id', 9);
$c   = Route::_()->getRouteCallingClass();
$m   = Route::_()->getRouteCallingMethod();
```

### Outbound URLs: methods / global functions

```php
$link = Route::Url('user/info');        // in-app relative URL (with url prefix & path_info)
$res  = Route::Res('css/app.css');       // static resource (can emit a CDN via controller_resource_prefix)
$dom  = Route::Domain();                // without protocol scheme: //host
$dom2 = Route::Domain(true);            // with scheme:      http(s)://host[:port]

$base = Route::_()->defaultUrlHandler(''); // base path
```

Corresponding global functions (the same-named convenience wrappers mapped in CoreHelper): `__url('...')`, `__res('...')`, `__domain()`, `__url_basepath()`.

### Route hooks (the key extension point)

4 nesting positions: `prepend-outter / prepend-inner / [default routing] / append-inner / append-outter`, plus a finishing group `finally` (`finally-inner` / `finally-outter`). Each hook receives `$path_info`; returning truthy means this round is handled — a hook returning true short-circuits the later process (finally hooks also short-circuit each other until one true breaks).

```php
Route::_()->addRouteHook(function ($path_info) {
    if (substr($path_info, 0, 6) === '/rpc/') {
        myRpcRouter($path_info);      // handle it yourself
        return true;                 // consumed; no further default routing
    }
    return false;
}, 'prepend-inner');
```

`dumpAllRouteHooksAsString()` exports the current hooks for debugging.

## Routing rules quick reference

- Default case (no prefix): URL segments resolve as: the first segment onward are "controller path segments", the last segment is the method segment.
- `Welcome` (no extra segments): when the path is empty and no pre hook hits → the welcome class + welcome method runs.
- Full class-name format: `{namespace_controller}\\{class name before postfix}{controller_class_postfix}`; then the `controller_class_map` remapping applies.
- With `controller_method_prefix` empty the method name is the URL's last segment; set to `action_`, the URL piece `/bar` → calls `action_bar`.
- On POST with `controller_prefix_post` hit, `<target method prefix>do_<method segment>` is called first. (The framework usually has method prefix = action_, giving `action_do_*`.)
- `getCallbackFromClassAndMethod()` reflection constraints: hidden methods starting with `__` are not callable, statics are not callable, and the target must extend `class_base` (if set).
- The is-controller check is separately available via `isController($class)`.

## Configuration example

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'namespace' => 'Demo',
        'namespace_controller' => 'Controller',
        'controller_class_postfix' => 'Controller',
        'controller_method_prefix' => 'action_',
        'controller_welcome_class' => 'Main',
    ];
}
```

For controllable child apps/prefixes or CDN-style resources, set controller_url_prefix/controller_resource_prefix together at the app/top level as described in Options.

## Caveats

1. Class names and postfix: the actually instantiated controller class name = path read + `controller_class_postfix`; except the welcome class, which needs no matching url segment, all must really exist and be instantiable via reflection `newInstance()` (no-arg constructor).
2. Non-callables under the reflection rules: hidden (`__`-prefixed) and static methods are rejected (`E005/E006`); a missing method is `E007`. Avoid writing public actions as static/protected.
3. PHP case: analysis works on the literal string path. No case folding happens internally; keep controllers on PSR-4 (`FooController->action_bar`) with consistent casing.
4. `controller_fix_mistake_path_info` defaults to true and "fixes" PATH_INFO in projects treating `/index.php` as a virtual docroot; if under php -S you unexpectedly keep getting an `/index.php` prefix, consider this key or your understanding of the URL prefix.
5. Readable errors: resolution failures end up in the default hook's `_On404()` / the host's unified handling; the concrete reason is readable via `Route::_()->getRouteError()` as the `E0xx` text above (see the list under the Options section).
6. No global state outside the options array: each instance phase isolates one Route; path-related properties (route_error, calling_*, parameters) stay with the instance, so callbacks can keep reading "who just matched".

## All options

```php
        'namespace' => '',
        'namespace_controller' => 'Controller',

        'controller_path_ext' => '',
        'controller_welcome_class' => 'Main',
        'controller_welcome_class_visible' => false,
        'controller_welcome_method' => 'index',

        'controller_class_adjust' => '',
        'controller_class_base' => '',
        'controller_class_postfix' => 'Controller',
        'controller_method_prefix' => '',
        'controller_prefix_post' => 'do_',

        'controller_class_map' => [],

        'controller_resource_prefix' => '',
        'controller_url_prefix' => '',
        'controller_fix_mistake_path_info' => true,
```

## Methods

> The methods below merge Route with the two inline traits it uses (`Route_Helper`, `Route_UrlManager`); all are available members of `Route::_()`.

### Public methods

    public static function RunQuickly(array $options = [], ?callable $after_init = null)
Boot: instantiate + init + run by environment (for standalone run experiments)

    public static function Route()
Returns the current (in-phase) Route instance

    public static function Parameter($key = null, $default = null)
Returns route parameters: a single value + default with a key, otherwise the whole array

    public function _Parameter($key = null, $default = null)
Instance implementation of Parameter

    public function bind($path_info, $request_method = 'GET')
Binds path & request method on the current instance (for no-HTTP environments/unit tests)

    public function run()
Runs routing once: pre hooks → (default callback) → post hooks; returns success

    public function clear()
Runs the finally finishing hooks one by one; stops at the first truthy return

    public function forceFail()
Sets is_failed true, making this run count as failed (even if earlier hooks succeeded)

    public function addRouteHook($callback, $position = 'append-outter', $once = true)
Registers a hook at the given position (prepend/append × inner/outer, or finally-*)

    public function defaultToggleRouteCallback($enable = true)
Toggles whether the "default route callback" is attempted (for pure custom routing that only runs hooks)

    public function defaultRunRouteCallback($path_info = null)
Runs the default callback once directly with the default rules (generates the callback internally and calls it)

    public function defaultGetRouteCallback($path_info)
Resolves path into [controller object, method name] (mapping/constraints all happen in this chain); returns null on failure and writes route_error

    public function getControllerNamespacePrefix()
Returns the namespace prefix for controller calls (with trailing `\`)

    public function replaceController($old_class, $new_class)
Replaces the old controller with the new class in controller_class_map at runtime

    public function getRunResult(): bool
(Internal, reads is_failed) gets the "should it really fail" result

    public function isController(string $class): bool
Judges by postfix/class_base whether a full name counts as a controller class

    public function dumpAllRouteHooksAsString()
Exports the pre/run/post hook groups (var_export) for hook debugging

    public function setParameters($parameters)
Overwrites the route parameters wholesale

    public function getRouteError()
Gets the last route_error text (`E0xx`); it is an empty string when there is no error.

    public function getRouteCallingPath()
Gets this match's path segment (prefix stripped)

    public function getRouteCallingClass()
Gets the controller full name to be/already instantiated this time

    public function getRouteCallingMethod()
Gets this action's method name

    public function setRouteCallingMethod($calling_method)
Actively rewrites the "current action method" (rare)

    public static function PathInfo($path_info = null)
Static shell: forwards to the instance `_PathInfo`.

    public function _PathInfo($path_info = null)
With a value, setPathInfo (writes PATH_INFO); without, returns the current getPathInfo.

    public static function Url($url = null)
Static shell: forwards to the instance `_Url`.

    public function _Url($url = null)
Generates an in-app URL: delegates to `url_handler` if set, otherwise `defaultUrlHandler` (absolute `/` kept, `?`/`#` appended, relative joined to the base path).

    public static function Res($url = null)
Static shell: forwards to the instance `_Res`.

    public function _Res(?string $url = null)
Builds a static-resource URL with `controller_resource_prefix`: `//`/`https?://` kept as-is, relative gets the base path prepended first.

    public static function Domain($use_scheme = false)
Static shell: forwards to the instance `_Domain`.

    public function _Domain($use_scheme = false)
Builds the host from `REQUEST_SCHEME`/`HOST`/`SERVER_PORT`: no scheme by default, included with `true`.

    public function defaultUrlHandler($url = null)
Default URL generation: values starting with `/` returned as-is, otherwise joined with the base path; supports `?` `#` prefix appending

    public function setUrlHandler($callback)
Sets a custom URL handler (overrides the default _Url logic)

    public function getUrlHandler()
Returns the current url_handler (if any)

### Protected methods

    protected function pathToClassAndMethod(string $path_info): ?array
Main chain: strips the url prefix/extension, calls adjustClassBaseName, assembles the full name with class_map, returns [full name, method]

    protected function adjustClassBaseName(string $path_info): array
Splits the path into path blocks plus the last "method segment"; merges per the welcome rules; runs controller_class_adjust

    protected function doControllerClassAdjust(array $blocks, string $method): array
Runs the uc_method/uc_class/uc_full_class normalization family

    protected function getCallbackFromClassAndMethod(string $full_class, string $method, string $path_info): ?array
Reflection check and construction: rejects class_base/hidden/static violations, reflectively newInstance and fetch method, returns [object, method]; writes route_error on failure

    protected function adjustMethod(string $method, \ReflectionClass $ref): string
On POST with controller_prefix_post hitting `action_do_*`, replaces the target method

    protected function getPathInfo(): string
Gets the current PATH_INFO; supports the controller_fix_mistake_path_info /index.php fix and writes back as needed

    protected function setPathInfo(string $path_info): void
Writes PATH_INFO to (the context field/global) $_SERVER

    protected function getUrlBasePath(): string
Derives the URL base path from DOCUMENT_ROOT/SCRIPT_FILENAME's script parent dir + controller_url_prefix

## Related links

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — component base class (init/context)
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — the serve-flow host (calls Route::run)
- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) — provides isolated reads when there is no superglobal context
- More routing-related components: `RouteHookRewrite / RouteMap / Resource / PathInfoCompat` etc. (see the corresponding reference pages)
- guide: [routing](../guide/routing.md), [layers](../guide/layers.md)
