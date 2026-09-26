# 4-1 Containers and phases, inside out

> What this solves: where the instance behind `ClassName::_()` actually lives and by what rules it is found; why instances are "the same name but different objects" with several apps/phases; and, when things go wrong, how to see what is in the container and how to troubleshoot it.
> Prerequisites: [Chapter 3-1 The application tree and phase basics](advanced-phase.md), [Chapter 3-4 Component sharing and cross-app communication](component-sharing.md). About 20 minutes.
> The examples here come from `tests/data_for_tests/ZThirdDemo`; you can really run them with `wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` (**from the repository root**).

## Minimal example

This assertion in `ZThirdDemoTest.php` is the whole mechanism of this chapter in miniature:

```php
$root_logger = spl_object_id(Logger::_());
$root_lang   = spl_object_id(Lang::_());
App::_()->toThisChild(ThirdApp::class);        // switch to the child app's phase ':shop'
spl_object_id(Logger::_()) === $root_logger;  // true  -> shared instance
spl_object_id(Lang::_())   !== $root_lang;    // true  -> one per phase
App::Phase('');                                // switch back to the root phase
```

The same [`Logger::_()`](../reference/Core-Logger.md), the same [`Lang::_()`](../reference/Component-Lang.md) — why is one identical everywhere and the other changes with the phase? The answer sits entirely in one class, [`DuckPhp\Core\PhaseContainer`](../reference/Core-PhaseContainer.md) (reference manual: Core-PhaseContainer).

## How it works

### There is one container; it buckets by phase

`PhaseContainer` is globally unique inside the process (`PhaseContainer::$instance`). It holds three things (`src/Core/PhaseContainer.php` lines 13-16):

```php
public $containers = [];     // all buckets: containers[phase name][class name] = instance
public $current = '';        // the current phase (bucket name)
public $default = '';        // the "shared bucket" name; after root init it is '#shared'
public $shared_classes = []; // the class names marked as shared
```

A "phase" is just a key of `$containers`. The root phase is the empty string `''`; a child app is a name such as `:shop` (Chapter 3-1). `#shared` is a bucket name too — **shared instances do not live inside some phase, they live in this dedicated shared bucket**.

### How an instance is stored and fetched: the three steps of `_GetObject()`

Every `::_()` ends up in `PhaseContainer::_()->_GetObject($class, $object)` (`src/Core/PhaseContainer.php` lines 42-58):

```
1. look for $class in the current phase bucket -> return it on a hit (with $object passed, replace first and return)
2. the class is marked shared (isset($this->shared_classes[$class])) -> look in the shared bucket ($this->default, i.e. '#shared')
3. neither -> new $class(), store it in "the bucket step 2 chose" (shared classes go to '#shared', the rest to the current phase bucket), return it
```

So one rule covers every case: **a non-shared class gets one copy per current phase; a shared class has exactly one copy in the whole process, stored in the `#shared` bucket**. `Lang` is the former, `Logger`/[`Console`](../reference/Core-Console.md)/[`DbManager`](../reference/Component-DbManager.md) the latter — being shared does not depend on where the class lives but on whether `addSharedClasses()` marked it (see below).

### Who gets marked shared: decided during assembly

The marking happens when the app initialises (`src/Core/KernelTrait.php` lines 325-346, `src/DuckPhp.php` lines 109-127):

- the root app's `initComponents()` hands `Console` to `initComponentsOfRoot()` with `EXT_FOLLOW_APP`, and that calls `addSharedClasses()` before initialising — **every class going through `initComponentsOfRoot()` is marked shared**.
- [`DuckPhp::initComponentsOfRoot()`](../reference/DuckPhp.md) adds `DbManager`/[`RedisManager`](../reference/Component-RedisManager.md) (`EXT_DEFAULT`) and [`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md)/[`GlobalUser`](../reference/GlobalUser-GlobalUser.md)/[`GlobalEvent`](../reference/Component-GlobalEvent.md) (default `EXT_DISABLE`; shared once enabled) on top of that.
- each app's own [`Route`](../reference/Core-Route.md) goes through `initComponentsOfInner()` and is **not marked shared** — which is why every phase has its own route table.
- extensions in `options['ext']` go through `initComponentsOfExt()` and are likewise not marked shared — one per phase by default.

### Mutable singletons: `::_()` and `::_($new)`

[`ComponentBase`](../reference/Core-ComponentBase.md) uses [`SingletonExTrait`](../reference/Core-SingletonExTrait.md), whose `::_()` is one line (`src/Core/SingletonExTrait.php` lines 16-19):

```php
public static function _($object = null)
{
    return PhaseContainer::GetObject(static::class, $object);
}
```

- `::_()` with no argument: fetch the instance by the three steps above.
- `::_($object)` with an argument: **put `$object` into the matching bucket** (replacing an existing one) and return it. That is the "mutable singleton" — the instance is not fixed, anyone can swap it. The framework does it itself: in `JsonView::init()`, `View::_(static::_())` replaces the `View` singleton with its own instance (`src/Ext/JsonView.php` line 34).

[`Foundation\SingletonTrait`](../reference/Foundation-SingletonTrait.md) is merely an alias of `SingletonExTrait` (`src/Foundation/SingletonTrait.php` is nothing but `use SingletonExTrait`); the two are exactly equivalent — the project's four-layer base classes use the former, framework components the latter, with identical semantics.

### Phase-name rules and the switching API

| Rule | Description |
|---|---|
| Root phase | the empty string `''` ([`KernelTrait::$ROOT_PHASE`](../reference/Core-KernelTrait.md), `src/Core/KernelTrait.php` line 69) |
| Shared bucket name | `'#shared'` (`$ROOT_PHASE_OF_SHARED`, line 70); `SwitchRootPhase($p)` changes it to `$p.'#shared'` (lines 131-139) |
| Child phase name | `<parent phase>:<name>`, where `name` is the child app's `name` option, or its `namespace` when unset, or the class-name basename when set to `'@'` (`initContainer()`, `src/Core/KernelTrait.php` lines 243-249) |
| Same-name phase clash | when a child app's phase name is taken, it throws [`DuckPhpSystemException`](../reference/Core-DuckPhpSystemException.md) telling you to change the `name` option (lines 251-256) |

The switching entry points (all in `KernelTrait`): [`App::Phase($new)`](../reference/Core-App.md) switches buckets and returns the old phase (lines 165-175); `App::Root(true)` fetches the root instance and switches the phase back to root (lines 104-111); `toThisChild($class)` switches to a child phase following `options['app'][class]['__phase__']` and returns that child app (lines 203-212); `SwitchRootPhase()` resets "who is root" in nested scenarios (lines 131-139).

### Test helper: `RestAllContainerForTesting()`

`PhaseContainer::RestAllContainerForTesting()` (`src/Core/PhaseContainer.php` lines 33-36) swaps the whole container for a brand-new one — the test suite calls it at the start of every case so singletons do not leak between cases. Copy that pattern when writing your own multi-app tests (Chapter 2-17).

## Common patterns

```php
// 1) see what is in the container right now (troubleshooting move #1): print every bucket
PhaseContainer::Dump();        // static convenience, same as PhaseContainer::_()->dumpAllObject()

// 2) is a class shared? (that decides whether it is shared across phases)
$is_shared = isset(PhaseContainer::_()->shared_classes[Logger::class]);

// 3) a child app wrongly picked up the root app's instance? give it a local instance in this phase
$this->createLocalObject(DbManager::class);   // this is what the framework does for local_database
//    (src/DuckPhp.php lines 140-145: createLocalObject, then re-init in the current phase)

// 4) swap a component's singleton by hand (the low-level way to replace framework behaviour, Chapter 4-3)
View::_(new MyView())->init(App::_()->options, App::_());

// 5) wipe it and start over (tests)
PhaseContainer::RestAllContainerForTesting();
```

The output format of `dumpAllObject()` (`src/Core/PhaseContainer.php` lines 124-154): it prints `current`/`default`, then lists the shared_classes table, then lists every instance **bucket by bucket** — shared class names carry a `*`, and when the class name differs from the actual object's class the real class is shown in parentheses (for example [`DuckPhp\Core\View (DuckPhp\Ext\CallableView)`](../reference/Ext-CallableView.md), which tells you the [View](../reference/Core-View.md) singleton was replaced). `tests/data_for_tests/ZAllDemoTest-10431.txt` is a real dump you can compare against.

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| `Xxx::_()` in a child app returns the main app's instance | The class is marked shared, so its instance sits in the `#shared` bucket | That is the design; isolate it with `createLocalObject()` (or the `local_database`/`local_redis` switches) |
| The state of a component "shared" between two apps overwrites each other | A shared instance is the **same object**, options included | Think before changing options: maybe you want `createLocalObject()` so each app has its own |
| After switching phases the rest of the code runs in the wrong app | `App::Phase($new)` was never switched back | `$old = App::Phase($new); ... App::Phase($old);` |
| A dump shows a class name with parentheses `(OtherClass)` | That singleton was replaced via `::_(new object)` | Normal (e.g. [JsonView](../reference/Ext-JsonView.md) replacing View); trace where the replacement came from (Chapter 4-3) |
| A child app's init throws `Phase Short name ... is used by ...` | Two child apps share the same `name` (or namespace), so the phase names clash | Give one of them a distinct `'name'` option explicitly |
| Singleton state leaks from one test case into the next | The container was not reset | Call `PhaseContainer::RestAllContainerForTesting()` at the start of the case |

## Next steps

- [Chapter 4-2 Developing components and extensions](custom-component.md): how components declare options, initialise and hook into routing — the container rules here are the environment they run in.
- [Chapter 4-3 Replacing framework behaviour](replace-behavior.md): a systematic look at replacement techniques such as `::_(new instance)` and `system_wrapper_replace()`.
- Reference manual: [DuckPhp\Core\PhaseContainer](../reference/Core-PhaseContainer.md), [DuckPhp\Core\ComponentBase](../reference/Core-ComponentBase.md), [DuckPhp\Core\SingletonExTrait](../reference/Core-SingletonExTrait.md), [DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md)
