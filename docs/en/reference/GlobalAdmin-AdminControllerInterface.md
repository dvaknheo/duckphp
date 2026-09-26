# DuckPhp\GlobalAdmin\AdminControllerInterface

## Introduction

`AdminControllerInterface` is an **empty marker interface** (it declares no methods). A project's "back-office admin controller" base class `implements` it to mark itself as belonging to the admin area, so the framework can recognise and constrain admin controllers by interface.

## Class info

- Namespace: `DuckPhp\GlobalAdmin`
- Declaration: `interface AdminControllerInterface` (no methods)

## Usage

```php
namespace MyProject\Controller;

use DuckPhp\GlobalAdmin\AdminControllerInterface;

class AdminBase implements AdminControllerInterface
{
    // Mark it with implements: every controller extending this class counts as an "admin controller"
}
```

## Caveats

- This interface provides no methods; the admin's session and permission capabilities come from `AdminActionInterface` and `AdminServiceInterface`.
- `Foundation\Controller\AdminControllerBase` is organised this way (details in the Foundation pages).

## Methods

This interface is an empty marker interface and declares no methods.

## Related links

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — admin action interface
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — the admin component implementation
