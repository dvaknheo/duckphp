# DuckPhp\Foundation\Controller\SessionTrait

## Introduction

`SessionTrait` gives the class that composes it **prefixed Session reads and writes**: on first access it calls `session_start()` automatically (through `SystemWrapper`) and uses `App::options['session_prefix']` as the key prefix to isolate the namespace, avoiding collisions with the session keys of other applications/modules.

Implementation points:
- `checkSessionStart()`: skips when already started; otherwise `_session_start()` and reads `session_prefix` from the App options into a cache.
- `get/set/unset`: read/write/delete the session variable for `session_prefix + key` (through `SuperGlobal`).

## Class info

- Namespace: `DuckPhp\Foundation`
- Declaration: `trait SessionTrait`
- Traits used: `DuckPhp\Core\SingletonExTrait`
- Used by: controllers/system classes in a project that need sessions (such as `Controller\Session`)

## Usage

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\SessionTrait;

class Session
{
    use SessionTrait;

    public function remember($name)
    {
        $this->set('name', $name);      // actually writes session_prefix + 'name'
        return $this->get('name');
    }
    public function forget()
    {
        $this->unset('name');
    }
}
```

## Caveats

- All three read/write methods are `protected`, for use inside the composing class (they are not exposed as a static API).
- The Session key prefix comes from the application option `session_prefix`; when it is not configured the prefix is an empty string.
- `session_start` is called through `SystemWrapper`, so a test environment can replace the injection.

## Methods

### Protected methods

    protected function checkSessionStart(): void
Makes sure the session is started and caches `session_prefix` (idempotent).

    protected function get(string $key, $default = null)
Reads a session variable (`session_prefix + $key`), returning `$default` when it is absent.

    protected function set(string $key, $value)
Writes a session variable (`session_prefix + $key`).

    protected function unset(string $key)
Deletes a session variable (`session_prefix + $key`).

## Related links

- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) — the low-level wrapper for session reads and writes
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) — the replaceable implementation of session_start
