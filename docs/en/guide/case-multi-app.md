# 3-7 Capstone: front end + back office + API

> Goal: assemble the mechanisms of volume 3 into a real architecture — three applications in one process, resource sharing made explicit, boundaries drawn clearly, and a third-party back office retrofitted without changing a single line of its code.
> Prerequisites: chapters 3-1 to 3-6 of this volume. About 40 minutes (copy the skeleton and rename things).
> Starting point: `tests/data_for_tests/ZThirdDemo` (the two-application version, already verified); this chapter grows it into three applications.

## The target architecture

```
                     URL                      phase      resource prefix
MainApp (front end)   /                        ''         /res/
├── AdminApp          /admin/…                 ':admin'    /admin/res/
└── API ApiApp        /api/…                   ':api'      /api/res/
```

```
                     front end   back office   API
views (view/)        yes         yes           none (JsonView)
database             shared DB   own DB        shared DB (read-only connection)
Redis                shared      shared        shared
event bus            listens     broadcasts    broadcasts
CLI commands         default set admin-*       api-*
override list        —           overrides its views/config/controllers   —
```

## Putting it together

### 1. Directories and namespaces

```
project/
├── public/index.php                 ← all three applications share this one entry
├── bin/cli.php
├── src/{Controller,Business,Model,System}/     ← front end (namespace MyProj\…)
├── view/ config/ res/
├── admin/                            ← back office (namespace MyProj\Admin\…, path=admin/)
│   └── {System,Controller,Business,Model,view,config,res}/
└── api/                              ← API (namespace MyProj\Api\…, path=api/)
    └── {System,Controller,Business,Model,config}/
```

Autoloading (add two mappings when mounting applications inside the project; a Composer package carries them itself):

```php
AutoLoader::_()->init(['path' => __DIR__ . '/../src/', 'namespace' => 'MyProj', 'path_namespace' => ''])->run();
AutoLoader::_()->assignPathNamespace(__DIR__ . '/../admin/', 'MyProj\Admin');
AutoLoader::_()->assignPathNamespace(__DIR__ . '/../api/', 'MyProj\Api');
```

### 2. The main application declares two child applications

```php
class MainApp extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'namespace' => 'MyProj',

        'controller_url_prefix' => '',
        'controller_resource_prefix' => '/res/',      // the slash rule from Chapter 3-3

        'installed' => false,                         // Chapter 3-6
        'url_install' => 'install',

        'ext' => [
            GlobalEvent::class => true,               // the event bus is off by default (Chapter 3-4)
            PermissionMenu::class => true,            // back-office menu (Chapter 2-11)
        ],

        'app' => [
            AdminApp::class => [
                'name' => 'admin',
                'controller_url_prefix' => 'admin/',
                'controller_resource_prefix' => 'res/',
                'local_database' => true,                       // the back office uses its own database
                'database_list' => [['dsn' => 'sqlite:' . __DIR__ . '/../../runtime/admin.db']],
                'controller_class_map' => [                     // Chapter 3-5: swap out its controller implementation
                    \MyProj\Admin\Controller\MainController::class => \MyProj\Override\AdminMainController::class,
                ],
            ],
            ApiApp::class => [
                'name' => 'api',
                'controller_url_prefix' => 'api/',
                'controller_resource_prefix' => 'res/',
                'ext' => [JsonView::class => true],             // the API only emits JSON (Chapter 2-6)
            ],
        ],
    ];

    protected function onInited(): void
    {
        parent::onInited();
        // an order placed in the back office must reach the front end (Chapter 3-4)
        GlobalEvent::_()->globalOn('admin.order.paid', '', function ($order_id) {
            // record the transaction, send a message, clear caches…
        });
    }
}
```

### 3. The sharing decision table (decide from the table, not on a whim)

| Resource | Front end | Back office | API | How it is done |
|---|---|---|---|---|
| Code (framework and shared libraries) | shared | shared | shared | the same vendor |
| Database connection | one shared | its own | one shared | the back office adds `local_database => true` |
| Redis | shared | shared | shared | nothing to do ([`RedisManager`](../reference/Component-RedisManager.md) is a shared component) |
| Logs | shared | shared | shared | shared by default; use separate `path_log` to split them |
| Language/messages | its own | its own | its own | its own by default (measured in Chapter 3-4) |
| Routes/views/config | its own | its own | its own | the default behaviour |

### 4. The override list (kept in one place, not scattered)

```php
// MyProj/System/Overrides.php is a list for humans only; it takes no part in runtime
// 1) back-office home view      view/admin/index.php            ← overrides the admin app's view/index.php
// 2) back-office payment config config/admin/pay.php             ← overrides admin/config/pay.php
// 3) back-office logo asset     res/admin/logo.png               ← overrides admin/res/logo.png
// 4) the index method of the back-office main controller: src/Override/AdminMainController.php
// 5) rewrite the old /backend address to the back-office home page
RouteHookRewrite::_()->assignRewrite('/backend', 'admin/');   // the key must keep its leading '/'
```

### 5. Permissions and menus

```php
// back-office controllers must implement the marker interface for the permission menu and admin views to recognise them
class AdminMainController extends Base implements \DuckPhp\GlobalAdmin\AdminControllerInterface
{
    /** @menu_directory Admin\Order */
    public function index() { /* … */ }
}
```

```php
// build the menu (Chapter 2-11 + Ext-PermissionMenu)
PermissionMenu::_()->buildAndSaveToConfigJsonFile();   // once: scan the comments into a menu config
$tree = PermissionMenu::_()->loadAll();                // every request: merges the menus of all child applications
$side = PermissionMenu::_()->permissionMenuTreeToSideMenuTree($tree);
```

### 6. Installation and going live

```php
// the front-end controller guards the installation state
Helper::checkInstall();
```

- Once `installed=true`, either turn the installation entry point off or put authentication in front of it (Chapter 3-6).
- Serve assets directly in production: [`RouteHookResource::_()->cloneResource()`](../reference/Component-RouteHookResource.md), or a build step `cp -r res/* public/`.
- Point the document root at `public/`; the `admin/` and `api/` source directories must not sit under the web root.

### 7. CLI commands grouped by application

```bash
php bin/cli.php help            # front-end and shared commands
php bin/cli.php admin-help      # commands of the back-office application (phase-name prefix, Chapter 2-16)
php bin/cli.php api-help        # commands of the API application
```

## Smoke testing: copy this pattern

The "one init + many requests" pattern used by `ZThirdDemoTest` is the least effort — take it and swap in your own list:

```php
private function request(string $path_info): string
{
    $_SERVER['PATH_INFO'] = $path_info;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = [];
    ob_start();
    MainApp::_()->serve();          // the main application hands the request to a child application
    return (string) ob_get_clean();
}

// the list (each line is one assertion)
// GET /                 front-end home page
// GET /admin/           back-office home page (should be the overridden view)
// GET /admin/res/logo.png
// GET /api/ping         JSON
// GET /backend          the rewrite takes effect
// event: an order is placed in the back office → the front-end listener receives it
```

To verify the HTTP layer as well (real headers and static files), use the `ZAllDemoTest` set-up: start the built-in server and `curl` each route, comparing output (Chapter 4-7, landed in M4).

## Troubleshooting cheat sheet

| Symptom | Check here first |
|---|---|
| A child application 404s | Does the prefix keep its trailing slash; are the `path`/namespace mappings right (Chapter 3-2) |
| The view is not the one you expected | `getOverrideableFile('view','index.php',true,true)` shows which one won; is the override directory name equal to the child application's `name` (Chapter 3-5) |
| Assets 404 | The prefix slash rule: `'/res/'` at the root, `'res/'` for a child application (Chapter 3-3) |
| Events never arrive | Is [`GlobalEvent`](../reference/Component-GlobalEvent.md) enabled; in which phase was `fire` registered (Chapter 3-4) |
| The back office connects to the front-end database | `local_database => true` was forgotten (Chapter 3-4) |
| The command is reported as not found | The command belongs to a child application → add the phase prefix (Chapter 2-16) |
| It keeps redirecting to the installation page | `installed` is still `false` (Chapter 3-6) |

## Wrapping up

Write this structure (directories, prefixes, sharing decisions, override list, smoke list) into the project's README so the next person does not have to work it out again; chapters 3-2 to 3-7 of volume 3 are the entire background they need to read.

## Related reference

- [Chapter 3-1](advanced-phase.md) phases · [Chapter 3-2](mount-app.md) mounting · [Chapter 3-3](static-resources.md) assets · [Chapter 3-4](component-sharing.md) sharing and communication · [Chapter 3-5](overriding.md) overriding · [Chapter 3-6](installer.md) installation
- [Ext-PermissionMenu](../reference/Ext-PermissionMenu.md) · [GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md) · [GlobalUser](../reference/GlobalUser-GlobalUser.md)
