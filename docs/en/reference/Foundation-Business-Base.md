# DuckPhp\Foundation\Business\Base

## Introduction

`Business\Base` is the recommended base class (abstract) for a project's Business (business) layer. The source is minimal: `use SingletonTrait`, which gives business classes `ClassName::_()` singleton access.

The business layer is meant to be "pure logic, handling the request context without state"; this base class binds no framework API — the settings/cache/validation a business class needs come from [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md).

## Class info

- Namespace: `DuckPhp\Foundation\Business`
- Declaration: `abstract class Base`
- Uses trait: `DuckPhp\Foundation\SingletonTrait`

## Usage

```php
namespace MyProject\Business;

use DuckPhp\Foundation\Business\Base;

class UserBusiness extends Base
{
    public function getList()
    {
        // business logic; may call the Model
    }
}
// from a controller: UserBusiness::_()->getList()
```

## Caveats

- This class defines no business methods; subclasses provide them.
- For the layering rules see the violation matrix in `docs/zh/guide/project-structure.md` §5: Business **may reference** definitions in the System layer (project exception classes, configuration) and the Model layer, but it must not touch the request context, and it must **not call the System layer's wiring actions** (registering routes/events/commands).

## Methods

This class is an empty abstract base and declares no extra methods (`_()` comes from SingletonTrait).

## Related links

- [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) — the business static helpers
- [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) — where the singleton entry point comes from
