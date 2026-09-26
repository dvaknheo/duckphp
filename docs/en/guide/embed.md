# 4-4 No Composer · single file · embedded

> What this solves: how to get DuckPHP running when you **have no Composer**, or when you only want to **drop one or two pages** into another project.
> Prerequisites: [Chapter 1-2 Installation and minimal example](install.md), [Chapter 1-7 The minimal go-live checklist](deployment.md). About 15 minutes.
> All examples come from `demo/public/` (`helloworld.php`, `just-route.php`, `traditional.php`) and `src/DuckPhpAllInOne.php`; start a server with `php -S 127.0.0.1:8080 -t demo/public` and visit them one by one.

## Minimal example

`demo/public/helloworld.php` (33 lines) shows the smallest "the framework as a library" stance: one controller class plus one `RunQuickly()`:

```php
class MainController
{
    public function index()
    {
        echo "hello world";
    }
}
$options = [
    'is_debug' => true,
    'namespace_controller' => "\\",   // the controller is in the root namespace, not the default Controller\
];
\DuckPhp\DuckPhp::RunQuickly($options);
```

Visiting `http://127.0.0.1:8080/helloworld.php` prints `hello world`. The whole file has no `namespace`, no `src/` directory and no Composer — the framework is used as an **ordinary library you `require`**.

## How it works

### Without Composer, how does the framework find classes?

DuckPHP ships a simplified PSR-4 style autoloader, [DuckPhp\Core\AutoLoader](../reference/Core-AutoLoader.md), which **does not depend on Composer**. It has two independent startup paths:

**Path A: load the framework itself only** (the `DuckPhp\` prefix → `src/`). The repository's `autoload.php` is just two lines:

```php
require __DIR__.'/src/Core/AutoLoader.php';
spl_autoload_register([DuckPhp\Core\AutoLoader::class ,'DuckPhpSystemAutoLoader']);
```

`bin/duckphp` (the framework's own scaffold command) uses the same form. Once registered, classes starting with `DuckPhp\` are `include_once`d by `DuckPhpSystemAutoLoader()` following the path `src/<sub-namespace>/<class name>.php` (`src/Core/AutoLoader.php` lines 222–234). **This step only solves "the framework can find itself"**; your project classes still cannot.

**Path B: make project classes findable too.** `AutoLoader::RunQuickly(['path'=>…])` initialises the project root and registers `spl_autoload_register([AutoLoader::class,'AutoLoad'])`; `AutoLoader::addPsr4('Namespace\\', 'directory')` then adds a "namespace → directory" mapping. `demo/cli.php` and `demo/public/index.php` contain the real form:

```php
// demo/cli.php (demo/public/index.php is the same shape)
if (!class_exists(\ProjectNameTemplate\System\App::class)) {
    \DuckPhp\Core\AutoLoader::RunQuickly([
        'path' => __DIR__.'/',
    ]);
    \DuckPhp\Core\AutoLoader::addPsr4("ProjectNameTemplate\\", 'src');
}
\ProjectNameTemplate\System\App::RunQuickly($options);
```

The `class_exists()` probe comes first: if Composer is present its autoload is used (path A is unnecessary), and only otherwise does it fall back to the framework's own [AutoLoader](../reference/Core-AutoLoader.md). That is the **standard probing stance** for "embedded without Composer".

> Reference manual: [DuckPhp\Core\AutoLoader](../reference/Core-AutoLoader.md) lists every option (`path` / `namespace` / `path_namespace` / `psr-4` / `autoload_path_namespace_map`) and the methods `assignPathNamespace()` / `clear()` and so on.

### `DuckPhpAllInOne`: one class is the whole application

[`DuckPhp\DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) in [src/DuckPhpAllInOne.php](../../src/DuckPhpAllInOne.php) packs **the app entry, the controllers, the view callbacks and the four Helper groups** into a single class:

- `__callStatic` embeds the **union of the four layers' Helpers** into this class: when you call a static method that does not exist on it, it looks for the first layer Helper declaring it in the order System → Controller → Business → Model and forwards the call (source lines 128–139). So inside its `action_*` methods you can call [`$this->Db()`](../reference/Db-Db.md), `$this->Setting()`, `$this->Show()` directly — note that at the reflection level these methods do not exist (IDEs rely on the 96 `@method` comments in the source); details in [Chapter 2-9](helper.md).


- Its constructor calls `embedMe()` (from line 145) to inject a set of default options:

| Option | Injected value | Effect |
|---|---|---|
| `namespace_controller` | `\` + this class's namespace | Controllers are the classes in this very namespace |
| `name` | `'@'` | The phase name uses the class-name basename |
| `controller_welcome_class` | `static::class` | The welcome page is **this class itself** |
| `controller_class_postfix` | `''` | No `Controller` suffix is appended to class names |
| `controller_method_prefix` | `'action_'` | Only `action_*` methods are actions |
| `cli_enable` | `true` | It is a CLI entry point too |
| `path_info_compact_enable` | `true` | It runs without PATH_INFO ([Chapter 2-3](routing.md)) |
| `duckphp_all_in_one_wrap_header_footer` | `true` | `_Show()` automatically wraps `view_header` / `view_footer` |

- Views are not view files but **class methods**: `viewToCallback()` (lines 86–93) turns `/` in the view name into `_` and looks for a `view_<name>` method; if it is found it is used as a callable view, and only otherwise does it fall back to the parent's file views. `_Show()` (lines 94–109) calls them in the order "head → body → foot". So a subclass only has to write `view_hello($data)` to have defined the `hello` view.
- `onPrepare()` (lines 63–71) registers `static::class` in `options['cmd']`, which is equivalent to `cli_command_with_app=true`: under CLI, `php <script> help` lists this class's commands.

Reference manual: [DuckPhp\DuckPhpAllInOne](../reference/DuckPhpAllInOne.md); tests in `tests/DuckPhpAllInOneTest.php` (they assert that `cmd` contains its own class, and that `Show([], 'index')` outputs contain `<html>` and `main page work at`).

## Common patterns

**1. Add just one or two pages to a legacy project** — the stance of `demo/public/helloworld.php`: drop one php file into the old project's `public/`, point `namespace_controller` at the root namespace, and write the controller class in that same file. The old project keeps its own loading mechanism; the two do not interfere.

**2. Routing only, nothing else** — the stance of `demo/public/just-route.php`: not even the [`DuckPhp`](../reference/DuckPhp.md) app class is needed, just [`Route::RunQuickly($options)`](../reference/Core-Route.md):

```php
use DuckPhp\Core\Route;

class MainController
{
    public function index() { echo "Just route test done"; }
    public function i()      { phpinfo(); }
}
$options = ['namespace_controller' => '\\'];
$flag = Route::RunQuickly($options);
if (!$flag) {
    header(404, 'no');
    echo "404!";
}
```

Only one class is used, [DuckPhp\Core\Route](../reference/Core-Route.md): route resolution, controller dispatch and returning `false` for a 404 are all its job. Good for "I only want to borrow the router".

**3. All-functions mode plus externalised view data** — the stance of `demo/public/traditional.php`: actions are `action_*` functions rather than class methods, via the [`Ext\RouteHookFunctionRoute`](../reference/Ext-RouteHookFunctionRoute.md) extension; and views do not go through the [View](../reference/Core-View.md) component either — [`Ext\EmptyView`](../reference/Ext-EmptyView.md) stores the data, and the **native PHP template** at the end of the file `extract()`s and renders it itself:

```php
$options['namespace'] = '\\';
$options['path_info_compact_enable'] = true;
$options['ext'][\DuckPhp\Ext\EmptyView::class] = true;            // _Show only stores the data
$options['ext'][\DuckPhp\Ext\RouteHookFunctionRoute::class] = true; // an action_* function is an action
$flag = \DuckPhp\DuckPhp::RunQuickly($options);
$xxx = \DuckPhp\Core\View::_()->getViewData();   // take the data out and render it yourself
extract($xxx);
```

This is the extreme form of "drop it into a traditional PHP page": the framework only handles routing and input, and rendering goes entirely back to the page's own HTML.

**4. A whole micro-application in one class** — extend `DuckPhpAllInOne`:

```php
class Tiny extends \DuckPhp\DuckPhpAllInOne
{
    public $options = [
        'path' => __DIR__,
        'is_debug' => false,
    ];
    public function action_hello() { $this->_Show(['m' => 'Hi'], 'hello'); }
    public function view_hello($data) { echo 'hello ' . __h($data['m']); }
}
Tiny::RunQuickly([]);
```

Visiting `/…/tiny.php/hello` calls `action_hello`, and `_Show` automatically wraps the built-in `view_header` / `view_footer` (unless you turn `duckphp_all_in_one_wrap_header_footer` off). The "post-login view" ([Chapter 2-19](user.md)) is enabled by the view data `__use_logined_view_data`, which in a subclass is just `Helper::assignViewData('__use_logined_view_data', true)`.

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| `Class 'DuckPhp\...' not found` | No autoload was registered | With Composer, `require vendor/autoload.php`; without it, `require <framework>/autoload.php` (or write the two lines yourself as `bin/duckphp` does) |
| The framework is found but project classes are not | Only `DuckPhpSystemAutoLoader` was registered, and it only handles the `DuckPhp\` prefix | Add `AutoLoader::RunQuickly(['path'=>…])` + `addPsr4('ProjectNamespace\\', 'src')` (see `demo/cli.php`) |
| A blank page after `RunQuickly()`, no route matched | The controller namespace is wrong | When controllers live in the root namespace, pass `'namespace_controller' => "\\"` (that is exactly what the comment on that line of `helloworld.php` is for) |
| A `DuckPhpAllInOne` subclass's `action_xxx` does not fire | The method name lacks the `action_` prefix | `embedMe()` injects `'controller_method_prefix' => 'action_'`, so methods must start with it |
| `_Show()` does not wrap the header/footer | `duckphp_all_in_one_wrap_header_footer` was turned off, or `view_header`/`view_footer` are undefined | It only wraps when that option is true and the subclass defines the matching `view_*` methods; leave it off if you do not want them |
| After embedding in a legacy project, the old project's classes are loaded first by the framework's autoloader | Both sides registered an autoloader, and the order is uncontrollable | Probe with `class_exists()` (as `demo/cli.php` does), or in the embedded file only `require` the framework's `autoload.php` and leave the project-side loading alone |

## Next steps

- [Chapter 4-5 Long-running processes and the embedded HTTP server](http-server.md): wrap the single file from here in the built-in server.
- [Chapter 4-6 Multiple entry points, domains and SAPIs](multi-entry.md): above the single-file stance, how one `src/` is reused by several entries.
- Reference manual: [DuckPhp\Core\AutoLoader](../reference/Core-AutoLoader.md), [DuckPhp\DuckPhpAllInOne](../reference/DuckPhpAllInOne.md), [DuckPhp\Core\Route](../reference/Core-Route.md)
