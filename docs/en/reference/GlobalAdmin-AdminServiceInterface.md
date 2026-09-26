# DuckPhp\GlobalAdmin\AdminServiceInterface

## Introduction

`AdminServiceInterface` is the contract for the "admin service": it defines the three things the back-office service side has to implement — permission checks (`canAccess`), operation logging (`log`) and the super-admin test (`isSuper`). The object returned by `AdminActionInterface::service()` implements this interface.

In practice a project usually turns authorisation and logging into a replaceable Service class and attaches it to `GlobalAdmin` through an option (such as `admin_callback_for_local_service`).

## Class info

- Namespace: `DuckPhp\GlobalAdmin`
- Declaration: `interface AdminServiceInterface`

## Usage

```php
namespace MyProject\System;

use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminService implements AdminServiceInterface
{
    public function canAccess($admin_id, ?string $url, string $class, string $method): bool
    {
        // return whether that admin may access url/controller/method
    }
    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
    {
        // record an admin action
    }
    public function isSuper($admin_id): bool
    {
        // is this a super admin
    }
}
```

## Caveats

- `canAccess`'s `$admin_id` may be `int|string`; `$url` is a nullable URL.
- The parameter order matches `AdminActionInterface::canAccess()`: **`$url` comes before `$class`/`$method`**. This is a breaking change: old code that passed arguments as `$class, $method, $url` silently misplaces them — check every call site.
- This interface does not answer "who is the current admin" — that is the job of `AdminActionInterface::id()/name()/data()`; all three methods here take `$admin_id` explicitly.

## Methods

### Public methods

    public function canAccess($admin_id, ?string $url, string $class, string $method): bool
Decides whether the given admin may access a URL / controller class / method. The parameter order matches `AdminActionInterface::canAccess()`: url first.

    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
Records one action-log entry for the given admin.

    public function isSuper($admin_id): bool
Decides whether the given admin is a super admin.

## Related links

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — the action contract that returns this service interface
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — the admin component (including the cross-phase `service()` proxy)
