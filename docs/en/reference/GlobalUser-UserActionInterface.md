# DuckPhp\GlobalUser\UserActionInterface

## Introduction

`UserActionInterface` is the contract interface for "user session actions": an implementor (such as `GlobalUser`, or a `UserAction` class inside a project) supplies the framework with the current user's identity queries (`id/name/data`), the site URLs (`urlForRegister/urlForLogin/urlForLogout/urlForHome`), the permission checks (`canAccess/log/batchGetUsernames`) and the service proxy (`service`).

Compared with the administrator side's `AdminActionInterface`, the user side adds a "registration URL (`urlForRegister`)" and "batch-fetch user names (`batchGetUsernames`)", and has no `isSuper`.

## Class info

- Namespace: `DuckPhp\GlobalUser`
- Declaration: `interface UserActionInterface`
- Implemented by: `DuckPhp\GlobalUser\GlobalUser`

## Usage

In a project, making your own "user action implementation" implement this interface is enough to wire it into the framework (usually by pointing a `user_callback_for_*` option at that class):

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserActionInterface;

class UserAction implements UserActionInterface
{
    public function id(bool $check_login = true) { /* returns the current user id */ }
    public function name(bool $check_login = true): string { /* … */ }
    public function data(bool $check_login = true): array { /* … */ }
    public function service() { /* returns the cross-Phase user service (UserServiceInterface) */ }
    public function urlForRegister(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function urlForLogin(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function urlForLogout(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function urlForHome(?string $url_back = null, ?array $ext = null): string { /* … */ }
    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool { /* … */ }
    public function log(string $string, ?string $type = null, array $ext = []) { /* … */ }
    public function batchGetUsernames(array $ids): array { /* … */ }
}
```

## Caveats

- When `$check_login` in `id()/name()/data()` is `true` it usually requires a logged-in user.
- The parameter order of `canAccess()` puts **`$url` first** (`?string $url, ?string $class, ?string $method`). This is a **breaking change**: old code passing arguments in the old `$class, $method, $url` order is silently misaligned. Do check whether every call site has been updated to the new order.

## Methods

### Public methods

    public function id(bool $check_login = true)
Returns the current user ID (`int|string`).

    public function name(bool $check_login = true): string
Returns the current user name.

    public function data(bool $check_login = true): array
Returns the current user's data array.

    public function service()
Returns the cross-Phase user service (`UserServiceInterface`).

    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
The registration page URL.

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
The login page URL.

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
The logout URL.

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
The in-site home URL.

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
Tells whether the current user may access the given URL/controller class/method (by default the current route). Note the parameter order: url comes first.

    public function log(string $string, ?string $type = null, array $ext = [])
Records one user operation log entry.

    public function batchGetUsernames(array $ids): array
Fetches user names in batch by ID (returning a map of id => user name).

## Related links

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — the default implementation of this interface
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — the user service interface
