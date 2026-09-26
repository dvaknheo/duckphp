# DuckPhp\Component\PhaseProxy

Cross-phase object proxy: wraps an object so that calling it automatically switches to the phase it belongs to, executes, and restores the previous phase.

## Introduction

`PhaseProxy` wraps an object that is "external / needed by a non-default phase", so that before a method call it runs `App::Phase(target phase)`, and after the call switches back to the caller's phase:

- The constructor takes `$phase` and `$overriding` (an object or a class name, usually another instance of a remote App);
- `CreatePhaseProxy($phase,$overriding)` is a static convenience; an empty phase means the current one;
- `__call` switches phase, invokes the inner object's method, then restores;
- `self()` returns the underlying object; `phase()` reads/writes the target phase.

Typical use: DuckPhp's providers wrap GlobalAdmin/GlobalUser in a PhaseProxy, so a clean instance can be executed across phases (e.g. assigning a phase to the user provider).

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class PhaseProxy`
- Related: switches phase via App::Phase (PhaseContainer).

## Usage

```php
use DuckPhp\Component\PhaseProxy;

// wrap the target object; with phase='myapp' calls run across phases
$proxy = PhaseProxy::CreatePhaseProxy('myapp', $someService);
$proxy->doSomething($arg);      // current phase temporarily switches to myapp, then restores
PhaseProxy::self();             // no param
$proxy->phase('other');
```

## Caveats

- `overriding` may be a class name that gets `new`ed; instantiation is lazy (on first call).
- `__call` relies on `App::Phase(...)`/restore; the restore must happen only after the call really ends.
- This is mostly for providers to use locally — business code rarely needs to `new` one itself.

## Methods

    public function __construct($phase, $overriding)
Set phase/overriding.

    public static function CreatePhaseProxy($phase, $overriding)
An omitted phase falls back to the current one (App::Phase()); returns new static.

    protected function getObjectForPhaseProxy(): object
Lazily materializes `overriding` (objects are used as-is; class names get `new`ed).

    public function __call($method, $args)
Switch phase → call method with args on the underlying object → restore, returning its result.

    public function self(): object
Returns the underlying object (triggers materialization).

    public function phase($new = null)
Read/write the target phase.

## Related links

- [DuckPhp\Core\App Phase](Core-KernelTrait.md)
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md), [DuckPhp\GlobalAdmin\GlobalAdmin] (providers also use PhaseProxy)
