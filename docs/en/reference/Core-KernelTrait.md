# DuckPhp\Core\KernelTrait

The "skeleton logic" of the application core: `use`d by `DuckPhp\Core\App` (and its derivative `DuckPhp`), it carries initialization, running, and phase + lifecycle orchestration.

## Introduction

`KernelTrait` writes "what an application is and how it runs" into one trait, pulled in by the application class (e.g. `App`) via `use KernelTrait`. It is responsible for:

- Declaring/merging the app's default options (`$kernel_options`) plus several runtime properties;
- Providing static entry points such as `RunQuickly()` (one-call boot), `Root()` (root app), `Phase()` (phase read/write);
- Running one full application initialization inside `init()`: derive path/namespace → register into the phase container → initialize the exception system → run the `onPrepare()` hook → initialize `Console`/`Route` and the `ext` extensions → recursively initialize the `app` child apps → `onInited()`;
- Providing Web (`serve()`), CLI (`execute()`), and the unified `run()`;
- Providing a set of instance methods exposing internal state (root?, phase name, in-call info, etc.) and overridable hook methods (`onPrepare/onInit/onInited/onRequest`…).

You do not necessarily use it directly: as long as you extend `DuckPhp\Core\App` or `DuckPhp\DuckPhp`, the subclass can customize by overriding `$options` / those protected hooks, without touching this trait's source.

## Options

`KernelTrait` defines the defaults as `protected $kernel_options` (merged into `public $options` with each layer at construction time). The table lists the keys that **actually take effect** (the parts commented out later in the source, such as `controller_*`, are historical / left for upper layers sharing the same options to enable; they do not take effect here by default).

| Option | Default | Description |
|---|---|---|
| `is_debug` | `false` | Debug flag field (source comment: "no use, just align" — placeholder alignment only; use aggregate logic such as `App::IsDebug()` for the real decision). |
| `path` | `null` | Project root directory. When explicitly left unset, it is derived two levels up from `SCRIPT_FILENAME`…, then completed into an absolute path ending with `/` in `initOptions()`. |
| `namespace` | `null` | Default project namespace. When unset it is inferred from the current implementation class (`getDefaultProjectNameSpace`). |
| `name` | `''` | Short name of this app; used for child-app phase naming. |
| `app` | `[]` | Child-app declaration table. Shaped like `[class => ['namespace'=>…,'controller_url_prefix'=>…, …]]`; `initChildren()` turns each into an independent child phase. |
| `cmd` | `[]` | CLI command-class map to register for the current app (`class => "default method prefix"`). Consumed by Console. |
| `data` | `[]` | Reserved data slot (source comment: "no use in, just align"). |
| `ext` | `[]` | Extension components to initialize in this layer: `class => true|array|'@method'|EXT_* constant`. Applied during initialization via `initOptions→…→initComponents…OfExt`. |
| `cli_enable` | `true` | Whether to enter command handling (`execute()`) under CLI. With `false`, even CLI goes through Web. |
| `skip_exception_check` | `false` | With `true`, `runException` re-throws the exception directly instead of entering the framework's unified exception dispatch. |
| `use_exit_exception` | `true` | Whether to define the global `__EXIT_EXCEPTION` (as `ExitException::class`) so exit semantics can be caught. |
| `override_from` | `null` | Records the class name that overrode this one. Used by the `override_class` flow. |
| `override_class` | `null` | If given, `init()` hands the current initialization over to this class (after construction it re-enters init with `override_from=get_class(original)`). |
| `app_children_allow_mix_mode` | `true` | Allows child-app shorthand: use the table key as `controller_url_prefix`, with a `'class'` key inside the value naming the real child-app class. With `false`, the structured form is required. |
| `on_init` | `null` | (`callable`) Called at the `onInit()` stage during initialization. |
| `on_inited` | `null` | (`callable`) Called at the `onInited()` stage after all initialization is done. |
| `on_request` | `null` | (`callable`) Called at the start of every `serve()`. |

> Note: `is_debug` / `data` are marked in the code as alignment placeholders only; do not rely on them to judge state.

## Usage

A minimal app inheriting this trait's semantics should still extend `App`/`DuckPhp`; here is a behavior example based directly on this trait's structure:

```php
// You normally don't `use` it directly; the equivalent is extending DuckPhp\DuckPhp / App:

class MyApp extends DuckPhp\DuckPhp {
    public $options = [
        'namespace' => 'MyProj',
        'app'   => [ /* 可有多个子应用 */ ],
        'cmd'   => [ MyCmd::class => 'command_', ],
        'ext'   => [ MyFeature::class => true, ],
        'cli_enable' => true,
    ];
}

MyApp::RunQuickly([]);   // cli? execute/internal? No: init first, then execute()/serve() by environment
```

Key points:

- To make a class "become an App": it must ultimately `use KernelTrait` (extending `App` already provides this).
- Per request/command, enter via `$app->run()` or the static `RunQuickly()` as needed.
- `init()` distinguishes the top level by `$context==null → is_root=true`; any init with a `context` passed becomes a child app (phase suffix).
- To run CLI commands: register via `regConsoleCommand()`/`cmd`; management lives in `Console`.

## Lifecycle order

The order inside `init()` (to help decide where to place hooks):

```
override_class?> → haltInitInBaseClass → initOptions(options, 推导 path/ns)
→ is_root / is_cli 判定 → initContainer(context)      // enter a phase, register the instance
→ initException(options)                               // exception callback binding (root)
→ onPrepare() ├ loader 差异 / 顶部 require 等可放
→ initComponents()                                     // Root: Console; every phase: Console+Route; ext
    → initComponentsOfRoot / Init    …… subclass 可注入组件
→ onInit()        （执行 on_init 回调）
→ initChildren($options['app'])       // recursively init each -> child phase
→ is_inited = true
→ onInited()      （执行 on_inited 回调）
```

One Web request through `serve()`:

```
prepareServe()   // phaseToCurrent + initComponentsOfDynmic(RENEW)
onRequest()      // the on_request callback
try{ Runtime::run(); Route::run() || runChildren() }
        // all miss -> own phaseToCurrent -> _On404()
catch(Throwable) → runException()（记录 last phase→…→ExceptionManager::CallException）
finally → phaseToCurrent; Route::clear(); Runtime::clear()
```

CLI `execute()`:

```
try{ return Console::run(); } catch(Throwable){ runException(); return true; }
```

`run()` picks execute or serve by `cli_enable`.

## Caveats

1. Root vs child determination: `static::class === self::class`, or is-a `self::class` with no context — the `is_root` result affects whether execute is possible and how phase components are loaded; query it after init via `isRoot()` instead of guessing.
2. Phase uniqueness: a duplicate child-phase name throws `DuckPhpSystemException` in `initContainer` with the hint "set … 'name' options". Give child apps that share a class different `name` values.
3. All `on_*` callbacks are invoked from inside the hook methods: do not both pass an `on_init` callback and override `onInit()`, or it runs twice.
4. `run()` semantics: only root + CLI + `cli_enable` goes to execute; otherwise Web. To force "CLI also enters serve", use `cli_enable=false`.
5. Overriding: to keep the parent skeleton logic when overriding a hook, call `parent::` as needed.
6. Static entry points: `RunQuickly/Root/Phase/FromCurrentParent/SwitchRootPhase` are provided as `public static`.
7. `Root($switch_phase = false)` returns the instance registered in the root phase and does **not** change the current phase just by reading it; only explicitly passing a truthy value also switches back to the root phase (a one-step "grab the root instance and switch back to root" usage from a child app). The phase-switch step internally calls `App::Phase(self::$ROOT_PHASE)` (hard-coded on `App`), while the returned instance is the `self::class` registration in the root phase — the source is authoritative.

## All options

```php
    protected $kernel_options = [
        'is_debug' => false, // no use, just align
        'path' => null,
        'namespace' => null,
        'name' => '',

        'app' => [],
        'cmd' => [],
        'data' => [], // no use in, just align
        'ext' => [],

        'cli_enable' => true,
        'skip_exception_check' => false,
        'use_exit_exception' => true,
        'override_from' => null,
        'override_class' => null,
        'app_children_allow_mix_mode' => true,

        'on_init' => null,
        'on_inited' => null,
        'on_request' => null,
    ];
```

## Methods

> Once the `App` class uses this trait, the methods it defines become available members; the full list below keeps the source order, grouped as "public / protected".

### Public methods

    public static function RunQuickly(array $options = [], ?callable $after_init = null): bool
One call: `static::_()->init($options)` (with optional after-init callback), then root + cli_enable → execute(), otherwise serve()

    public static function Root($switch_phase = false)
Returns the current class's instance registered in the root phase (null if none); when `$switch_phase` is truthy it also switches the current phase back to the root phase

    public static function Phase($new = null)
Static phase port: no argument returns the current phase name; passing a string switches (returns the old value)

    public static function FromCurrentParent()
If in a "child app of a parent app" phase, switches back to the parent instance and returns it; otherwise returns null

    public static function SwitchRootPhase($phase)
Resets the root phase and the shared container name (`$phase.'#shared'`); also syncs PhaseContainer's current/default

    public function _Phase(?string $new = null): string
Instance implementation of Phase; with an argument it switches away and records last_phase, returning the pre-switch value

    public function isRoot()
Whether this is the root App (no parent context)

    public function isCli()
In a CLI call — returns is_cli at root; child apps delegate to root

    public function getLastPhase()
Returns the previous phase name

    public function getThisClassName()
Returns the class name this instance was registered into the container with (this_class)

    public function getThisParent()
Derives the parent from the current "child phase", switches back to it, and returns the parent instance

    public function toThisChild($class)
Switches to the phase of the `class` child app and returns that instance (null if it does not exist)

    public function getThisPhaseName()
Returns the current instance's phase name string

    public function getThisCommandPrefix()
Derives the CLI command prefix from this phase name (separators turned into `-`)

    public function regConsoleCommand(string $class, string $default_method = 'command_')
Registers a command class for the current phase into Console, and writes $options['cmd']

    public function getProjectPath()
Returns the root app's `options['path']`

    public function init(array $options, ?object $context = null)
Master application init entry (hands over to the new class and re-enters if override_class is set). Order: see the "Lifecycle order" section

    public function toChildPhase(string $class)
If $class is registered as a child app, switches to its phase and returns true; otherwise false

    public function run(): bool
Dispatches to execute() / serve() by cli_enable

    public function serve(): bool
Runs one request-level route … (pre→Route/children→404→exception→finally hooks+clear)

    public function execute(): bool
CLI: `Console::run()`; exceptions are wrapped by runException

    public function phaseToCurrent(): void
Switches PhaseContainer's current back to this instance's phase

    public static function On404(): void
void outer shell: calls the instance _On404

    public static function OnDefaultException($ex): void
Hands over to the instance _OnDefaultException (for ExceptionManager to register as the root default handler)

    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
Static shell forwarding to _OnDevErrorHandler

    public function _On404(): void
Fallback 404 response (this trait prints "no found"; the App layer overrides with a custom view)

    public function _OnDefaultException($ex): void
Fallback default exception (the trait only prints a placeholder; the App layer does logging/pages)

    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
Fallback dev-time error (the trait only prints a placeholder; the App layer extends)

### Protected methods

    protected function initOptions(array $options): void
Merge and fill: derive namespace/path defaults and turn path into an absolute path

    protected function getDefaultProjectNameSpace(?string $class): string
Strips the last two levels of the implementation class's namespace to get the project namespace (X\System\App → X)

    protected function getDefaultProjectPath(): string
Derives the current project directory from the parent of SCRIPT_FILENAME (takes _SERVER from __SUPERGLOBAL_CONTEXT when the outer globals are not visible)

    protected function initContainer(?object $context = null): bool
Root: builds the default container and sets current; child: generates a unique `<parentPhase>:<name>` phase and registers it, recording the self/static/override instances

    protected function createLocalObject(string $class, ?object $object = null): object
Creates a local object in the current phase (not shared across phases) and registers it

    protected function initException(array $options): void
Configures the exception system: binds the default/dev handlers to root self, disables handle_all for non-root; may define __EXIT_EXCEPTION

    protected function initComponents()
Initializes this app's core: Console (FOLLOW) first at root, Route (FOLLOW) per layer, then consumes options['ext']

    protected function initComponentsOfRoot($classes, $default): void
Marks classes as public, then initializes them (root-phase components)

    protected function initComponentsOfInner($classes, $default): void
Initializes (Route/Console, also common to child apps), and registers this phase's command prefix and cmd

    protected function initComponentsOfExt($classes, $default): void
Initializes extensions per the ext table

    protected function initComponentsOfDynmic($classes, $default): void
Assembly port for "dynamic components" (prepareServe uses it per request with RENEW)

    protected function initComponentsByClasseOptions(array $exts, $default): void
Walks the `class=>opts` table and hands each to initExtensionsByOptions (empty entries auto-filtered)

    protected function initExtensionsByOptions(string $class, $options, $default)
Interprets opts (EXT_* constant/array/`@method`/option key name) and inits/creates/skips an extension accordingly

    protected function initChildren(array $apps): void
Normalizes and initializes each child app of `app`, records each child's `__phase__` and phaseToCurrent back to the parent

    protected function prepareServe()
Pre-serve: returns to this phase and RENEW-rebuilds the "dynamic components"

    protected function runException(\Throwable $ex): void
Unified exception handling: records last phase, Runtime::onException, then hands to ExceptionManager; throws directly when skip_exception

    protected function runChildren(): bool
Lets each child app in options['app'] order try serve(); returns on the first success

    protected function onAfterCreatePhases(): void
(Empty) Hook after the root container phases are built, for subclasses to extend

    protected function haltInitInBaseClass(): void
(Empty) "Do not initialize the base class directly" interception point; App uses it to throw, DuckPhp opens it up

    protected function onPrepare(): void
(Empty) Initialization-prepare hook (after options merge, before component init)

    protected function onInit(): void
Runs the $options['on_init'] callback (if any)

    protected function onInited(): void
Runs the $options['on_inited'] callback (if any)

    protected function onRequest(): void
At the start of each `serve()` request, runs the $options['on_request'] callback (if any)

## Related links

- [DuckPhp\Core\App](Core-App.md) — the application base class that `use`s KernelTrait
- [DuckPhp\DuckPhp](DuckPhp.md) / [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — the usual entry subclasses
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — component base init / context
- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) — the phase container (this trait depends on it)
- [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Core\Console](Core-Console.md) — the targets of serve and execute
- guide: [lifecycle](../guide/lifecycle.md), [advanced-phase](../guide/advanced-phase.md), [cli](../guide/cli.md)
