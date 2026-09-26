# DuckPhp\Core\ComponentBase

## Introduction

`ComponentBase` is the base class of the vast majority of components in the DuckPHP framework. The framework's core classes (`Route`, `View`, `Console`, `Logger`, `SuperGlobal`, `SystemWrapper`, etc.) and the various `Component\*`, `Ext\*` components all extend it directly or indirectly.

It converges the common behavior of a "component" in one place: the `ClassName::_()` singleton-style access entry comes from `use SingletonExTrait`; the `init()` template flow uniformly handles option merging, context injection and initialization; `isInited()` exposes the initialization state. A subclass usually only needs to override the two empty hooks `initOptions()` / `initContext()` to customize initialization, without caring about the base-class flow.

> In the source, the line `class ComponentBase // implements ComponentInterface` states in comment form that it fulfills the `ComponentInterface` contract (it is not declared with the `implements` keyword).

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class ComponentBase`
- Trait used: `DuckPhp\Core\SingletonExTrait`

## Option mechanism

`ComponentBase` itself only declares an empty `public $options = []` and **defines no concrete option keys**. Concrete options are declared by subclasses in their own `public $options`. The base class provides this option mechanism:

- When `init()` merges options it uses `array_intersect_key(array_replace_recursive($this->options, $options), $this->options)`: only keys **already declared by the subclass** are kept; unknown incoming options are filtered out by this whitelist (this is also the foundation of the framework's "option names stay controlled, no casual extension").
- `init_once` (protected property, default `false`): when `true`, `init()` returns the current instance directly if it was already initialized, unless the incoming options carry `__force__ => true`.
- `reInit()` internally just sets `__force__` to true and reruns `init()`, to force re-initialization.

## Usage

### Defining your own component

```php
namespace My\Component;

use DuckPhp\Core\ComponentBase;

class MyComponent extends ComponentBase
{
    public $options = [
        'my_option' => 'default',
    ];

    protected function initOptions(array $options): void
    {
        // here you can read the merged $this->options and process it
    }

    protected function initContext(object $context): void
    {
        // $context is usually the owning App instance; save it if needed
    }
}

// get the instance and initialize it ($app is usually App::_())
$component = MyComponent::_()->init(['my_option' => 'value'], $app);
if ($component->isInited()) {
    // initialization completed
}
```

### Forcing re-initialization

```php
$component->reInit(['my_option' => 'other']);
```

### Accessing the owning App

```php
$app = $component->context(); // internally equivalent to App::_()
```

## Configuration example

`ComponentBase` has no concrete options; when configuration is needed the subclass declares `public $options` and the corresponding keys are passed in, e.g.:

```php
$component = MyComponent::_()->init([
    'my_option' => 'value',
    // keys not declared in $options are filtered out
], App::_());
```

## Caveats

- `init()` returns the current instance and is chainable; interfaces/callers commonly use it as `ClassName::_()->init($options, $context)`.
- Outside the `init_once` scenario, `isInited()` does not prevent repeated `init()`; for idempotent initialization set the subclass's `init_once` property to `true` yourself.
- A `null` `$context` skips `initContext()` (when `App` etc. acts as the context itself, an instance is passed).
- This class **provides no path utilities**: the static `IsAbsPath()` / `SlashDir()` of earlier versions have been removed; path checks and joins now live in [DuckPhp\Core\App](Core-App.md) (`isAbsPath()` / `slashDir()`), and [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) has its own private copy of `isAbsPath()` / `slashDir()`.

## Methods

### Public methods

    public function __construct()
Empty constructor, so a subclass can be `new`ed directly by `_()` without writing a constructor.

    public function context()
Returns the owning App instance; internally equivalent to `App::_()`.

    public function init(array $options, ?object $context = null)
Component initialization template: whitelist option merge → `initOptions()` → `initContext()` when a context is passed → mark `is_inited`; with `init_once`, already initialized, and no `__force__`, returns itself directly.

    public function reInit(array $options, ?object $context = null)
Sets `__force__` to true and reruns `init()`, to force re-initialization.

    public function isInited(): bool
Returns whether initialization has completed.

### Protected methods

    protected function initOptions(array $options): void
Subclass override point: processes and consumes the merged options. Empty implementation in the base class.

    protected function initContext(object $context): void
Subclass override point: receives and handles the context (usually the owning App). Empty implementation in the base class.

## Related links

- [DuckPhp\Core\ComponentInterface](Core-ComponentInterface.md) — the component interface this class implements
- [DuckPhp\Core\App](Core-App.md) — the owning app instance returned by `context()`
- [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) — the Trait behind the `_()` static entry
