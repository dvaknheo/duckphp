# DuckPhp\GlobalAdmin\AdminSessionInterface

## Introduction

`AdminSessionInterface` is the "admin session" contract: it defines how the *current admin* is stored in and read back from the session, with five methods — `setCurrentAdmin($admin)`, `unsetCurrentAdmin()`, `getCurrentAdmin()`, `getCurrentAdminName()` and `getCurrentAdminId()`.

It pairs with `AdminSessionTrait` (the default implementation, built on the host's `get()`/`set()` session access); `GlobalAdmin` obtains an implementation of this interface through the `admin_callback_for_session` option and reads the current admin in "session" style (see `GlobalAdmin::id()/name()`).

## Class info

- Namespace: `DuckPhp\GlobalAdmin`
- Declaration: `interface AdminSessionInterface`

## Usage

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminSessionInterface;
use DuckPhp\GlobalAdmin\AdminSessionTrait;
use DuckPhp\Foundation\Controller\SessionTrait;

class AdminSession implements AdminSessionInterface
{
    use SessionTrait;        // provides get/set/unset (with session_prefix)
    use AdminSessionTrait;   // implements the five methods of this interface
}

// App option: 'admin_callback_for_session' => [AdminSession::class, ???]
```

## Caveats

- As soon as `GlobalAdmin` sees `admin_callback_for_session` configured, `id()/name()` take the session path first instead of `admin_callback_for_id/name`.
- `getCurrentAdmin()` is expected to return the admin array (with keys such as `id`/`name`); `getCurrentAdminId()`/`getCurrentAdminName()` are the convenience readers.

## Methods

### Public methods

    public function setCurrentAdmin($admin)
Writes the current admin (usually after a successful login).

    public function unsetCurrentAdmin()
Clears the current admin (logging out).

    public function getCurrentAdmin()
Reads the current admin (an array).

    public function getCurrentAdminName()
Reads the current admin name.

    public function getCurrentAdminId()
Reads the current admin ID.

## Related links

- [DuckPhp\GlobalAdmin\AdminSessionTrait](GlobalAdmin-AdminSessionTrait.md) — the default implementation
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — uses this interface through `admin_callback_for_session`
