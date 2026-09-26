# DuckPhp\GlobalAdmin\AdminSessionTrait

## Introduction

`AdminSessionTrait` is the default implementation of `AdminSessionInterface`: use it together with a host that provides `get()`/`set()` session access (typically one that also uses `Foundation\SessionTrait`) and you get the ability to store the current admin in the session.

It stores the admin array under the key `'admin'`, derives the id/name convenience readers from it, and implements "drop the current admin" by writing an empty array.

## Class info

- Namespace: `DuckPhp\GlobalAdmin`
- Declaration: `trait AdminSessionTrait`
- Requires the host to provide: `get(string $key, $default = null)`, `set(string $key, $value)` (e.g. `Foundation\SessionTrait`)

## Usage

```php
namespace MyProject\System;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalAdmin\AdminSessionTrait;

class AdminSession
{
    use SessionTrait;        // get/set/unset (with session_prefix)
    use AdminSessionTrait;   // this trait

    // now you have: getCurrentAdminId()/getCurrentAdminName()/getCurrentAdmin()/setCurrentAdmin()/unsetCurrentAdmin()
}
```

## Caveats

- The session key is fixed to `'admin'` (`$this->get('admin', [])`); the actual storage prefix comes from the host session implementation (e.g. `session_prefix`).
- `getCurrentAdminId()` defaults to `0` and `getCurrentAdminName()` to `''`, which makes emptiness checks easy.
- `setCurrentAdmin($admin)` writes the value you pass (on login, write an array containing `id`/`name`).

## Methods

### Public methods

    public function getCurrentAdminId()
Reads the current admin ID (session `admin.id`, default `0`).

    public function getCurrentAdminName(): string
Reads the current admin name (session `admin.name`, default `''`).

    public function getCurrentAdmin(): array
Reads the current admin array (session `admin`, default `[]`).

    public function setCurrentAdmin($admin)
Writes the admin data into the session.

    public function unsetCurrentAdmin()
Clears the current admin (writes `[]`).

## Related links

- [DuckPhp\GlobalAdmin\AdminSessionInterface](GlobalAdmin-AdminSessionInterface.md) — the interface this trait implements
- [DuckPhp\Foundation\Controller\SessionTrait](Foundation-Controller-SessionTrait.md) — the session base that provides `get`/`set`
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — the component that uses the session
