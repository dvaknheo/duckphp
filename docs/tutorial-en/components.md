# Components and Extensions

DuckPHP adopts a componentized architecture, where all functional modules are optional and replaceable.

## Built-in Components (Component)

Components are under the `DuckPhp\Component\` namespace and are automatically initialized when the framework starts.

### DbManager — Database Manager

Manages database connection pools, supporting read-write separation.

```php
DbManager::_()->_DbForRead()->fetchAll("SELECT * FROM users");
DbManager::_()->_DbForWrite()->execute("UPDATE users SET name=?", 'new');
```

### RedisManager — Redis Manager

```php
RedisManager::Redis(0)->set('key', 'value');
RedisManager::Redis(0)->get('key');
```

### Configer — Configuration Reader

Loads PHP configuration files from the `config/` directory.

```php
// Load config/app.php
Configer::_()->_Config('app', 'key', 'default');
// Or
Helper::Config('app', 'key');
```

### Cache — Cache

Default implementation is an empty cache (does not store), can be extended:

```php
Cache::_()->get('key', 'default');
Cache::_()->set('key', 'value', 3600);
```

### Pager — Pagination

Generates pagination HTML:

```php
Pager::_()->PageHtml($total, [
    'page_size' => 10,
    'page_key' => 'page',
]);
// Or
Helper::PageHtml($total);
```

### Lang — Internationalization

Multi-language support:

```php
// Configure language file config/lang/en_US.php
// Content: return ['hello' => 'Hello'];

__l('hello');            // Output 'Hello'
__l('hello {name}', ['name' => 'World']); // 'Hello World'
__h($str);               // HTML escape
__hl('hello');           // Internationalization + HTML escape
```

Language auto-detection (by priority):

1. URL parameter (`?lang=en_US`)
2. Cookie
3. HTTP `Accept-Language` header
4. Command line environment variable
5. Default language configuration

### RouteHook* — Route Hooks

| Component | Function |
|---|---|
| `RouteHookCheckStatus` | Check maintenance mode/installation status |
| `RouteHookRewrite` | URL rewriting |
| `RouteHookRouteMap` | Route mapping matching |
| `RouteHookResource` | Static resource handling |
| `RouteHookPathInfoCompat` | PATH_INFO compatibility mode |

## Optional Extensions (Ext)

Extensions are under the `DuckPhp\Ext\` namespace, enabled on demand via `options['ext']`.

### CallableView — Functional View

Use class methods instead of view files, suitable for APIs or simple projects:

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\CallableView::class => true,
    ],
    'callable_view_class' => \MyApp\View\Views::class,
];
```

View class:

```php
namespace MyApp\View;

class Views
{
    public static function main($data)
    {
        extract($data);
        ?>
        <h1><?= $title ?></h1>
        <?php
    }
}
```

### JsonView — JSON View

Converts all view output to JSON, suitable for pure API projects:

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\JsonView::class => true,
    ],
    'json_view_skip_vars' => ['debug_info'],  // Exclude certain variables
];
```

### MiniRoute — Mini Route

Simplified routing mode, suitable for small projects. Compared to the default route, it removes controller suffix and method prefix conventions:

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\MiniRoute::class => true,
    ],
];
```

MiniRoute default options:
- `controller_class_postfix` = `''` (no suffix)
- `controller_method_prefix` = `''` (no prefix)
- Other options are consistent with the main route

After enabling, URL `/user/list` will directly map to `User::list()`, instead of `UserController::action_list()`.

### JsonRpcExt — JSON-RPC Support

Provides JSON-RPC server and client support:

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\JsonRpcExt::class => true,
    ],
    'jsonrpc_namespace' => 'JsonRpc',           // Service namespace
    'jsonrpc_backend' => 'https://127.0.0.1',     // Backend address
    'jsonrpc_is_debug' => false,                // Debug mode
    'jsonrpc_enable_autoload' => true,            // Auto-load services
    'jsonrpc_timeout' => 5,                       // Timeout
];
```

After enabling, the framework will automatically handle JSON-RPC requests, exposing class methods under the specified namespace as RPC services.

### StrictCheck — Strict Check

Strict mode checking during development.

## Sub-Application System (App Nesting)

DuckPHP supports running multiple isolated applications in the same process — sub-applications.

### Configuring Sub-Applications

```php
class App extends DuckPhp
{
    public $options = [
        'app' => [
            'BlogApp' => [
                'controller_url_prefix' => 'blog/',  // URL prefix
                'namespace' => 'BlogApp\\',
                // Other options...
            ],
            'ApiApp' => [
                'controller_url_prefix' => 'api/',
                'namespace' => 'ApiApp\\',
                'ext' => [
                    \DuckPhp\Ext\JsonView::class => true,
                ],
            ],
        ],
    ];
}
```

### Sub-Application Lifecycle

Sub-applications have the same lifecycle as the main application. The main application's `onBeforeChildrenInit()` callback is triggered before sub-application initialization.

### Phase Isolation

Each application has independent space in the phase container, managed by `PhaseContainer`:

```php
// Switch current phase
App::Phase('BlogApp');
// At this point, all singletons under BlogApp are visible
$data = BlogService::_()->getData();

// Switch back to main application
App::Phase(App::Root()->getOverridingClass());
```

## Global Functions

The framework defines the following global helper functions (in `src/Core/Functions.php`):

| Function | Description |
|---|---|
| `__h($str)` | HTML escape |
| `__l($str, $args)` | Internationalization |
| `__hl($str, $args)` | Internationalization + HTML escape |
| `__url($url)` | Generate URL |
| `__res($url)` | Generate resource URL |
| `__domain($use_scheme)` | Get current domain |
| `__json($data)` | JSON encoding |
| `__display($view, $data)` | Render view fragment |
| `__var_dump(...)` | Debug output (debug mode only) |
| `__var_log($var)` | Debug log |
| `__trace_dump()` | Print call stack |
| `__debug_log($msg)` | Debug log |
| `__logger()` | Get logger instance |
| `__is_debug()` | Check if debug mode |
| `__is_real_debug()` | Check if real debug mode |
| `__platform()` | Get platform identifier |
