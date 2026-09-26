# DuckPhp\GlobalAdmin\AdminLoginServiceInterface

## Introduction

`AdminLoginServiceInterface` is the "admin login service" contract: it describes the service side's login/logout — `login(array $post)` and `logout($id)`. It is the service-side counterpart of `AdminLoginActionInterface`, usually implemented by a project's Service class and attached through `GlobalAdmin`'s `admin_callback_for_login_service` option.

## Class info

- Namespace: `DuckPhp\GlobalAdmin`
- Declaration: `interface AdminLoginServiceInterface`

## Usage

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminLoginServiceInterface;

class AdminLoginService implements AdminLoginServiceInterface
{
    public function login(array $post)
    {
        // verify and return the admin data
    }
    public function logout($id)
    {
        // log out and clean up; $id is the admin ID
    }
}

// App options:
// 'admin_callback_for_login_service' => [AdminLoginService::class, 'login'], // see the GlobalAdmin page
```

## Caveats

- This interface only declares login/logout; permission checks, logging and so on still belong to `AdminServiceInterface`.
- `logout($id)`'s `$id` is `int|string` and names the admin to log out.
- The method signatures match `AdminLoginActionInterface`; the difference is intent (an Action targets the action layer, a Service targets the service layer).

## Methods

### Public methods

    public function login(array $post)
Runs the login and returns the admin data.

    public function logout($id)
Logs the admin out; `$id` is the ID of the admin to log out (`int|string`).

## Related links

- [DuckPhp\GlobalAdmin\AdminLoginActionInterface](GlobalAdmin-AdminLoginActionInterface.md) — the action-side contract of the same name
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — the permission / logging service contract
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — the consumer
