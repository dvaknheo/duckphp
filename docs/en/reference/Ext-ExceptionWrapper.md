# DuckPhp\Ext\ExceptionWrapper

## Introduction

`ExceptionWrapper` is an "exception-safe call wrapper": once you wrap an object into this component, any method call made on it (through the magic `__call`) is wrapped in try/catch — on success it returns the call result; on failure it hands the `\Exception` object back to the caller as the return value (instead of throwing it).

Commonly used for "calling a third-party / exception-prone object while treating exceptions as return values".

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class ExceptionWrapper extends DuckPhp\Core\ComponentBase`

## Usage

```php
use DuckPhp\Ext\ExceptionWrapper;

$safe = ExceptionWrapper::Wrap($httpClient);
$ret  = $safe->request('https://…');   // ok → result; throws → returns the $ex object

$obj = ExceptionWrapper::Release();     // takes back the wrapped object and clears it
```

## Caveats

- `Wrap($object)` (static) is equivalent to `doWrap`; `Release()` (static) is equivalent to `doRelease` (returns the wrapped object and empties the internal reference).
- Only `\Exception` is caught (not `\Error` or non-Exception `\Throwable`).
- The current object is held through the `static::_()` singleton; only one object is wrapped at a time — instantiate separately if you need to wrap several in parallel.

## Methods

### Public methods

    public static function Wrap($object)
Static: hands `$object` to the current instance for wrapping.

    public static function Release()
Static: takes back the wrapped object and clears it.

    public function doWrap($object): self
Stores the object to wrap and returns itself.

    public function doRelease(): ?object
Returns the wrapped object and empties the internal reference (`null` when none).

    public function __call(string $method, array $args)
Proxies the call to the wrapped object's method; when a `\Exception` is thrown, returns that exception object.

## Related links

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — the component base class
