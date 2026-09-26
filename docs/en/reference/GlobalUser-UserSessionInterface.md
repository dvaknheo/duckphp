# DuckPhp\GlobalUser\UserSessionInterface

## Introduction

`UserSessionInterface` is the "user session" contract: it stores and reads back the *current user*, with five methods — `setCurrentUser($user)`, `unsetCurrentUser()`, `getCurrentUser()`, `getCurrentUserName()` and `getCurrentUserId()`.

It pairs with `UserSessionTrait` (the default implementation, built on the host's `get()`/`set()`); `GlobalUser` obtains an implementation of this interface through the `user_callback_for_session` option and reads the current user in "session" style.

## Class info

- Namespace: `DuckPhp\GlobalUser`
- Declaration: `interface UserSessionInterface`

## Usage

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserSessionInterface;
use DuckPhp\GlobalUser\UserSessionTrait;
use DuckPhp\Foundation\Controller\SessionTrait;

class UserSession implements UserSessionInterface
{
    use SessionTrait;      // get/set/unset (with session_prefix)
    use UserSessionTrait;  // implements this interface
}
```

## Caveats

- When `GlobalUser` sees `user_callback_for_session` configured, `id()`/`name()` take the session path first.
- `getCurrentUser()` is expected to return the user array (with `id`/`name` and so on); there are also id/name convenience readers.

## Methods

### Public methods

    public function setCurrentUser($user)
Writes the current user (usually after a successful login or registration).

    public function unsetCurrentUser()
Clears the current user (logging out).

    public function getCurrentUser()
Reads the current user (an array).

    public function getCurrentUserName()
Reads the current user name.

    public function getCurrentUserId()
Reads the current user ID.

## Related links

- [DuckPhp\GlobalUser\UserSessionTrait](GlobalUser-UserSessionTrait.md) — the default implementation
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — uses this interface through `user_callback_for_session`
