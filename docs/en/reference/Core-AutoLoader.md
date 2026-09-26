# DuckPhp\Core\AutoLoader

A simplified PSR-4-style autoloader (no Composer dependency) that lets an application load its own namespaces and class files before the framework boots.

## Introduction

`Core\AutoLoader` provides a very light "namespace → directory" autoload: map a prefix to a directory, and afterwards any class under that prefix is looked up as a same-named `.php` under the corresponding subpath per PSR-4 rules and `include_once`d.

- It can map `DuckPhp\` itself to `src/`, so the framework core works without `autoload.php` (another minimal callback, `DuckPhpSystemAutoLoader`, does exactly this job).
- It supports a set of `path=>prefix` mappings (the instance `namespacePaths`), a PSR-4 array (`psr-4`, where the parameter is namespace=>path), and relative/absolute directories.
- Under the no-Composer philosophy, the framework usually boots it itself instead of relying on vendor loading.

It **does not extend `ComponentBase`**, because it must be available before the app/context exists; so it manages its own singleton, init and enablement with a standalone `_()` + `init/run`.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class AutoLoader` (not a ComponentBase subclass)
- Key properties: `public $options`; `public $namespace_paths` (load mapping); `protected $is_inited / $is_running`.

## Options

`AutoLoader::$options` (defaults under "All options"):

| Option | Default | Description |
|---|---|---|
| `path` | `''` | Project root directory. Defaults to trying `realpath(getcwd().'/../')`. Used to normalize relative paths. |
| `namespace` | `''` | The app's main namespace (e.g. `App`). When non-empty and `skip_app_autoload` is false, `namespace` is mapped to `path_namespace`. |
| `path_namespace` | `'app'` | The app directory (the directory the main namespace maps to); may be absolute; relative means relative to `path`. By default maps `app/` → `${namespace}\`. |
| `skip_app_autoload` | `false` | When true, the main namespace is no longer auto-mapped to the app directory. |
| `autoload_cache_in_cli` | `false` | When true, `run()` in CLI mode first runs `cacheClasses()` (opcache precompilation). |
| `autoload_path_namespace_map` | `[]` | Extra `path => namespace` mappings (the direction is not flipped). |
| `psr-4` | `[]` | PSR-4-style mapping: `namespace => path` (flipped to path=>namespace at init and loaded into `namespace_paths`). |

## Usage

Normal project bootstrap (equivalent to letting an app root autoload its own source):

```php
\DuckPhp\Core\AutoLoader::RunQuickly([
    'path'           => __DIR__ . '/..',
    'namespace'      => 'MyApp',
    'path_namespace' => 'app',          // map MyApp\ to <root>/app
]);
```

Afterwards, referencing `MyApp\Controller\Home` tries `include <root>/app/Controller/Home.php`.

### Adding a namespace (common)

```php
use DuckPhp\Core\AutoLoader;

// way one: static convenience (note the parameter is namespace => path)
AutoLoader::addPsr4('Vendor\\', __DIR__ . '/vendor');

// way two: instance mapping (manual)
AutoLoader::_()->assignPathNamespace(['/abs/src/' => 'Vendor\\src\\']);
```

### Booting the framework core without vendor

For the framework or a no-Composer scenario, you can register just the minimal callback:

```php
spl_autoload_register([\DuckPhp\Core\AutoLoader::class, 'DuckPhpSystemAutoLoader']);
```

`DuckPhpSystemAutoLoader($class)` only handles the `DuckPhp\` prefix → the corresponding `src/.../...php` (ordinary classes may not need it in a vendor/autoload scenario).

## Configuration example

A complete minimal example that can replace composer: see the `RunQuickly` call. To add your own namespace for a plugin library:

```php
AutoLoader::_()->init(['path' => __DIR__])->assignPathNamespace([
    '/vendor/mylib/' => 'Mylib\\',
]);
AutoLoader::_()->run();   // register spl_autoload_register
```

## Caveats

1. It is not a `ComponentBase` subclass: it must boot before the app is up; don't assume it has a `PhaseContainer`.
2. The same file name is `include_once`d; duplicate class definitions resolve to the first one — be careful when manually including other copies.
3. The actual keys of `namespace_paths` are "directories", the values "prefixes"; when written as `psr-4` (namespace=>path) they are `array_flip`ped before being added.
4. The `addPsr4` signature is `namespace => path`; internally it is flipped to the internal representation.
5. `autoload_cache_in_cli` only precompiles (`opcache_compile_file`); it does not "record as needing include"; outside CLI you can call `cacheNamespacePath/cacheClasses` manually as well.
6. `clear()` unregisters the callback with `spl_autoload_unregister`; call it "after you're done using it".

## All options

```php
    public $options = [
        'path' => '',
        'namespace' => '',
        'path_namespace' => 'app',
        'skip_app_autoload' => false,

        'autoload_cache_in_cli' => false,
        'autoload_path_namespace_map' => [],
        'psr-4' => [],
    ];
```

## Methods

> The source's own methods and the (optionally registrable) static callbacks are listed uniformly below. Visible members: the static conveniences `_/RunQuickly/addPsr4`, the autoload callbacks `AutoLoad/DuckPhpSystemAutoLoader`, the instance methods init/run/etc.

### Public static methods

    public static function _($object = null)
Gets this AutoLoader singleton; passing an object registers/replaces the instance for the current class.

    public static function RunQuickly(array $options = [])
`static::_()->init($options)->run()`: combines initialization and registration in one go.

    public static function addPsr4($namespace, $input_path = null)
Adds a PSR-4 mapping as `namespace => path`; pass an array for multiple namespaces (value = path). Internally flipped to path=>prefix.

### Public methods

    public function __construct()
Empty constructor.

    public function init(array $options, ?object $context = null)
Initialization: idempotent (returns itself when already done); fills/normalizes path, sets namespace/path_namespace. When skip=false and a namespace is given, registers app dir→namespace; also merges autoload_path_namespace_map and the flipped psr-4.

    public function isInited(): bool
Whether init has been done.

    public function run()
Registers autoloading to `spl_autoload_register([static::class,'AutoLoad'])`; runs cacheClasses first when `autoload_cache_in_cli`; guards against repeats (is_running).

    public function runAutoLoader()
A proxy of `run()` (a writing preference).

    public static function AutoLoad(string $class): void
The spl callback shell: forwards to `_Autoload($class)`.

    public function _Autoload(string $class): void
Matches prefixes against `namespace_paths`, turns the remainder into a relative file path and `include_once`s it (silently returns when not found).

    public function assignPathNamespace($input_path, $namespace = null)
Merges additional records into `namespace_paths`: supports a `path=>namespace` array or a single path+namespace pair; path is normalized to end with `/`, namespace to end with `\`.

    public function cacheClasses()
Walks all directories of `namespace_paths` (absolute) and precompiles each .php under them with `opcache_compile_file`; returns the collected files.

    public function cacheNamespacePath($path)
Recursively collects the files of a single directory and precompiles them (same semantics, single-directory version).

    public function clear(): void
Unregisters the `[AutoLoader,'AutoLoad']` callback from PHP's autoload stack.

    public static function DuckPhpSystemAutoLoader(string $class): void
Standalone minimal callback: only handles the `DuckPhp\` prefix, maps it to `<framework>/src/<…>.php` and include_onces it (for framework self-loading without vendor).

    public function slashDir($path)
Normalizes the path tail to end with a directory separator (`''` returned as-is, otherwise `rtrim('/\\')` then append `DIRECTORY_SEPARATOR`).

### Protected methods

    protected function isAbsPath($path)
Whether a path is absolute: starts with `/`, a drive letter (e.g. `C:\`, `C:/`), or `\\`; the argument may be null.

    protected function getNamespacePath(string $sub_path, string $main_path): string
Resolves a relative/absolute subpath to a directory path: when relative, returns an absolute one based on main_path and appends a directory separator.

## Related links

- [DuckPhp\Core\App](Core-App.md) the application entry point (the upper-level policy on Composer/autoload)
- the `autoload psr-4` section of composer.json — the autoload counterpart under Composer
- [Core-CoreHelper](Core-CoreHelper.md) (when class-name lookup objects are involved)
