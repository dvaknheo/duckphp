# DuckPhp\Core\ExitException

## Introduction

Used to turn places where "the program should end early" into a **catchable exception** instead of a direct `exit`/`die`: the framework often throws an `ExitException` instead of exiting directly, giving outer layers (output buffering, resource cleanup, test harnesses) a chance to wrap up uniformly.

`ExitException extends DuckPhp\Core\DuckPhpSystemException`, so it also has the capabilities of `DuckPhpSystemException` (and its `ThrowOnTrait`); in addition it provides `Init()` to establish the global constant `__EXIT_EXCEPTION`.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class ExitException extends DuckPhpSystemException`
- Related global constant: `__EXIT_EXCEPTION` (written by Init, its value being this class's name)

## Usage

When the framework layer decides "the request should stop here", it is wrapped as a thrown ExitException so the upper layer can recognize it in a `try…catch` and finish cleanup and stopping. You usually don't use it directly in business code; if you need "exiting is also a controllable object", you can:

```php
// the framework normally initializes it: ExitException::Init(); so __EXIT_EXCEPTION is defined
throw new \DuckPhp\Core\ExitException('stop here', 200);
```

`SystemWrapper`'s `exit` or other wrap-up scenarios can choose to let an outer layer catch the ExitException and do finally.

## Caveats

- Unlike PHP's native `exit`: `ExitException` keeps "ending" at the catchable layer; tests can use it to avoid really terminating the process.
- When `use_exit_exception` is on, the framework brings out the necessary `ExitException::Init()` — see `KernelTrait`/`App`'s `initException` (which does `define('__EXIT_EXCEPTION', ExitException::class)`).

## Methods

### Public methods

    public static function Init()
When `__EXIT_EXCEPTION` is not yet defined, `define`s it as `static::class` (this class's name).

## Related links

- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) — the parent class
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) — wraps the exit semantics so an ExitException can be thrown
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — Init/define at the initException stage
