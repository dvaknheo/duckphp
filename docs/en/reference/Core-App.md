# DuckPhp\Core\App

DuckPHP's application base class: `use KernelTrait` plus a batch of "system-level" capabilities; the parent class of `DuckPhp` / `DuckPhpAllInOne`.

## Introduction

`App` lives at `DuckPhp\Core\App`: `class App extends ComponentBase { use KernelTrait }`, with a layer of reorganization applied to the externally visible members.

- It renames and then overrides several KernelTrait entry points ("assemble components / run one request"), inserting the framework's static components (`SystemWrapper`, `Logger`, `CoreHelper`, `Route`, `View`, `SuperGlobal`) and settings (supplier→setting) loading into the working skeleton.
- On construction it merges options from kernel / core / user into `public $options` via `array_replace_recursive`, and clears the temporary properties; `core_options` sets a number of process-level / error / settings-file defaults.
- It also provides a set of static/instance conveniences that gateway the framework's public capabilities: `Setting()/version()/Platform()`, the named debug checks `IsDebug/IsHiddenDebug`, error page / maintenance page callbacks, overridable file lookup, simple URL/lang flavors, etc.

A typical project never news up `App` directly; you normally define a `class XApp extends DuckPhp\DuckPhp` or `DuckPhpAllInOne`, whose parent chain ends up using this class's components/settings. Only extend `App` directly when you **assemble the lower layers yourself**.

<br/>(The class source's `require_once __DIR__.'/Functions.php'` is already included in this entry.)

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class App extends ComponentBase`
- Trait used: `KernelTrait`
- Renamed with `as` and then overridden: `initComponents`→`Kernel_initComponents`, `prepareServe`→…, `initComponentsOfRoot`→…, `Inner`, `Dynmic`; it then calls `Kernel_xxx()` inside its own methods to keep the parent skeleton behavior.
- Constants: `VERSION=1.4.1`, `EXT_SKIP_INIT=-1`, `EXT_DISABLE=0`, `EXT_DEFAULT=1`, `EXT_FOLLOW_APP=2`, `EXT_RENEW=3`, `EXT_ROOT_HOLD_POSISION_ONLY=0`.
- Public properties: `$options`, `$setting` (starting from `options['setting']`, merged with the optional `.env` and the settings file), plus the Kernel core-state fields.

## Options

`App::core_options` (defaults) — you can almost always change these; the table lists the keys that actually take effect:

| Option                         | Default                               | Description                                                                                |
| ------------------------------ | ------------------------------------- | ------------------------------------------------------------------------------------------ |
| `path_runtime`                 | `'runtime'`                           | Runtime directory relative to the project root (or absolute). Returned by `getRuntimePath()`. |
| `path_config`                  | `'config'`                            | Config file directory (`getConfigFile()` looks files up based on it + Phase override).     |
| `default_exception_do_log`     | `true`                                | Whether the default exception handler writes a log.                                        |
| `close_resource_at_output`     | `false`                               | Whether to close/reclaim resources uniformly when output ends (off by default).            |
| `html_handler`                 | `null`                                | (reserved / for extensions) arbitrary HTML handler callback.                               |
| `lang_handler`                 | `null`                                | When set, `lang()`/`langText()` prefer it instead of the fallback simple replacement.      |
| `is_maintain`                  | `false`                               | Maintenance flag. When hit, `prepareServe()` renders the maintenance page (`error_maintain`). |
| `skip_404`                     | `false`                               | Skip the 404 display (`skip404Handler()` sets it true).                                    |
| `error_404`                    | `null`                                | Used on 404 (path or callable). null → built-in 404 placeholder / dev info.                |
| `error_500`                    | `null`                                | Default page for exceptions (path or callable). null → detailed under debug, minimal otherwise. |
| `error_debug`                  | `null`                                | Dev-time error view/callable. null → built-in fieldset receipt.                            |
| `error_maintain`               | `null`                                | Maintenance page view/callable. null → built-in "Maintaining.".                            |
| `setting_file`                 | `'config/DuckPhpSettings.config.php'` | Settings file (relative to root or absolute).                                              |
| `setting_file_ignore_exists`   | `true`                                | Whether to ignore a missing settings file (no error thrown).                               |
| `setting_file_enable`          | `true`                                | Whether to load the settings file.                                                         |
| `use_env_file`                 | `false`                               | When true, loadSetting also reads the root `.env` (INI) and merges it.                     |
| `setting`                      | `[]`                                  | Application settings (setting) given directly as an array; the starting point of `loadSetting()`, then `.env` (optional) and the settings file are merged. |
| `installed`                    | `false`                               | Whether the app is "installed" (false triggers an install redirect).                       |
| `url_install`                  | `'install'`                           | Install URL to jump to when not installed.                                                 |
| `exception_map`                | `[]`                                  | Exception class map (`original class => replacement class`); `ProjectThrowOn/BusinessThrowOn/ControllerThrowOn` replace the class name through it before throwing. |

## Usage

In a real project you most often write your own App with `DuckPhp`; if you don't even want the DuckPhp layer, inherit like this:

```php
    namespace Demo\System;
    use DuckPhp\Core\App;

    class App extends App {
    public $options = [
    'namespace' => 'Demo',
    'path'      => __DIR__.'/../..',
    'error_404' => '_sys/error_404',
    ];

    protected function onPrepare(): void { parent::onPrepare(); /* 预整理 */ }
    }
```

Then you can boot with `Demo\System\App::RunQuickly([])` (provided by Kernel) — it runs the Settings-loaded + component-assembly + root routing stages.

### Static convenience entry points in business code / templates

```php
    \App::version();      // (Demo\System\App)1.4.1
    \App::Setting('site_name','');  // read a setting
    \App::_()->getRuntimePath();    // .../runtime/
    \App::IsDebug();                // whether debug
    \App::Platform();               // duckphp_platform (you can see the environment directly)
```

### Loading order summary

- Inside `init()`: the kernel finishes options/phase/exception → `onPrepare()` (root reads `.env`+Setting into `$this->setting`) → `initComponents…` where App inserts the System components → `…` the rest follows the Kernel flow.
- Before `serve()`: in maintenance mode `prepareServe()` outputs the maintenance page first.

## Configuration example

```php
    class App extends \DuckPhp\DuckPhp {
    public $options = [
    'path_runtime'   => 'runtime',
    'setting_file_enable' => true,
    'setting_file'    => 'config/site.config.php',
    'use_env_file'    => true,
    'error_404'       => '_sys/error_404',
    'error_500'       => '_sys/error_500',
    'error_maintain'  => '_sys/maintain',
    'is_maintain'     => false,
    'installed'       => true,
    ];
    }
```

## Caveats

1. Do not modify `$setting` directly at runtime → rely on `Setting()`; change it via the file / override the kernel to keep it consistent.
2. Maintenance (`is_maintain` or `duckphp_is_maintain=true` in Setting) shows you the maintenance page instead of normal business.
3. `haltInitInBaseClass()` throws `DuckPhpSystemException`; **this is deliberate** — it prevents calling `App::_()->init()` on the base `App` directly ([DuckPhp](DuckPhp.md) overrides it with an empty implementation, so DuckPhp can initialize).
4. `getOverrideableFile()` has two uses, distinguished by `$must_exist`: pass `true` to read a file (you get `null` when it does not exist — don't use the return value as an existence check), pass `false` to get a "not-yet-existing path to write to" (returns the last candidate path).
5. With `installed=false` requests 302 to `url_install` (see `checkInstallToPage`); set it to true in production.
6. `_On404/_OnDefaultException/_OnDevErrorHandler` all call `onBeforeOutput()` (when there is a View page), so you can put resource headers etc. in `onBeforeOutput()` ahead of time.
7. `_DEPRECATED` notices are only output under debug+inited; undefined ones re-trigger the dev handler.

## All options

```php
    protected $core_options = [
    'path_runtime' => 'runtime',
    'path_config' => 'config',

    'default_exception_do_log' => true,
    'close_resource_at_output' => false,
    'html_handler' => null,
    'lang_handler' => null,

    'is_maintain' => false,
    'error_404' => null,
    'error_500' => null,
    'error_debug' => null,
    'error_maintain' => null,

    'setting_file' => 'config/DuckPhpSettings.config.php',
    'setting_file_ignore_exists' => true,
    'setting_file_enable' => true,
    'use_env_file' => false,
    'setting' => [],

    'installed' => false,
    'url_install' => 'install',
    'exception_map' => [],
    ];
```

## Methods

This file only covers the methods defined by `class App …` itself in `App.php`; the same-named shells brought in by KernelTrait are documented in [Core-KernelTrait](Core-KernelTrait.md) and not repeated here.

### Public methods

    public function __construct()
Merges kernel/core/common and user options into $options; clears the temporary options container and records this_class

    public function version()
Returns the `(class name)VERSION` version identifier (for debugging/CLI)

    public static function Setting($key = null, $default = null)
Static setting read: delegates to the root app's _Setting

    public function _Setting($key = null, $default = null)
Reads the root setting: with a key → `Root()->setting[$key] ?? default`; without → returns the whole setting

    public static function Platform()
Static shell reading the `duckphp_platform` setting

    public function _Platform()
Instance: returns the duckphp_platform value

    public static function IsDebug()
Static debug check: delegates to __IsDebug()

    public function _IsDebug()
debug = setting(duckphp_is_debug) ∨ root options is_debug ∨ own options is_debug

    public static function IsHiddenDebug()
Static shell for the real debug

    public function _IsHiddenDebug()
Same as `_IsDebug()` by default (upper layers override it when they need to distinguish)

    public function _On404(): void
404 fallback: 404 header, runs the error_404 callable/view; when absent outputs a placeholder plus route error under debug; returns early when is_root/skip

    public function _OnDefaultException($ex): void
Default exception exit: restores phase, optional log, 500 header, uses error_500 or the default detailed/placeholder page; avoids error_500 before ininit

    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
Debug error handler: returns when not debug; assembles errno/…/shortfile, uses error_debug or the built-in one

    public function isAbsPath($path)
Whether a path is absolute: starts with `/`, a drive letter (e.g. `C:\`, `C:/`), or `\\`; the argument is cast to string first.

    public function slashDir($path)
Normalizes the path tail to end with a directory separator (`''` returned as-is, otherwise `rtrim('/\\')` then append `DIRECTORY_SEPARATOR`).

    public function getOverrideableFile($path_sub, $file, $use_override = true, $must_exist = false)
Phase-aware file lookup: walks back level by level from the current phase looking for `path_sub/subdir/file`, returns on first hit; with `$must_exist=true` **only really existing files count** (returns `null` when none do), with `false` returns the last candidate path (may not exist yet, for writing)

    public function getConfigFile(string $file, bool $must_exist = false)
Gets a file under config (base `path_config` + `getOverrideableFile`); returns `null` when `$must_exist=true` and the file does not exist

    public function getRuntimePath(): string
Returns the absolute runtime directory (prepends the root path when path_runtime is relative)

    public function skip404Handler()
Sets options[skip_404]=true, skipping the 404 display

    public function onBeforeOutput()
Pre-output hook; the framework calls it first when it decides to render a View (empty base)

    public function _Show(array $data, string $view = '')
Renders a view with data; the view name defaults to the current route path; calls onBeforeOutput first

    public function checkInstallToPage(?string $url_install = null): void
Not installed: 302 to the install URL (defaults to options[url_install]) and exit

    public function lang($str, $args = [], $fallback = null)
Minimal translation: the lang_handler callback takes priority; otherwise replaces {key} / uses fallback

    public function langText(string $desc, array $args = []): string
Translates the `[[key|fallback]]` fragments in the text through lang() (args can be passed)

### Protected methods

    protected function onPrepare(): void
In the root app, calls loadSetting() at Kernel's onPrepare stage to load the settings

    protected function initComponentsOfRoot($components, $default): void
Adds SystemWrapper/CoreHelper(EXT_SKIP_INIT) and Logger(EXT_DEFAULT) to the root components, then proceeds to Kernel_initComponentsOfRoot; Logger uses EXT_DEFAULT so it gets initialized by `init(this app's options)`, so its `path_log`/`log_file_template`/`log_prefix` pick up the app options

    protected function initComponentsOfInner($classes, $default): void
After merging View(FOLLOW_APP) into the inner layer, proceeds to Kernel…Inner

    protected function initComponentsOfDynmic($classes, $default): void
After merging SuperGlobal and View(FOLLOW_APP) into the dynamic layer, proceeds to Kernel…Dynmic

    protected function prepareServe()
After calling Kernel…prepareServe: when the maintenance setting (error_maintain / view) hits, outputs the maintenance page directly

    protected function loadSetting(): void
Starting from options[setting], runs dealWithEnvFile/SettingFile in turn according to use_env_file and setting_file_enable

    protected function dealWithEnvFile(): void
parse_ini_file(root/.env) merged into $this->setting

    protected function dealWithSettingFile(): void
Resolves the config file as absolute/relative, requires the returned array into setting; throws ErrorException when missing and !ignore

    protected function haltInitInBaseClass(): void
Throws "DO NOT INIT App!" when static::class===self::class (direct App)

## Related links

- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — this class's skeleton (init/run/services)
- [DuckPhp\DuckPhp](DuckPhp.md) / [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — the common entry points
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\View](Core-View.md), [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md), [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md), [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md)
- guide: [configuration](../guide/configuration.md), [lifecycle](../guide/lifecycle.md)
