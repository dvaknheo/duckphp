# DuckPhp\GlobalUser\UserLoginActionInterface

## Introduction

`UserLoginActionInterface` is the "user login action" contract. It is separate from `UserActionInterface` and describes register/login/logout: `register(array $post)`, `login(array $post)` and `logout()`.

The framework's `GlobalUser` implements both `UserActionInterface` and this interface; a project can also implement the login logic as this interface alone and hand it to `GlobalUser::register()/login()/logout()` through the `user_callback_for_login_service` option.

## Class info

- Namespace: `DuckPhp\GlobalUser`
- Declaration: `interface UserLoginActionInterface`
- Implemented by: `DuckPhp\GlobalUser\GlobalUser`

## Usage

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserLoginActionInterface;

class UserLoginAction implements UserLoginActionInterface
{
    public function register(array $post)
    {
        // register and return the user data (it will be written into the session)
        return ['id' => 7, 'name' => 'duck'];
    }
    public function login(array $post)
    {
        // log in and return the user data
    }
    public function logout()
    {
        // log-out cleanup
    }
}
```

## Caveats

- `register()/login()` normally return the "current user data", which the caller writes into the session.
- The method signatures match `UserLoginServiceInterface`; the difference is intent (action layer vs service layer).

## Methods

### Public methods

    public function register(array $post)
Runs the registration (the input is the form/POST data array).

    public function login(array $post)
Runs the login.

    public function logout()
Logs the user out.

## Related links

- [DuckPhp\GlobalUser\UserLoginServiceInterface](GlobalUser-UserLoginServiceInterface.md) — the service-side contract of the same name
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — the user action contract
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — the default implementation
