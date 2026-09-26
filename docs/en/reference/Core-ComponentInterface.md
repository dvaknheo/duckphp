# DuckPhp\Core\ComponentInterface

## Introduction

`ComponentInterface` is the contract interface for DuckPHP components, defining "which public entries a component inside the framework must have":

- a singleton-style static entry `_()`;
- a uniform initialization method `init()`;
- an initialization state query `isInited()`.

In the source, `ComponentBase` declares in comment form (`class ComponentBase // implements ComponentInterface`) that it implements all methods of this interface, so the vast majority of real components do not `implements` this interface directly but extend `ComponentBase`.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `interface ComponentInterface`

## Usage

Ordinary business code does not need to program against this interface directly. To give a class "component shape", just extend `ComponentBase`; if you want to write a component that does not depend on `ComponentBase` at all, implement the three methods of this interface:

```php
namespace My\Component;

use DuckPhp\Core\ComponentInterface;

class MyComponent implements ComponentInterface
{
    public static function _($new_object = null)
    {
        // implement your own singleton or on-demand creation
    }
    public function init(array $options, ?object $context = null)
    {
        return $this; // initialize and return itself
    }
    public function isInited(): bool
    {
        return true;
    }
}
```

## Caveats

- The second parameter of `init()` is uniformly written `$context` everywhere (same name in implementations such as `ComponentBase`; earlier versions of the interface once misspelled it `$contetxt`, since fixed).
- This interface does not include `reInit()`; `reInit()` is a convenience method `ComponentBase` provides on top of the interface.

## Methods

### Public methods

    public static function _($new_object = null)
Singleton-style static entry: returns (or creates) the current component instance; passing an object is generally used to replace/register the instance.

    public function init(array $options, ?object $context = null)
Component initialization entry, returns `$this` by convention for chaining; `$context` is the context (usually the owning App).

    public function isInited(): bool
Returns whether this component has completed initialization.

## Related links

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — the main implementation base class of this interface
