# DuckPhp\GlobalUser\UserSessionTrait

## Introduction

`UserSessionTrait` is the default implementation of `UserSessionInterface`: combined with a host that provides `get()`/`set()` session access (typically one that also uses `Foundation\SessionTrait`), it gives you the ability to store the current user in the session.

It stores the user array under the key `'user'`, derives the id/name convenience readers from it, and implements logout by writing an empty array.

## Class info

- Namespace: `DuckPhp\GlobalUser`
- Declaration: `trait UserSessionTrait`
- Requires the host to provide: `get(string $key, $default = null)`, `set(string $key, $value)` (e.g. `Foundation\SessionTrait`)

## Usage

```php
namespace MyProject\System;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalUser\UserSessionTrait;

class UserSession
{
    use SessionTrait;      // get/set/unset
    use UserSessionTrait;  // this trait
    // now you have getCurrentUserId()/getCurrentUserName()/getCurrentUser()/setCurrentUser()/unsetCurrentUser()
}
```

## Caveats

- The session key is fixed to `'user'`; the storage prefix comes from the host session implementation (e.g. `session_prefix`).
- `getCurrentUserId()` defaults to `0`, `getCurrentUserName()` to `''`.
- `setCurrentUser($user)` writes the value you pass (on login/registration, write an array containing `id`/`name`).

## Methods

### Public methods

    public function getCurrentUserId()
Reads the current user ID (session `user.id`, default `0`).

    public function getCurrentUserName(): string
Reads the current user name (session `user.name`, default `''`).

    public function getCurrentUser(): array
Reads the current user array (session `user`, default `[]`).

    public function setCurrentUser($user)
Writes the user data into the session.

    public function unsetCurrentUser()
Clears the current user (writes `[]`).

## Related links

- [DuckPhp\GlobalUser\UserSessionInterface](GlobalUser-UserSessionInterface.md) — the interface this trait implements
- [DuckPhp\Foundation\Controller\SessionTrait](Foundation-Controller-SessionTrait.md) — the session base that provides `get`/`set`
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — the component that uses the session
