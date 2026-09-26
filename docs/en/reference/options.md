# Application options overview

> This is the home page of the "application options" feature: first where options **come from** and **who overrides whom**, then the two indexes.
> By class: [Application options (by class)](options-by-class.md) · By name: [Application options (A-Z index)](options-index.md) · The other configuration set: [Application settings (Setting)](setting.md)

## In one sentence

`$options` is the framework's **only configuration entrance**: whether it is written in the application class's `$options` property, in `init($options)`, in the `ext` table, or changed dynamically from the command line, it all merges into the current App instance's `$options` (`App::$options`).

## Where options come from (the merge chain)

```php
// 1) on construction: the "declared defaults" are combined first (the later one wins)
//    App::__construct()
$this->options = array_replace_recursive($this->kernel_options, $this->core_options, $this->common_options, $this->options);

// 2) on initialisation: the options the caller passed are merged in (highest priority)
//    KernelTrait::initOptions()
$this->options = array_replace_recursive($this->options, $options);
```

| Order | Source | Written by | Note |
| --- | --- | --- | --- |
| 1 | `KernelTrait::$kernel_options` | the framework | the application skeleton: `path`/`namespace`/`app`/`cmd`/`ext`/`cli_enable`/`on_*` |
| 2 | `App::$core_options` | the framework | core: `path_runtime`, `path_config`, `setting*`, `error_*`, `exception_map` |
| 3 | `DuckPhp::$common_options` | the framework | entry-class defaults: the default `ext` component table, `*_provider`, `lang_*`, `local_database` and so on |
| 4 | **the subclass's `$options` property** | your project | your application class writes here; the same key overrides the three layers above |
| 5 | the **array** options given in the `ext` table | your project | each component merges them when it is initialised |
| 6 | `init($options)` | your project | **merged last, highest priority** (`initOptions()` applies no whitelist) |
| — | the setting file / `.env` | the environment | not part of `$options`; read with `Setting()`: see [Application settings](setting.md) |
| — | the data file (`ExtOptionsLoader`) | runtime | runtime-changeable options land in `runtime/DuckPhpData.config.json` |

> ⚠️ **Components have a whitelist, App does not**: a component (`ComponentBase` subclass) applies `array_intersect_key($this->options, $options)` in `init()` — **a key the component does not have in its own `$options` is silently dropped**; `App` goes through `KernelTrait::initOptions()` instead, where any key may come in (which is why "implicit options" work on App).

## Configuration layers cheat sheet

<!-- GEN:layers start -->
| Layer | Content |
|---|---|
| `DuckPhp\Core\KernelTrait::$kernel_options` | The application skeleton: path/namespace/app/cmd/ext/cli_enable/on_* and so on |
| `DuckPhp\Core\App::$core_options` | Core: path_runtime, path_config, setting*, error_*, exception_map… |
| `DuckPhp::$common_options` | Entry-class defaults: the default `ext` component table, provider, lang_*, data_file_*… |
| Each component's own `$options` | A component merges with its own whitelist when it is init'ed; see [by class](options-by-class.md) |
| Extensions mounted by the `ext` table | `class => true/array/'option key'/EXT_* constant`; a string value is an **option key name** |
| `init($options)` at runtime | Merged last, **highest priority** (`KernelTrait::initOptions()` is a plain `array_replace_recursive`) |
| Setting file / `.env` | Not part of `$options`; read with `Setting()`, see [Application settings](setting.md) |
| The data file (`ExtOptionsLoader`) | Runtime-changeable options land in `runtime/DuckPhpData.config.json` |
<!-- GEN:layers end -->

## Hidden options

Keys the framework reads but **deliberately keeps out of every `$options`**. They can be used like ordinary options (written in the application class's `$options`, or given in `init($options)`); they simply are not part of the formal list above, because they are "framework-internal switches" or "hooks for external tools" that should not be mistaken for ordinary configuration.

<!-- GEN:hidden start -->
| Option | Default | Where it is read | Description |
|---|---|---|---|
| `not_empty` | `true` | DuckPhp::$common_options | Declared among the default options but read nowhere in the source (a historical leftover; ignore it). |
| `url_admin_home` | `null` | GlobalAdmin\Admin::urlForHome() | The "application-level" override of the back-office home URL: it wins over the component's `globaladmin_url_home`. |
| `url_user_home` | `null` | GlobalUser\User::urlForHome() | The "application-level" override of the in-site home URL: it wins over the component's `globaluser_url_home`. |
| `session_prefix` | `''` | Foundation\Controller\SessionTrait | The session-name prefix (the root application's setting goes through here too). |
| `table_prefix` | `''` | Ext\SqlDumper / Ext\RouteHookWebInstaller | The database table-name prefix; the `{prefix}` placeholder stands for it when SQL is exported/installed. |
| `exception_for_business` | `\Exception::class` | CoreHelper::_BusinessThrowOn() | The exception class used when `BusinessThrowOn()` does not name one explicitly. |
| `exception_for_controller` | `\Exception::class` | CoreHelper::_ControllerThrowOn() | The exception class used when `ControllerThrowOn()` does not name one explicitly. |
| `duckphp_all_in_one_wrap_header_footer` | `false` | DuckPhpAllInOne::onInited() | Whether the AllInOne entry wraps `_Show()` in header/footer views (that class sets it true itself). |
| `permission_menu_tree_for_admin` | `null` | Ext\PermissionMenu::getMenuJsonFileConfig() | The configuration file of the back-office permission menu tree (relative to `path_config`). |
| `duckcoverage_test_lister` | `null` | external package dvaknheo/duckcoverage | Used with that composer package for coverage tests; the framework itself never reads it. |
| `background` | `false` | HttpServer::run*() | Whether the built-in server runs in the background; the CLI switch `-b/--background` sets it true. |
<!-- GEN:hidden end -->

Conventions:

- for the complete machine-readable list: `php docs/scripts/gen-options-docs.php --json`, or the human-readable `python3 docs/scripts/scan-options.py`;
- the scanner cross-checks whether the defaults in the hidden table agree with the source, whether anything really reads the key, and whether the construction flow empties it;
- the `// @used-by <package>` line in the hidden table means **the entry is read by an external package** (this repository has no read site for it, so the scanner no longer warns).

## Two dynamic channels (so an "option list" can never be exhaustive)

1. **Callback keys looked up dynamically**: `run_callback_by_key($key)` in `GlobalAdmin` / `GlobalUser` reads `$this->options[$key]`, and the key names come from their own internal constants (such as `admin_callback_for_login_service`).
2. **A string in the `ext` table is an option key name**: in `initExtensionsByOptions()`, when a component's value in the `ext` table is written as a **string**, that string is taken as an **option key name** and `$this->options[...]` is read. For example, in `DuckPhp::$common_options`

   ```php
   RouteHookPathInfoCompat::class => 'path_info_compact_enable',
   ```

   so `path_info_compact_enable` (declared by `RouteHookPathInfoCompat::$options` itself) becomes "this extension's switch".

## Extensions and the data file

The value in the `ext` table may be written in four ways (`KernelTrait::initExtensionsByOptions()`):

| Form | Meaning |
|---|---|
| `true` | initialise the default way (following the App options) |
| `false` / `null` / `EXT_DISABLE(0)` | do not load |
| an array | initialise that component with this array (the component whitelist still applies) |
| a `'option key name'` string | take `$options[option key name]` and decide from the value found |
| `EXT_SKIP_INIT(-1)` / `EXT_DEFAULT(1)` / `EXT_FOLLOW_APP(2)` / `EXT_RENEW(3)` | take the instance without initialising / initialise by default / follow the App options / rebuild on every request (a dynamic component) |

**The data file** (`ExtOptionsLoader`, the `data_file_*` options): it writes "runtime-changeable options" into a JSON file under `path_runtime` and overrides them back on the next start; the command line `php xx debug` changes `is_debug` in it. The file also carries two fields the framework writes itself: `__class__` (the options' originating class) and `__date__` (when it was written) — both are internal mechanics, never write them by hand.

## Common recipes

- **Differentiating several child applications**: `'app' => [AppA::class => ['name' => 'a', 'controller_url_prefix' => 'a']]`; a child application's options are merged independently in `initChildren()` and do not affect the others. The mixed form also supports `['class' => AppA::class]` (the table key becomes the `controller_url_prefix`).
- **Per-Phase override files**: `getOverrideableFile()` walks back from the current Phase layer by layer looking for a file — the same `config/x.php` can have an override copy for a child application.
- **Three debug switches**: the option `is_debug`, the setting `duckphp_is_debug` (the two are OR-ed, see `App::IsDebug()`), and `is_debug` in the data file (changeable from the command line).
- **Changing an option temporarily**: `App::_()->options['some_key'] = value;` affects the current instance only; to persist it, write the setting file or the data file.
- **CLI arguments**: things like `php xx run --port=8080` are **command-line arguments** (`Console::getCliParameters()`), not app options — do not mix the two.

## Related links

- [Application options (by class)](options-by-class.md) —— "which configuration does a class support"
- [Application options (A-Z index)](options-index.md) —— "what does this key mean", quickly
- [Application settings (Setting)](setting.md) —— "environment data" such as database passwords and the debug/maintenance switches
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) —— where `kernel_options` and `initOptions()` live
- [DuckPhp\Core\App](Core-App.md) —— `core_options` and the setting-file loading
- [DuckPhp\DuckPhp](DuckPhp.md) —— `common_options` and the hidden option table
