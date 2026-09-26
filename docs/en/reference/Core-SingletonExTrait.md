# DuckPhp\Core\SingletonExTrait

## Introduction

`SingletonExTrait` is the common source of "singleton-style access" in DuckPHP. Via `use SingletonExTrait`, a class directly gains a static entry like `ClassName::_()`, managed centrally by `PhaseContainer`, which returns that class's instance (in the current phase).

Unlike the classic "static singleton property": **instance ownership and management are handed entirely to `PhaseContainer`**, and passing `$object` to register/replace the instance is supported. Since the phase container separates instances by "phase + class name", the same class can hold different objects under different phases — the basis for independent instances in child apps / multi-tenancy.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `trait SingletonExTrait`
- Users: `DuckPhp\Core\ComponentBase` (most components get `_()` through it); the `Helper` traits also pull it in to expose the `_()` convenience outward.

## Usage

### Giving a class the `_()` entry

```php
use DuckPhp\Core\SingletonExTrait;

class MyService
{
    use SingletonExTrait;
    // ... business ...
}

$svc = MyService::_();          // get the instance in the current phase (created and registered if absent)
```
Inside the framework the return actually goes through `PhaseContainer::GetObject(static::class, $object)`; you rarely need to new yourself.

### Registering an already-built instance

```php
$existing = new MyService;
MyService::_($existing);        // returns (and registers $existing as the current-phase instance) $existing
```
This replaces/presets a component instance (often used in tests or when the framework swaps a component implementation).

## Caveats

- This is where the static entry lives; for "mutable singleton / decoupled from the container", this trait is only a shell — the real semantics are decided by `PhaseContainer` (lookup order: current phase → shared/parent container → auto-create).
- With `$object` non-null, the passed object is registered and the same object returned, "overriding" the previous instance instead of creating one.
- No conflict with PHP built-in keywords/other singletons; can be `use`d by many classes.

## Methods

### Public methods

    public static function _($object = null)
Singleton entry: returns (no argument) or registers then returns (object passed) the current-phase instance of `static::class`; delegates internally to `PhaseContainer::GetObject()`.

## Related links

- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) — where instance/singleton lookup and creation really happen
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — the component base class that pulls in this trait
