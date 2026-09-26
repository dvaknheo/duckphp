# 3-1 The application tree and phase basics

> What this solves: how to run **several applications** in one process (a main app plus mounted child apps) and how their singletons, routes and views avoid interfering with each other.
> Prerequisites: [Chapter 2-1 The four-layer architecture](layers.md), [Chapter 2-2 The request lifecycle](lifecycle.md). About 15 minutes.
> Every piece of code in this volume comes from `tests/data_for_tests/ZThirdDemo`; you can really run it with `wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` (**from the repository root**).

## Mental model: an application tree plus phases

```
MainApp                    phase ''          <- the root app (Root)
└── ThirdApp (name=shop)   phase ':shop'     <- a child app
```

**A phase is an instance space.** `::_()` returns the instance in the **current phase**; switching phases swaps an entire set of singletons:

```
MainApp's phase        child app's phase ':shop'
App::_()      ->MainApp      App::_()      ->ThirdApp
Route::_()    ->the main app's route     Route::_()    ->the child app's route
ShopBusiness::_() ->the main app's copy  ShopBusiness::_() ->the child app's copy
```

Two rules you must remember:

1. **It only affects `::_()`.** Objects produced by `new SomeClass()` are not affected by phases.
2. **A phase name is not a class name.** The root phase is the empty string `''`; a child phase is `<parent phase>:<child app name>`, so a child app with `name => 'shop'` has the phase name **`:shop`**, and one level deeper it is `:shop:another`. When `name` is unset the child app's `namespace` is used; when it is `'@'` the class-name basename is used.

## Minimal example

The main app declares the child app (`ZThirdDemo/src/System/MainApp.php`):

```php
class MainApp extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'namespace' => 'ZThirdDemo',
        'app' => [
            ThirdApp::class => [
                'name' => 'shop',                   // -> phase ':shop'
                'controller_url_prefix' => 'shop/', // -> URL /shop/...
            ],
        ],
    ];
}
```

What you observe:

```php
$app = MainApp::_(new MainApp())->init(['path' => $path]);
App::Phase();                                              // ''      <- currently in the root phase
App::_()->options['app'][ThirdApp::class]['__phase__'];     // ':shop' <- the child app's phase name after registration
App::_()->options['app'][ThirdApp::class]['controller_url_prefix']; // 'shop/'
```

> Once a child app finishes initialising, the framework writes its phase name back into `options['app'][<class>]['__phase__']`; that is the **proper way** to get a child app's phase — do not build the string yourself.

## The phase API at a glance

| What you want           | How to write it                                                    |
| -------------- | ------------------------------------------------------ |
| Read the current phase          | [`App::Phase()`](../reference/Core-App.md)                                         |
| Switch to a child app (and get it)    | `App::_()->toThisChild(ThirdApp::class)` (returns `null` when it does not exist) |
| Switch to a child phase by class         | `App::_()->toChildPhase(ThirdApp::class)` (returns bool)     |
| Switch back from a child app to its parent        | `ThirdApp::FromCurrentParent()` (returns `null` when not in a child phase)       |
| Fetch the root instance and switch back to root       | `App::Root(true)`; use `App::Root()` to fetch without switching               |
| Reset the root phase name         | `App::SwitchRootPhase($phase)`                         |
| The current app's phase name       | `App::_()->getThisPhaseName()`                         |
| The current app's CLI command prefix | `App::_()->getThisCommandPrefix()` (`/` in the phase name becomes `-`)   |
| See what is in the container       | [`PhaseContainer::_()->dumpAllObject()`](../reference/Core-PhaseContainer.md)                 |

## Declaring a child app: the `app` option

The value of `app` is `[child app class => options array]`. **Key point: that array is passed to the child app's `init()` verbatim**, so the parent app can tune it at this level "without touching the child app's code" (Chapter 3-5 relies on exactly that for overrides):

```php
ThirdApp::class => [
    'name' => 'shop',                     // phase name
    'controller_url_prefix' => 'shop/',   // URL prefix (concatenated with the parent app's prefix)
    'controller_resource_prefix' => 'res/',// resource prefix (Chapter 3-3)
    'local_database' => true,             // give it an independent database connection
    'database_list' => [['dsn' => 'sqlite:' . __DIR__ . '/shop.db']],
    'controller_class_map' => [           // replace its controller implementation (Chapter 3-5)
        ThirdMainController::class => MyController::class,
    ],
    'ext' => [JsonView::class => true],   // add an extension just for it
],
```

Two conveniences in the syntax:

- **Shorthand (mix mode, on by default)**: `'blog' => ['class' => BlogApp::class]` treats the key `'blog'` as the URL prefix.
- **Disabling a child app**: give the value `false` (setting `options['app'][class]` to `false` at runtime works too; routing and menu merging both skip it).

## A child app's directories and namespace

A child app is **a complete DuckPHP application**, usually with its own `path` and `namespace`, and it can live in any directory (a third-party package under `vendor/`, or `third/` inside the project):

```
ZThirdDemo/
├── src/…            <- the main app (namespace ZThirdDemo\…, path = project root)
├── view/ config/ res/
└── third/           <- the child app (namespace ZThirdDemo\Third\…, path = third/)
    ├── System/ThirdApp.php
    ├── Controller/ Business/
    ├── view/ config/ res/
```

Its classes have to be autoloadable. When a third-party app lives inside your project, add a mapping for the child app in addition to the main namespace (`ZThirdDemoTest.php`):

```php
AutoLoader::_()->init(['path' => $path . 'src/', 'namespace' => 'ZThirdDemo', 'path_namespace' => ''])->run();
AutoLoader::_()->assignPathNamespace($path . 'third/', 'ZThirdDemo\Third');
```

> With a Composer package this step is unnecessary: the package's `autoload` has already registered its namespace.

## Lifecycle: when a child app initialises

```
MainApp::init()
  ├── onPrepare()
  ├── initComponents()        <- the main app's own components/extensions
  ├── onInit()                <- the main app's "own business" is done
  ├── initChildren(options['app'])  <- init() each child app (each one runs the same flow internally)
  └── onInited()              <- everything is ready; child apps are safe to touch
```

So: **touch child apps only after `onInited()`**. Accessing a child app inside `onInit()` gets you an instance that has not been init'ed yet.

## Common patterns

```php
// 1) switch to a child app temporarily, then switch back
$phase = App::Phase();
App::_()->toThisChild(ThirdApp::class);
$name = ThirdApp::_()->options['shop_name'];   // an instance inside the child app's phase
App::Phase($phase);                            // always switch back

// 2) inside a child app, get the root app (not "the current app")
$root = App::Root();           // instance only
$root = App::Root(true);       // instance plus switch the current phase back to root

// 3) inside a child app, learn "who am I"
$name = App::_()->getThisPhaseName();     // e.g. ':shop'
$cmd_prefix = App::_()->getThisCommandPrefix();  // the CLI command prefix

// 4) CLI: a child app's commands carry the prefix ('/' in the phase name becomes '-')
//   php cli.php shop-<command>     <- command groups are separated by phase, see Chapter 2-16
```

## Common errors

| Symptom                                                     | Cause                                | Fix                                                                             |
| ------------------------------------------------------ | --------------------------------- | ------------------------------------------------------------------------------ |
| Behaviour is unchanged after `App::Phase('ZThirdDemo\Third\System\ThirdApp')` | A phase name is not a class name                           | Use `App::_()->toThisChild(ThirdApp::class)`, or read `options['app'][class]['__phase__']` |
| All the code afterwards runs inside the child app                                        | You switched phases and never switched back                          | Remember `$phase = App::Phase();` … `App::Phase($phase);`                            |
| `App::_()` inside a child app is not the main app                                  | It is **the current (child) app**                    | Use `App::Root()` for the root app                                                            |
| `Call to undefined method ...::getOverridingClass()`   | That was an early API                       | Use `getThisClassName()` / `getThisPhaseName()`                                  |
| The child app 404s, or the route prefix is doubled                                         | `controller_url_prefix` has a `/` too many or too few | Write the child prefix as `'shop/'` (trailing slash) so URLs are `/shop/` and `/shop/list`                          |
| The child app's classes cannot be found                                               | No autoload mapping was added for its namespace                   | See `assignPathNamespace()` above (a Composer package brings its own)                                    |

## FAQ

- **Can a child app have child apps?** Yes, just keep writing them under `app`; the phase name becomes `:shop:sub`.
- **Can views be shared?** By default each app uses its own `path_view`; but a parent app can **override a child app's views by creating a subdirectory named after the child app** in its own view directory (Chapter 3-5).
- **How do I read the main app's config?** `App::Root()->options`, `App::_()->getProjectPath()` (see the [reference manual Core-App](../reference/Core-App.md)).
- **Who handles a child app's exceptions?** The exception manager handles them all (Chapter 2-12); a child app does not catch its own.

## Next steps

- [Chapter 3-2 Mounting an external application](mount-app.md): applying the mechanism above to "a ready-made app you did not write".
- Reference manual: [DuckPhp\Core\App](../reference/Core-App.md), [DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md), [DuckPhp\Core\PhaseContainer](../reference/Core-PhaseContainer.md)
