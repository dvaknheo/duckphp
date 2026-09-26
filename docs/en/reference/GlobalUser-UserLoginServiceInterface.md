# DuckPhp\GlobalUser\UserLoginServiceInterface

## Introduction

`UserLoginServiceInterface` is the "user login service" contract: the service side of register/login/logout — `register(array $post)`, `login(array $post)`, `logout($id)`. It is the service-side counterpart of `UserLoginActionInterface`, usually implemented by a project's Service class and attached through `GlobalUser`'s `user_callback_for_login_service` option.

The user side has one method more than the admin side: `register(array $post)`.

## Class info

- Namespace: `DuckPhp\GlobalUser`
- Declaration: `interface UserLoginServiceInterface`

## Usage

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserLoginServiceInterface;

class UserLoginService implements UserLoginServiceInterface
{
    public function register(array $post) { /* run the registration and return the user data */ }
    public function login(array $post)    { /* run the login and return the user data */ }
    public function logout($id)          { /* log out and clean up; $id is the user ID */ }
}
```

## Caveats

- This interface only declares register/login/logout; permission checks, logging and bulk username lookups still belong to `UserServiceInterface`.
- `logout($id)`'s `$id` is `int|string` and names the user to log out.

## Methods

### Public methods

    public function register(array $post)
Runs the registration and returns the user data.

    public function login(array $post)
Runs the login and returns the user data.

    public function logout($id)
Logs the user out; `$id` is the ID of the user to log out (`int|string`).

## Related links

- [DuckPhp\GlobalUser\UserLoginActionInterface](GlobalUser-UserLoginActionInterface.md) — the action-side contract of the same name
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — permission / logging / bulk-username service contract
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — the user side that consumes it
