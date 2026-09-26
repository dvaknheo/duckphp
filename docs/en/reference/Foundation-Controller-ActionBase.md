# DuckPhp\Foundation\Controller\ActionBase

## Introduction

`ActionBase` is the recommended base class (abstract) for a project's "reusable Action" classes. Under DuckPHP's layering conventions, a reusable "action fragment" inside a controller can be pulled out into an Action class; like the controller base `Controller\Base`, `ActionBase` only provides the singleton access from `use SingletonTrait`.

## Class info

- Namespace: `DuckPhp\Foundation\Controller`
- Declaration: `abstract class ActionBase`
- Uses trait: `DuckPhp\Foundation\SingletonTrait`

## Usage

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\ActionBase;

class LoginAction extends ActionBase
{
    public function doLogin($name, $password)
    {
        // the reusable "action" logic
    }
}
// from another controller: LoginAction::_()->doLogin(...)
```

## Caveats

- This class enforces no method signature; whether `do_*` acts as the action prefix is decided by routing options (`Route`'s `controller_prefix_post` and friends).
- The difference from `Controller\Base` is naming and intended use: Base targets the controller body, ActionBase targets reusable actions.

## Methods

This class is an empty abstract base and declares no extra methods (`_()` comes from SingletonTrait).

## Related links

- [DuckPhp\Foundation\Controller\Base](Foundation-Controller-Base.md) — the controller base class
- [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) — where the singleton entry point comes from
