# DuckPhp\GlobalUser\UserControllerInterface

## Introduction

`UserControllerInterface` is an **empty marker interface** (it declares no methods). A project's "front-end logged-in user controller" base class `implements` it to mark itself as belonging to the user area (controllers that require a login), so the framework can recognise and constrain them by interface.

## Class info

- Namespace: `DuckPhp\GlobalUser`
- Declaration: `interface UserControllerInterface` (no methods)

## Usage

```php
namespace MyProject\Controller;

use DuckPhp\GlobalUser\UserControllerInterface;

class UserBase implements UserControllerInterface
{
    // Mark it with implements: every controller extending this class counts as a "user controller"
}
```

## Caveats

- This interface provides no methods; the user's session and permission capabilities come from `UserActionInterface` and `UserServiceInterface`.
- `Foundation\Controller\UserControllerBase` is organised this way (details in the Foundation pages).

## Methods

This interface is an empty marker interface and declares no methods.

## Related links

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — user action interface
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — the user component implementation
