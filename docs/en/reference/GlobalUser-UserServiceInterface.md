# DuckPhp\GlobalUser\UserServiceInterface

## Introduction

`UserServiceInterface` is the contract for the "user service": it defines the three things the front-end service side has to implement — permission checks (`canAccess`), operation logging (`log`) and bulk username lookups (`batchGetUsernames`). The object returned by `UserActionInterface::service()` implements this interface.

Compared with the admin side's `AdminServiceInterface`: there is no `isSuper`, and there is one extra method, `batchGetUsernames()`.

## Class info

- Namespace: `DuckPhp\GlobalUser`
- Declaration: `interface UserServiceInterface`

## Usage

```php
namespace MyProject\System;

use DuckPhp\GlobalUser\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function canAccess($user_id, ?string $url, string $class, string $method): bool
    {
        // return whether that user may access url/controller/method
    }
    public function log($user_id, string $string, ?string $type = null, array $ext = [])
    {
        // record a user action
    }
    public function batchGetUsernames(array $ids): array
    {
        // return id => user name
    }
}
```

## Caveats

- `canAccess`'s `$user_id` may be `int|string`; `$url` is a nullable URL.
- The parameter order matches `UserActionInterface::canAccess()`: **`$url` comes before `$class`/`$method`**. This is a breaking change: old code that passed arguments as `$class, $method, $url` silently misplaces them — check every call site.
- Questions like "who is the current user" belong to `UserActionInterface::id()/name()/data()`; every method here takes `$user_id` explicitly.

## Methods

### Public methods

    public function canAccess($user_id, ?string $url, string $class, string $method): bool
Decides whether the given user may access a URL / controller class / method. The parameter order matches `UserActionInterface::canAccess()`: url first.

    public function log($user_id, string $string, ?string $type = null, array $ext = [])
Records one action-log entry for the given user.

    public function batchGetUsernames(array $ids): array
Looks up user names by ID in bulk (returns a mapping array).

## Related links

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — the action contract that returns this service interface
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — the user component (including the cross-phase `service()` proxy)
