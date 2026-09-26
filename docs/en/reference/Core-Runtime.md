# DuckPhp\Core\Runtime

A small component for runtime state and output-buffer management: marks "running", captures whether output happened, wraps the output buffer with `run()/clear()`, and changes state on exception.

## Introduction

`Runtime` (`class Runtime extends ComponentBase`) is not a big business component but a "state recorder + output-buffer holder" in the application lifecycle:

- Provides the three read-only states `isRunning/isInException/isOutputed`;
- `run()`: if the option `use_output_buffer` is true, `ob_start()` and remember the ob level before entering;
- `clear()`: at the end, flushes its own buffer down to the entry level, and sets is_running=false, is_outputed=true;
- `onException()`: sets `is_in_exception` true (so upper layers can tell whether they are on the exception-handling path).

Kernel `serve()`/the exception flow wraps "one request's run window" around it.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class Runtime extends ComponentBase`

## Options

`Runtime::$options`

| Option | Default | Description |
|---|---|---|
| `use_output_buffer` | `false` | Whether to enable whole-output buffering; when on, run() starts an ob and clear() flushes it. |

## Usage

```php
use DuckPhp\Core\Runtime;
$r = Runtime::_()->init(['use_output_buffer'=>true]);
$r->run();          // with buffering on -> ob_start
// ... output ...
$r->clear();        // buffer flushed; is_outputed becomes true
Runtime::_()->isRunning();       // false (cleared)
Runtime::_()->isInException();
```

## Configuration example

```php
// app options (the framework usually wraps it this way)
Runtime::_()->init([
    'use_output_buffer' => true,   // enable when "who outputs first" must be gathered by the buffer
]);
```

## Caveats

- `clear()` returns false directly if `run()` has not happened and there is no output level.
- The onException state is used by the Kernel to adjust the path during exceptions.
- clear flushes but does not abandon; to swallow output completely use the ob API elsewhere.

## All options

```php
    public $options = [
        'use_output_buffer' => false,
    ];
```

## Methods

### Public methods

    public function isRunning()
Whether inside the run→clear run window.

    public function isInException()
Whether onException has been entered (exception handling in progress).

    public function isOutputed()
Whether this round has been cleared / output finished.

    public function run()
If use_output_buffer, records the initial ob level then starts buffering; sets is_running=true.

    public function clear()
At the end, flushes its own opened buffer down to the init level; is_running=false; is_outputed=true. Returns false if not running.

    public function onException()
Sets is_in_exception to true.

## Related links

- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — the serve flow wraps "one request's window" and the exception onException state around it
