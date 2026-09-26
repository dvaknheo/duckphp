# DuckPhp\Ext\ExtendableStaticCallTrait

## Introduction

`ExtendableStaticCallTrait` gives a class the ability to "extend static methods from outside": register extra static methods with `AssignExtendStaticMethod()` (key → callback); afterwards, when these "undefined" static methods are called on the class, the magic `__callStatic` forwards them to the registered callbacks.

Callbacks can be closures/callable arrays, and two string shorthands are supported: `Class@method` (resolves `Class` through `_()` to its singleton) and `Class->method` (calls after `new Class`).

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `trait ExtendableStaticCallTrait`

## Usage

```php
class Demo
{
    use \DuckPhp\Ext\ExtendableStaticCallTrait;
}

// Register extended static methods
Demo::AssignExtendStaticMethod('foo', function ($a) { return 'foo:'.$a; });
Demo::AssignExtendStaticMethod('bar', MyClass::class.'@baz'); // or 'MyClass->baz'

echo Demo::foo(1);    // foo:1 (via __callStatic → CallExtendStaticMethod)
```

## Caveats

- The registry is stored per `static::class` (each subclass gets its own `$static_methods`).
- `AssignExtendStaticMethod($key, $value)`: when `$key` is an array and `$value === null`, merges in bulk; otherwise assigns a single key.
- The string callback `Class@method` uses `$class::_()` (singleton); `Class->method` uses `new $class()`.
- When a registered callback cannot be found, `call_user_func_array(null, …)` throws — make sure the method you call is registered.

## Methods

### Public methods

    public static function AssignExtendStaticMethod($key, $value = null)
Registers one (or a batch of) extended static method(s).

    public static function GetExtendStaticMethodList()
Returns the extended static method table registered on the current class.

    public static function __callStatic($name, $arguments)
Magic: forwards undefined static calls to `CallExtendStaticMethod`.

### Protected methods

    protected static function CallExtendStaticMethod($name, $arguments)
Looks up the registry by name, resolves the callback (supports the `@`/`->` shorthands), then calls it.

## Related links

- [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md) — a facade base class built on the same static-forwarding idea (related design)
