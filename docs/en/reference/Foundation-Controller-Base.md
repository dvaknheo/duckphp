# DuckPhp\Foundation\Controller\Base

## Introduction

`Foundation\Controller\Base` is the recommended base class for a project's controller layer (abstract). The source is minimal: it only `use`s `SingletonTrait`, which gives a controller class `ClassName::_()` singleton access.

Your project's controller base should extend it (or extend it directly to share that pattern); controller actions are still written to the routing conventions such as `action_xxx()`. This class binds no input/output capability — that comes from [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md).

## Class info

- Namespace: `DuckPhp\Foundation\Controller`
- Declaration: `abstract class Base`
- Uses trait: `DuckPhp\Foundation\SingletonTrait`

## Usage

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\Base;

abstract class MyControllerBase extends Base
{
    // subclasses can now use static::_() to fetch the instance
}
```

## Caveats

- This is the foundation of the "example project layering": the controller bases that really handle "login / admin / no-permission" are `AdminControllerBase`/`UserControllerBase`; `Controller\Helper` provides the static methods.
- This class defines no routing methods; business actions are supplied by concrete controllers following the framework's routing conventions.

## Methods

This class is an empty abstract base and declares no extra methods (`_()` comes from SingletonTrait → `Core\SingletonExTrait`).

## Related links

- [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) — where the singleton entry point comes from
- [DuckPhp\Foundation\Controller\AdminControllerBase](Foundation-Controller-AdminControllerBase.md) / [UserControllerBase](Foundation-Controller-UserControllerBase.md) — controller bases that require a login
- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — the controller static helpers
