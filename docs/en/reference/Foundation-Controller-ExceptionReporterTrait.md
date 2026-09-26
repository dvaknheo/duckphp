# DuckPhp\Foundation\Controller\ExceptionReporterTrait

An exception-reporter skeleton: it dispatches by the exception's **short class name** to `on{ShortClassName}($ex)`, calls it on a hit, and falls back to the application's default exception handling on a miss.

## Introduction

`ExceptionReporterTrait` is the recommended implementation of a "project exception reporter". It does one thing only: it translates the exception **type** into **your method**:

1. `OnException($ex)` (the static entry point) → `static::_()->_OnException($ex)`;
2. `_OnException()` takes the exception's short class name `Xxx` and assembles the method name `onXxx()`;
3. if that method is callable (an instance method or a static method both work) → call it and return its return value as it is;
4. if it is not callable → `App::_()->_OnDefaultException($ex)`.

The reporter itself does not judge "whether the exception belongs to the project" and does no filtering: what reaches it is decided by [`ExceptionManager`](Core-ExceptionManager.md)'s "exception class → handler" map, and that map is registered by the entry class reading the two options `exception_reporter` / `exception_for_project`. So one reporter can receive several kinds of exception at once — business exceptions, controller exceptions and so on — and dispatch goes purely by short class name.

## Class info

- Namespace: `DuckPhp\Foundation\Controller`
- Declaration: `trait ExceptionReporterTrait`
- Traits used: **none** (this Trait `use`s no Trait at all; a class composing it must provide `_()` itself, see caveat 1)
- Constants: none

## Usage

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\ExceptionReporterTrait;
use DuckPhp\Foundation\SingletonTrait;

class ExceptionAction
{
    use SingletonTrait;                 // provides _(), which OnException() relies on for the instance
    use ExceptionReporterTrait;

    public function onBusinessException($ex)   // MyProject\System\BusinessException
    {
        Helper::ShowJson(['error' => $ex->getMessage()]);
    }
    public function onControllerException($ex) // MyProject\System\ControllerException
    {
        Helper::Show302('login');
    }
}
```

Enabling it in the entry class (the value must be **callable**; a bare class name is not callable and throws at startup):

```php
// MyProject\System\App
public $options = [
    'exception_reporter'    => [ExceptionAction::class, 'OnException'],
    'exception_for_project' => ProjectException::class,   // the reporter takes over this class and its subclasses
];
```

## Caveats

1. **The composing class must carry `_()` itself**. `OnException()` is implemented as `static::_()->_OnException($ex)`, and this Trait `use`s no singleton Trait, so the reporter class has to `use DuckPhp\Foundation\SingletonTrait` itself (or extend a `Controller\Base` that already composes it). A class that "only uses this Trait" reports `Call to undefined method …::_()` **at the moment the exception is really thrown** — it cannot be seen at startup, since that step only checks `is_callable()`.
2. The method name = `on` + the exception's **short class name** (`MyProject\System\BusinessException` → `onBusinessException()`); it has nothing to do with the exception's namespace, nor with the `exception_for_*` options.
3. Both static and instance methods can be hit: the test is `is_callable([$this, $method])`, and a static method is callable on an instance as well.
4. **A method-name collision goes to the fallback**: PHP method names are case-insensitive, so for an exception class called `Exception` the assembled `onException` differs from `OnException` only in case → the recursion guard is hit and it goes straight to `App::_()->_OnDefaultException($ex)` (the same holds for `_OnException`). To funnel everything when "no `onXxx()` was hit", do not count on adding `onException()`; override `App::_OnDefaultException()` instead.
5. **The old interface is gone**: early versions judged "is this a project exception" from `App::options['namespace']` and offered the overridable `defaultException()` / `defaultSystemException()`. Neither method exists any more, the namespace no longer takes part in the judgement, and the fallback is fixed at `App::_()->_OnDefaultException()` (following the old documentation gives a straight `Call to undefined method`).
6. Registration happens when the entry class is init'ed (`DuckPhp::initComponentsOfInner()`): if `exception_reporter` fails `is_callable()` it throws `DuckPhpSystemException("'exception_reporter' config error!:…")`; `exception_for_project` (default `\Exception::class`) is the target base class for the assign, and subclasses are hit as well (so `BusinessException extends ProjectException` also reaches the reporter).

## Methods

### Public methods

    public static function OnException(\Throwable $ex)
The static entry point: forwards to `static::_()->_OnException($ex)` and returns the reporter method's return value.

    public function _OnException($ex)
The instance-side entry point: assembles `on{short class name}()` from the exception's short class name, runs it and returns its return value when callable, otherwise `App::_()->_OnDefaultException($ex)`.

## Related links

- [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) — provides `_()`, usually needed when composing the reporter
- [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) — the consumer of `exception_reporter` (the exception class → handler map)
- [DuckPhp\Core\App](Core-App.md) — `_OnDefaultException()` and the source of the `exception_reporter` / `exception_for_project` options
- [DuckPhp\DuckPhp](DuckPhp.md) — the assembly point that reads the options at init and assigns them
- guide: [exception](../guide/exception.md), [lifecycle](../guide/lifecycle.md)
