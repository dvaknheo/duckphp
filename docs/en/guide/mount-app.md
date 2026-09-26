# 3-2 Mounting an external application

> What this solves: you have a DuckPHP application **you did not write** (a colleague's module, an old project, a package in `vendor/`) and want to mount it into the current project without touching it.
> Prerequisites: [Chapter 3-1 The application tree and phase basics](advanced-phase.md). About 15 minutes.
> Examples: `tests/data_for_tests/ZThirdDemo` (a main app plus the third-party app under `third/`); running `tests/ZThirdDemoTest.php` verifies it.

## Three steps

### Step 1: make its classes loadable

| What it is | What to do |
|---|---|
| A Composer package | Nothing — the package's `autoload` is already registered |
| A directory in your project (e.g. `third/`) | Add a namespace mapping |

```php
// in the main app's entry file or a test
AutoLoader::_()->init(['path' => $path . 'src/', 'namespace' => 'ZThirdDemo', 'path_namespace' => ''])->run();
AutoLoader::_()->assignPathNamespace($path . 'third/', 'ZThirdDemo\Third');
```

> Note that the mapping's **base directory must line up with that app's `path`**: `ZThirdDemo\Third\System\ThirdApp` has to be findable under `third/`, because the controller scan path is derived from "the directory the app class file lives in".

### Step 2: register it in the `app` option

```php
class MainApp extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'namespace' => 'ZThirdDemo',
        'app' => [
            ThirdApp::class => [
                'name' => 'shop',                    // phase name -> ':shop'
                'controller_url_prefix' => 'shop/',  // URL -> /shop/
            ],
        ],
    ];
}
```

At this point it is mounted: `GET /shop/` is handled by it, `GET /` still belongs to the main app.

### Step 3: check the assumptions it ships with

| What to check                    | Why                                                         | What to do                                           |
| ----------------------- | ----------------------------------------------------------- | --------------------------------------------- |
| `path` / `namespace`    | They decide where its views, config and controllers come from | Nothing to do if it declares them; otherwise fill them in under `app` |
| Is its welcome controller `Main`?        | Its home page is `/shop/` only when the welcome class is `Main`, otherwise `/shop/Xxx/index` | Keep the default, or use `controller_welcome_class` |
| `controller_url_prefix` | A missing trailing slash builds `/shopXxx/index`                                 | Write `'shop/'`                                   |
| Resource directory                    | Its `res/` must be reachable over a URL                                       | Configure `controller_resource_prefix` (Chapter 3-3)        |
| Database                     | Should it use an **independent** connection?                                              | Inject `local_database => true` plus `database_list` |
| CLI commands                  | Its commands carry the phase prefix                                                  | `php cli.php shop-<command>` (Chapter 2-16)               |
|                         |                                                             |                                               |

## It is really "a complete application"

A mounted app keeps everything of its own:

```
third/                        <- its own root (path)
├── System/ThirdApp.php       <- its entry class, extending DuckPhp
├── Controller/MainController.php
├── Business/ShopBusiness.php
├── view/                     <- its views (the main app can override per phase, see Chapter 3-5)
├── config/                   <- its config (same)
└── res/                      <- its resources (same)
```

```php
namespace ZThirdDemo\Third\System;

class ThirdApp extends \DuckPhp\DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../',
        'namespace' => 'ZThirdDemo\Third',
        'controller_resource_prefix' => 'res/',
        'shop_name' => 'Third Party Shop',
    ];
}
```

**Do not edit its files just to mount it**: inject what you need through the `app` options (the table in step 2), and change behaviour through overrides (Chapter 3-5). That way the next upgrade is still a straight directory swap.

## Several entry points sharing one code base

The main app and the child app share the same entry files; an entry only "initialises and runs":

```php
// public/index.php -- the web entry
AutoLoader::_()->init([...])->run();
MainApp::RunQuickly([]);

// bin/cli.php -- the CLI entry (same code base)
MainApp::RunQuickly(['cli_enable' => true]);
```

So mounting a back-office app needs neither a second entry point nor a second domain.

## Verification checklist

```
GET /              -> the main app's home page
GET /shop/         -> the third-party app's home page (its own view, unless overridden)
GET /shop/native   -> one of its other actions
GET /shop/res/*.css-> its static resources
GET /legacy-shop   -> a rewrite rule written by the main app points at it (Chapter 3-5)
```

`ZThirdDemoTest` asserts exactly this checklist, so you can copy it as a smoke test for your own project.

## Common errors

| Symptom                               | Cause                                | Fix                                                  |
| -------------------------------- | --------------------------------- | --------------------------------------------------- |
| The child app 404s although every class is there | The route prefix does not match                           | The prefix needs a trailing slash; check that `path` points at its root directory |
| The class found is the main app's same-named class | Both apps use one namespace                      | Give every app its own namespace (phases isolate **instances**, not classes) |
| Its view render reports "variable does not exist" | Its views depend on its own `Helper`/data            | Do not copy just the view files; either mount the whole directory or "override without moving" per Chapter 3-5 |
| `php cli.php help` does not list its commands | Commands get the phase prefix                         | Use `php cli.php shop-help`, or read the command-prefix rules in Chapter 2-16 |
| Its resources 404 in production                      | Production serves statically, so `res/` must be deployed to the docroot | Use [`RouteHookResource::_()->cloneResource()`](../reference/Component-RouteHookResource.md) (Chapter 3-3) |
|                                  |                                   |                                                     |

## Next steps

- [Chapter 3-3 Static resources and the document root](static-resources.md)
- [Chapter 3-5 Overriding and replacement](overriding.md): swap its views/config/controllers without editing its files
