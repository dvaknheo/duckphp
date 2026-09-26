# DuckPhp\Core\DuckPhpSystemException

## Introduction

The general exception base class thrown by the system, carrying ThrowOn capability.

`DuckPhpSystemException extends Exception` (the PHP standard exception), and uses `ThrowOnTrait` to provide the static "throw when satisfied" entry. The framework uses its subclasses in various places to throw exceptions describing problems (such as "duplicate Phase name", "cannot init the base class", etc.).

> ⚠️ **Only for framework-internal system-level errors; external/business exceptions must not extend it.**
> The criterion should be "is this a problem of the framework itself" vs "a business/permission problem of the project":
> - framework-internal mechanism errors (Phase name conflict, initing the base class directly, missing provider…) → throw `DuckPhpSystemException` (or a framework-internal subclass);
> - project-side business/permission/login exceptions → define your own by extending `\Exception` (the framework's early `AdminException` / `UserException` were this pattern; **these two classes have been removed from the source**: login/permission semantics now use the `EXCEPTION_CODE_*` / `EXCEPTION_MESSAGE_*` constants on `DuckPhp\GlobalAdmin\Admin` / `DuckPhp\GlobalUser\User`; not-logged-in is handled by `throwLoginOn()` via 302 / Ajax JSON / a custom callback followed by `exit()`).
>
> The reason: catching `DuckPhpSystemException` is the fallback signal of "the framework is broken"; mixing business exceptions in leaves the upper layer unable to tell "should prompt the user" from "should report a fault".
> You don't need to extend this class to get the `ThrowOn()` guard-style throwing — just `use DuckPhp\Ext\ThrowOnTrait;` in your own exception class.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class DuckPhpSystemException extends Exception`
- Trait used: `DuckPhp\Ext\ThrowOnTrait`

> **Draft constants not yet enabled**: the source also has a batch of constants commented out with `//` (`E_NO_INIT_BASE_CLASS`, `E_Command_Not_Found_In_All`, `E_X`, `E_X2`, `E_X4`, `E_C5`, `E_A1`, `E_A2`), which are not-yet-enabled design drafts, **not listed as official constants** — do not use them in project code.

## Usage

```php
use DuckPhp\Core\DuckPhpSystemException;

// guard-style: throw when not satisfied
DuckPhpSystemException::ThrowOn($user == null, 'not logged in');
```

To define **your own business exception** in a project, extend `\Exception` (not this class), and `use ThrowOnTrait` if you want guard-style throwing:

```php
use DuckPhp\Ext\ThrowOnTrait;

class MyBizException extends \Exception
{
    use ThrowOnTrait;
}
```

### As the base class for "exit as an exception"
`DuckPhp\Core\ExitException extends DuckPhpSystemException`, serving the `__EXIT_EXCEPTION` semantics (interrupting by throwing ExitException instead of a real exit). See the `use_exit_exception` description in `Core-KernelTrait`.

## Methods

This class **declares no methods of its own**; the available capabilities come from the following sources:

- The static throwing utility `ThrowOn(...)`: from `use DuckPhp\Ext\ThrowOnTrait` — when the first argument is true, `throw new static($message,$code)`; see [Core-ThrowOnTrait](Ext-ThrowOnTrait.md).
- Standard exception capabilities: from PHP's built-in `Exception` (`getMessage()`, `getCode()`, `getPrevious()`, `getLine()`, `getFile()`, `getTrace()`, `__toString()`, etc.), all available.

## Related links

- [DuckPhp\Ext\ThrowOnTrait](Ext-ThrowOnTrait.md) — the Trait the static throwing comes from
- [DuckPhp\Core\ExitException](Core-ExitException.md) — its distinctive subclass (exit semantics)
