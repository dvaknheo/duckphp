# DuckPhp\Foundation\SingletonTrait

## Introduction

`SingletonTrait` is the Foundation layer's **thin wrapper** around `Core\SingletonExTrait`: it exposes the framework core's singleton entry point once more under a `Foundation` name, so the Foundation base classes (`Controller\Base`, `Business\Base`, `Model\Base`, …) can pull it in uniformly.

Its implementation is exactly `use DuckPhp\Core\SingletonExTrait as Singleton; use Singleton;` — the semantics are identical to `Core\SingletonExTrait` (`ClassName::_()` goes through `PhaseContainer::GetObject`).

## Class info

- Namespace: `DuckPhp\Foundation`
- Declaration: `trait SingletonTrait`
- Uses trait: `DuckPhp\Core\SingletonExTrait` (aliased as `Singleton`)

## Usage

```php
namespace MyProject;

use DuckPhp\Foundation\SingletonTrait;

class AnyClass
{
    use SingletonTrait;
}
$obj = AnyClass::_(); // fetch the instance the singleton way
```

## Caveats

- This trait only forwards; it holds no extra state. If you already use `Core\SingletonExTrait`, it is functionally equivalent.
- Why does it exist? So code in the Foundation layer does not depend on the `Core` namespace directly — the layering convention is that business classes pull capabilities in through Foundation.

## Methods

This trait declares no methods of its own: `_($object = null)` comes from `Core\SingletonExTrait` (compose it and `ClassName::_()` fetches the instance).

## Related links

- [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) — the actual implementation
- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) — the container that holds singletons
