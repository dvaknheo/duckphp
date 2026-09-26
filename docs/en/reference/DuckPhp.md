# DuckPhp\DuckPhp

The framework's most commonly used entry class: it extends `DuckPhp\Core\App` and hands you "which common components/options are installed by default" in one go, which makes it the most common starting point for new projects.

## Introduction

`DuckPhp` is a subclass of `DuckPhp\Core\App`. It defines no complex business of its own; instead it:

- declares a set of "framework default" application options with `protected $common_options`: multi-language, the Rewrite/Map/resource/compatibility route hooks are on by default; slots are reserved for the DB prefix, the user/admin views, the database/Redis providers, language, and so on.
- overrides `initComponentsOfRoot` / `initComponentsOfInner` so that, on top of the System components the parent (`App`) installs, the **business components built into the framework** are gradually wired into the current application:
  - at the root-component stage: DbManager/RedisManager/Admin/User/GlobalEvent are all instantiated with `EXT_ROOT_HOLD_POSISION_ONLY` (creating the singleton only, without initialising it automatically); RedisManager/DbManager are initialised conditionally (depending on whether `redis`/`redis_list` or `database`/`database_list` exist); after DbManager is initialised it writes `database_driver` back into the App options.
  - at the inner stage: it registers the default command-line plugin and `Configer`; decides from `local_database` / `local_redis` whether to create a separate DB/Redis for this Phase; hooks up `data_file_enable`; and handles `admin_provider` / `user_provider`.
- provides two instance conveniences, `_Show()` (which dispatches to the user/admin view or the parent's default according to the controller type) and `lang()` (which goes to an optional `lang_handler` or to `DuckPhp\Component\Lang`).

The overwhelming majority of projects only need:

```php
class MyApp extends \DuckPhp\DuckPhp { /* override/merge $options */ }
```

and then `MyApp::RunQuickly([])` is enough to put it to use. Only the few scenarios needing offline or deep control extend `App` directly.

## Class info

- Namespace: `DuckPhp`
- Declaration: `class DuckPhp extends App` (`App = DuckPhp\Core\App`)
- How to adopt it: usually `class YourApp extends \DuckPhp\DuckPhp` gives you all of this, with no manual component assembly.
- Only the `use DuckPhp\…` imports in this file decide how components are assembled; when writing code in a subclass, prefer going through a Helper (`DuckPhp\Foundation\*`/the static shells) rather than depending on the framework namespace directly.

## Options

The table below lists the keys that really take effect in `DuckPhp::$common_options` (historical entries commented out further down the source are not listed):

| Option | Default | Meaning |
|---|---|---|
| `data_file_enable` | `false` | Whether to turn on the "data attributes / external extension options file" mechanism. Once on, the corresponding initialisation stage calls `ExtOptionsLoader` to read extra definitions (such as configuration shared by several applications). |
| `ext` | an array (see below) | The extension-hook map this class enables by default. Its value is the five source `ext` entries: `Lang`, `RouteHookRewrite`, `RouteHookRouteMap`, `RouteHookResource`, `RouteHookPathInfoCompat` (= that switch value). |
| `session_prefix` | `null` | The session key naming prefix (used by the host to avoid collisions/concurrent overwrites across applications). |
| `table_prefix` | `null` | The database table-name prefix (the DB layer uses it to assemble table names where CRUD needs it). |
| `admin_provider` | `''` | A custom administrator provider class name; when not empty it is instantiated in the inner stage and handed to `GlobalAdmin`. |
| `user_provider` | `''` | A custom user provider class name; when not empty it is likewise handed to `GlobalUser` (it may be wrapped in a `PhaseProxy`). |
| `database_driver` | `''` | The database driver label (such as `mysql`). After initialisation the real driver DbManager obtained is written back into this option for the layers above to read. |
| `cli_command_with_common` | `true` | Whether to register the built-in default CLI command set (`DuckPhp\Component\Command`) into the current application's command list. |
| `duckcoverage_test_lister` | `null` | A coverage-test lister used only by this repository/tests; ordinary projects normally leave it unset. |
| `lang_default` | `null` | The default language for multi-language support (shared with the Lang component; the fallback when no further detection is done). |
| `lang_final` | `null` | The final language; once set, no detection happens and it is used as is. |
| `local_database` | `false` | When `true`, this App (including its child app Phases) creates a separate `DbManager` (it does not join the shared bucket `#shared`, so they do not interfere). |
| `local_redis` | `false` | Redis with the same semantics: when true it gets its own `RedisManager`. |

In addition, this class has a set of hidden options (`$hidden_options`) that the framework reads but whose defaults are not merged into `$options`; they exist for tools/documentation only:

| Option | Default | Meaning |
|---|---|---|
| `url_admin_home` | `null` | The administrator back-office home URL (used inside the framework). |
| `url_user_home` | `null` | The user front-end home URL (used inside the framework). |
| `session_prefix` | `''` | The session key prefix. |
| `table_prefix` | `''` | The database table prefix. |
| `exception_for_business` | `\Exception::class` | The business exception base class. |
| `exception_for_controller` | `\Exception::class` | The controller exception base class. |
| `permission_menu_tree_for_admin` | `null` | The administrator permission menu tree (used by the PermissionMenu component). |
| `duckcoverage_test_lister` | `null` | The coverage-test lister. |

## Usage

The traditional entry point:

```php
namespace Demo\System;
use DuckPhp\DuckPhp;

class App extends DuckPhp {
    public $options = [
        'namespace'  => 'Demo',
        'path'       => __DIR__.'/../..',
        'user_provider' => \Demo\UserProvider::class,
    ];
}

\Demo\System\App::RunQuickly([]);
```

Key points:

- `name → namespace` derives the project directory/namespace automatically (checked by the Kernel layer).
- To use your own controller suffix/prefix, override them in `$options` in the same way (such as `controller_method_prefix => 'action_'`).
- The user/admin back ends are off by default — if `user_provider` / `admin_provider` are empty, the corresponding interface is simply not enabled.
- The "logged-in view" (handing rendering over to `GlobalUser`/`GlobalAdmin`) is now switched by **`__use_logined_view_data`**: it takes effect when it is truthy in `_Show()`'s `$data` or in `View::_()->data`, and the framework's `Controller\UserControllerBase`/`AdminControllerBase` assign it automatically with `assignViewData('__use_logined_view_data', true)`. The two options used historically, `use_user_view` / `use_admin_view`, **are no longer read by the source** (only commented lines remain in `$common_options`), so do not write them any more.
- CLI commands <hint>are provided by default; to switch off **just one** of them, use `cli_command_with_common=false`.</hint>

## Configuration example

```php
class MyApp extends \DuckPhp\DuckPhp {
    public $options = [
        'namespace'  => 'Demo',
        'path'       => __DIR__.'/../..',
        'lang_default'   => 'zh_CN',
        'lang_final'     => 'zh_CN',
        'admin_provider' => \Demo\Admin\Provider::class,
        'database_driver'=> 'mysql',
        'local_database' => false,     // share the default Db between applications
    ];
}
```

At runtime they are reachable through the static shells/singletons:

```php
use DuckPhp\DuckPhp;
DuckPhp::Platform();
DuckPhp::Setting('shop_name','demo');
\App::_()->lang('no.result'); // goes to lang_handler when there is one, otherwise to Lang
```

## Caveats

1. Whether a class may be init'ed directly: `App` blocks initialisation of an unextended base in `haltInitInBaseClass`; `DuckPhp` gives those hooks sensible defaults, so it can be used as is.
2. To switch off a default extension: merge `ext => [Lang::class => false, RouteHookRewrite::class => false, …]` (the array layer overrides the defaults).
3. Enabling administrator/user: configure `user_provider` / `admin_provider` (empty means off); the "logged-in view" is triggered by `__use_logined_view_data` (the old `use_admin_view/use_user_view` no longer work).
4. Separate DB/Redis: use `local_database/local_redis` in multi-app or sandbox scenarios, otherwise the root Manager is shared.
5. After configuration the `database_driver` parameter is written back by the framework (so it reads a value instead of being empty).
6. This class has few methods; the framework's body is in `App`/`KernelTrait`; the method list below covers only the hook shells added by `DuckPhp.php` itself.

## All options

Below is the "active keys" part of `protected $common_options` as written in the source (the historical options commented out have been removed from this listing):

```php
    protected $common_options = [
        'data_file_enable' => false,
        'ext' => [
            Lang::class                 => true,
            RouteHookRewrite::class     => true,
            RouteHookRouteMap::class    => true,
            RouteHookResource::class    => true,
            RouteHookPathInfoCompat::class => 'path_info_compact_enable',
        ],
        'session_prefix' => null,
        'table_prefix' => null,
        // 'use_user_view' => true,
        // 'use_admin_view' => true,
        'admin_provider' => '',
        'user_provider' => '',
        'database_driver' => '',
        'cli_command_with_common' => true,
        'duckcoverage_test_lister' => null,
        'lang_default' => null,
        'lang_final' => null,
        'local_database' => false,
        'local_redis' => false,
    ];
```

`$hidden_options` (read by the framework but whose defaults are not merged into `$options`):

```php
    protected $hidden_options = [
        'not_empty' => true,
        'url_admin_home' => null,
        'url_user_home' => null,
        'session_prefix' => '',
        'table_prefix' => '',
        'exception_for_business' => \Exception::class,
        'exception_for_controller' => \Exception::class,
        'permission_menu_tree_for_admin' => null,
        'duckcoverage_test_lister' => null,
    ];
```

## Methods

> Only the methods this class overrides/adds in `DuckPhp.php` are listed. The shells and statics inherited from Core- (App/KernelTrait/Route and so on) are documented on their own pages: `Core-App`, `Core-KernelTrait`.

### Public methods

    public function _Show(array $data, string $view = '')
When the current calling controller is of User/Admin type and `__use_logined_view_data` is on, it takes `__logined_render_header_footer` first, then calls `User::_()->mergeViewData($data)` or `Admin::_()->mergeViewData($data)` to merge the view data; when `__use_logined_header_footer_file` is truthy it also sets the header/footer files; finally it falls back to parent::_Show()

    public function lang($str, $args = [], $fallback = null)
Translation: delegates to the lang_handler callback when there is one; otherwise hands it to Lang(_)::language() (with fallback)

### Protected methods

    protected function initComponentsOfRoot($components, $default): void
After the parent finishes, adds SystemWrapper/Logger/CoreHelper (SKIP_INIT); brings DbManager/RedisManager/Admin/User/GlobalEvent all into the component list with EXT_ROOT_HOLD_POSISION_ONLY; initialises ExtOptionsLoader when data_file_enable is set; initialises RedisManager when redis/redis_list is configured; initialises DbManager when database/database_list is configured (and writes database_driver back into the App options)

    protected function initComponentsOfInner($components, $default): void
On top of the parent's inner loading: brings in ExtOptionsLoader when data_file_enable is set on the child; registers Command/Configer by default; and when local_database/local_redis is true (or the driver does not match) uses createLocalObject for its own Db/Redis (writing database_driver back at the same time)

    protected function initComponentsOfExt($classes, $default): void
Extension loading: it first goes through the parent's `initComponentsByClasseOptions()` to handle the `ext` table, then points GlobalAdmin/GlobalUser at the project's own implementations according to `admin_provider`/`user_provider` (wrapping them with `PhaseProxy::CreatePhaseProxy()` and attaching them to the current Phase)

    protected function haltInitInBaseClass(): void
**The empty body is deliberate**: the parent `App` throws in this hook to block "init'ing the base class App directly"; DuckPhp is a usable entry class, so it overrides the hook with an empty body to let it through

    protected function isLocalDatabase(): bool
Tells whether a separate DB is needed (local_database true, or an explicit database_driver differing from the driver the current Manager derives)

    protected function isLocalRedis(): bool
Returns true only when local_redis=true

## Related links

- [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — the single-file AllInOne entry point
- [DuckPhp\Core\App](Core-App.md) — the parent class (which uses KernelTrait)
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — the init/serve/component-loading framework
- Component pages: `DuckPhp\Component\DbManager` (Component-DbManager.md), `RedisManager`, `Lang`, `Configer` and so on
- guide: [configuration](../guide/configuration.md), [project-structure](../guide/project-structure.md), [quickstart](../guide/quickstart.md)
