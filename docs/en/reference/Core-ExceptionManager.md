# DuckPhp\Core\ExceptionManager

The hub of framework exception/error handling: customizes PHP's `set_error_handler/set_exception_handler`, dispatching errors and exceptions along established routes to "project handler / default handler / debugger".

## Introduction

`ExceptionManager` (`class ExceptionManager extends ComponentBase`) does two things:

- **Takes over PHP errors**: when `handle_all_dev_error` is on, `on_error_handler` acts as the error callback (only the noteworthy levels trigger `dev_error_handler`; the others are converted to `ErrorException`).
- **Takes over exceptions**: when `handle_all_exception` is on, `set_exception_handler(CallException)` runs `_CallException`: if the exception is an `__EXIT_EXCEPTION` it is let through directly; otherwise it looks in `exceptionHandlers` (in reversed registration order) for the first class callback that is_a the exception and executes it; when none match, it finally goes to `default_exception_handler`.

The framework (Core `KernelTrait`) `init`s it during the initialization stage, setting the root App's `OnDefaultException` as `default_exception_handler` and `OnDevErrorHandler` as `dev_error_handler`, so most projects don't have to tune these by hand.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class ExceptionManager extends ComponentBase`

## Options

`ExceptionManager::$options`:

| Option                      | Default | Description                                                                  |
| --------------------------- | ------- | ---------------------------------------------------------------------------- |
| `handle_all_dev_error`      | true    | Whether to take over the PHP error handler (dev path).                        |
| `handle_all_exception`      | true    | Whether to take over the global exception handler.                            |
| `system_exception_handler`  | null    | Custom exception installation callback `function(callable $handler)`, can replace the built-in set_exception_handler (for extreme/cross-runtime use). |
| `handle_exception_on_init`  | true    | Run the takeover immediately at init.                                         |
| `default_exception_handler` | null    | Custom fallback for unmatched ones (usually filled with App::OnDefaultException). |
| `dev_error_handler`         | null    | The dev error callback (usually App::OnDevErrorHandler).                      |

## Usage (usually no manual changes needed)

> Hidden options (not declared in this class's `$options`, but read at init): `exception_reporter` (the project-level "exception reporter class", needs a static `OnException`), `exception_for_project` (the project main exception class name, used as the assign target base class when a reporter is configured). They are usually written in the app entry's options.
```php
use DuckPhp\Core\ExceptionManager;

// pre-assign "its own callback" to an exception family
ExceptionManager::_()->assignExceptionHandler(MyAppException::class, function ($ex) {
    // custom handling
});
```

```php
// proactively sentence an exception by the rules (internal call default/report ... all go through _CallException)
ExceptionManager::CallException($ex);
```

### Global takeover/cleanup

You almost never do this manually in a normal run/clear scenario (the framework handles it). When loading it on your own:

```php
$em = ExceptionManager::_()->init([]);
$em->run();     // install the error/exception handlers
// ...
$em->clear();   // restore them
```

## Configuration example

```php
// in the business app options…
'exception_reporter'   => [\App\ExceptionReporter::class, 'OnException'],  // must be callable; a bare class name is not callable
'exception_for_project'=> \App\BusinessException::class,
```

## Caveats

1. `on_error_handler` throws non-notice-level errors as `ErrorException`; notice/deprecated go to `dev_error_handler`.
2. `_CallException` matching looks for the first is_a hit in **reverse registration order** — the later an assign is declared, the higher its priority (commonly used for "most specific type first").
3. `__EXIT_EXCEPTION` (ExitException) skips dispatching, so "exiting" is not intercepted by mistake.
4. With no `exception_reporter` installed and no subclass match, only default remains; if default is null, the exception ends up unhandled (left to PHP).
5. `clear()` restores the PHP handlers and resets the run flags.

## Methods

### Public methods

    public function init(array $options, ?object $context = null)
After the parent init, options take effect: run() when `handle_exception_on_init`; and if exception_reporter, assigns the default callback for exception_for_project.

    public static function CallException($ex)
Feeds the exception into `_CallException` (shell) for set_exception_handler/catchers.

    public function setDefaultExceptionHandler($default_exception_handler)
Sets the default fallback handler.

    public function assignExceptionHandler($class, $callback = null)
Registration: a single class (string=>callback) or a batch array class=>callback.

    public function setMultiExceptionHandler(array $classes, $callback)
The same callback for multiple exception classes.

    public function on_error_handler($errno, $errstr, $errfile, $errline)
Used by `set_error_handler`; notice/deprecation→dev_error_handler, the rest throw ErrorException; returning true blocks the PHP default.

    public function _CallException($ex)
The dispatch core (see Introduction).

    public function isInited(): bool
Whether initialized.

    public function run()
Installs the error/exception handlers (avoids duplicates), saving the last handlers.

    public function reset()
(placeholder) just returns this (illustrative: preparing to clear the interface; the actual clearing is done by clear).

    public function clear()
restore_error/exception_handler (or system cleanup), resets the is_running/is_inited flags.

### Protected methods

    protected function initOptions(array $options): void
Takes the default/system handlers out of options and stores them in properties.


### (The register-up remains as-is as an example; most paths go through init)

## Related links

- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — assembles it at initialization (default/dev handlers are App's on404 etc.)
- [DuckPhp\Core\App](Core-App.md) — `OnDefaultException`/`OnDevErrorHandler`
- [DuckPhp\Core\ExitException](Core-ExitException.md) — the type let through directly by _CallException
- guide: exception.md
