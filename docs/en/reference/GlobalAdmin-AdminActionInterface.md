# DuckPhp\GlobalAdmin\AdminActionInterface

## Introduction

`AdminActionInterface` is the contract interface for "administrator session actions": an implementor (such as `GlobalAdmin`, or an `AdminAction` class inside a project) supplies the framework with the current administrator's identity queries (`id/name/data`), the login-state URLs (`urlForLogin/urlForLogout/urlForHome`), the permission checks (`canAccess/log/isSuper`) and the service proxy (`service`).

It abstracts "who the administrator is and what they may do", so that the Admin controller base classes in the `Foundation` layer and `GlobalAdmin` all program against this interface.

## Class info

- Namespace: `DuckPhp\GlobalAdmin`
- Declaration: `interface AdminActionInterface`
- Implemented by: `DuckPhp\GlobalAdmin\GlobalAdmin`

## Usage

In a project, making your own "administrator action implementation" implement this interface is enough to wire it into the framework (usually by pointing an `admin_callback_for_*` option at that class):

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminAction implements AdminActionInterface
{
    public function id(bool $check_login = true) { /* returns the current administrator id */ }
    public function name(bool $check_login = true): string { /* … */ }
    public function data(bool $check_login = true): array { /* … */ }
    public function service() { /* returns the cross-Phase administrator service (AdminServiceInterface) */ }
    public function urlForLogin(?string $url_back = null): string { /* … */ }
    public function urlForLogout(): string { /* … */ }
    public function urlForHome(): string { /* … */ }
    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool { /* … */ }
    public function log(string $string, ?string $type = null, array $ext = []) { /* … */ }
    public function isSuper(): bool { /* … */ }
}
```

## Caveats

- When `$check_login` in `id()/name()/data()` is `true` it usually requires a logged-in administrator (what happens otherwise is up to the implementation, e.g. throwing).
- The parameter order of `canAccess()` puts **`$url` first** (`?string $url, ?string $class, ?string $method`). This is a **breaking change**: old code passing arguments in the old `$class, $method, $url` order is silently misaligned. Do check whether every call site has been updated to the new order.

## Methods

### Public methods

    public function id(bool $check_login = true)
Returns the current administrator ID (`int|string`).

    public function name(bool $check_login = true): string
Returns the current administrator name.

    public function data(bool $check_login = true): array
Returns the current administrator's data array.

    public function service()
Returns the cross-Phase administrator service (`AdminServiceInterface`).

    public function urlForLogin(?string $url_back = null): string
The login page URL; `$url_back` is where to return after logging in.

    public function urlForLogout(): string
The logout URL.

    public function urlForHome(): string
The back-office home URL.

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
Tells whether the current administrator may access the given URL/controller class/method (by default the current route). Note the parameter order: url comes first.

    public function log(string $string, ?string $type = null, array $ext = [])
Records one administrator operation log entry.

    public function isSuper(): bool
Whether the current administrator is a super administrator.

## Related links

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — the default implementation of this interface
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — the administrator service interface
