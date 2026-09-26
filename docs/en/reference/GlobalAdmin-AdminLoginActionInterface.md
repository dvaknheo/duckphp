# DuckPhp\GlobalAdmin\AdminLoginActionInterface

## Introduction

`AdminLoginActionInterface` is the "admin login action" contract. It is separate from `AdminActionInterface` and describes only the login/logout actions: `login(array $post)` and `logout()`.

The framework's `GlobalAdmin` implements both `AdminActionInterface` and this interface; a project can also implement the login logic as this interface alone (e.g. an `AdminLoginAction`) and hand it to `GlobalAdmin::login()/logout()` through the `admin_callback_for_login_service` option.

## Class info

- Namespace: `DuckPhp\GlobalAdmin`
- Declaration: `interface AdminLoginActionInterface`
- Implemented by: `DuckPhp\GlobalAdmin\GlobalAdmin`

## Usage

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminLoginActionInterface;

class AdminLoginAction implements AdminLoginActionInterface
{
    public function login(array $post)
    {
        // verify the account and password; return the admin data (it will be written into the session)
        return ['id' => 1, 'name' => 'root'];
    }
    public function logout()
    {
        // optional cleanup logic
    }
}
```

> You may instead implement `AdminLoginServiceInterface` (same `login/logout` methods) as the service-side implementation and attach it via `admin_callback_for_login_service`.

## Caveats

- `login(array $post)` normally returns the "current admin data", which the caller (e.g. `GlobalAdmin::login()`) writes into the session.
- `AdminActionInterface` keeps commented-out `login`/`logout` placeholders; the real declarations have moved to this interface.

## Methods

### Public methods

    public function login(array $post)
Runs the login (the input is the form/POST data array).

    public function logout()
Logs the admin out.

## Related links

- [DuckPhp\GlobalAdmin\AdminLoginServiceInterface](GlobalAdmin-AdminLoginServiceInterface.md) — the service-side contract of the same name
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — the admin action contract
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — the default implementation
