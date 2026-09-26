# DuckPhp\Ext\StaticReplacer

## Introduction

`StaticReplacer` can simulate "global variables / function-static variables / class static properties" by moving them into instance storage. It is meant for tests and other situations that need isolation or a controllable replacement of global state. All three methods **return by reference**, so reading and writing feel close to the native structures:

- `_GLOBALS($k, $v)`: simulates `$GLOBALS[$k]`;
- `_STATICS($name, $value)`: a "function-static variable" keyed by the call site (`debug_backtrace`: object/class/method);
- `_CLASS_STATICS($class_name, $var_name)`: reads a class's static property into a local cache (the first read takes the real value through reflection, later ones use the cache).

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class StaticReplacer extends DuckPhp\Core\ComponentBase`

## Usage

```php
use DuckPhp\Ext\StaticReplacer;

$sr = StaticReplacer::_();

// simulate a global variable
$v = &$sr->_GLOBALS('counter');
$v++;

// simulate a function static (the key includes the call site)
$x = &$sr->_STATICS('cache', null);

// read/replace a class static property
$old = &$sr->_CLASS_STATICS(MyClass::class, 'property');
```

## Caveats

- The key of `_STATICS` is derived from `debug_backtrace` (object hash + class + type + function name), so the **same name at different call sites** is a different slot; the `$parent` argument selects the stack frame.
- `_CLASS_STATICS` reads the real value through reflection only on the first call and returns a local copy afterwards — **writing to that copy does not write back to the real class static property** (which makes it good for read isolation).
- The class is an ordinary component instance; whether its state persists across requests depends on the component container's lifetime.

## Methods

### Public methods

    public function &_GLOBALS(string $k, $v = null)
Gets/creates a "global variable" slot by reference.

    public function &_STATICS(string $name, $value = null, int $parent = 0)
Gets/creates a "function-static variable" slot by reference (the key includes the calling context).

    public function &_CLASS_STATICS(string $class_name, string $var_name)
Gets the local cache of a class static property by reference (the first read goes through reflection).

## Related links

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — the component base class
