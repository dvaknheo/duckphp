# DuckPhp\Ext\ThrowOnTrait

## Introduction

`ThrowOnTrait` provides a static conditional-throw method. It is shorthand for "when some condition holds, throw an instance of the current class".

- `ThrowOn($flag, $message, $code)`: when `$flag` is truthy it does `throw new static($message, $code)`; otherwise it does nothing.

Exception classes commonly use it: the exception class itself becomes a carrier that can both be constructed and thrown.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `trait ThrowOnTrait`
- Typical host: `DuckPhp\Core\DuckPhpSystemException`. Any class that `use`s it gets `static::ThrowOn(...)`.

## Usage

```php
class MyException extends \DuckPhp\Core\DuckPhpSystemException
{
}

// simple: throw when the flag holds
MyException::ThrowOn($ok === false, 'invalid token');
```

If `$flag` is truthy it throws; if not it returns silently.

## Caveats

- The thrown instance has the type `static::class` (the class where it is used).
- It assumes the class using the trait is an `Exception` (or a subclass); otherwise `new static(...)` would not accept `($message, $code)`.
- It collapses a condition check into one line, which suits guard-style validation.

## Methods

### Public methods

    public static function ThrowOn($flag, $message, $code = 0)
Throws `new static($message, $code)` when `$flag` is truthy; otherwise it just returns (no side effects).

## Related links

- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) — the host exception base class
