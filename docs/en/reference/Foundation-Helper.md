# DuckPhp\Foundation\Helper

## Introduction

`Foundation\Helper` is the "union of the four Helper layers" facade: it **declares not a single Helper method of its own**, but uses `__callStatic` to dispatch the call to the **first** of the four layer Helpers that declares that method, in this fixed lookup order:

1. `DuckPhp\Foundation\System\SystemHelper`
2. `DuckPhp\Foundation\Controller\ControllerHelper`
3. `DuckPhp\Foundation\Business\BusinessHelper`
4. `DuckPhp\Foundation\Model\ModelHelper`

Use it when you want "one import, all four layers' capabilities"; when you only want one layer's capabilities, use that layer's class directly (see "Related links").

## Class info

- Namespace: `DuckPhp\Foundation`
- Declaration: `class Helper`
- Traits used: none (the methods are dispatched by `__callStatic`)
- Event constants: none (the 10 `$EVENT_*` static properties live on the Business/Controller layer Helpers, see "Caveats")
- The source also carries **96 `@method static …` annotations** (grouped by the four classes above, `---- resolved from Foundation\<layer>\<layer>Helper (n) ----`), for IDE / static-analysis visibility only — they are **not real methods**

## Usage

```php
use DuckPhp\Foundation\Helper;

Helper::Setting('app_name');                 // → Controller\ControllerHelper::Setting()
Helper::DbForRead()->fetch($sql);            // → Model\ModelHelper::DbForRead()
Helper::POST('name');                        // → Controller\ControllerHelper::POST()
Helper::addRouteHook($cb, 'prepend-inner');  // → System\SystemHelper::addRouteHook()
Helper::ThrowOn(!$user, 'please log in first');   // → System\SystemHelper::ThrowOn() (Project semantics)
```

## Caveats

### Who wins among the 12 cross-layer duplicate method names

Because the order above is "first hit wins":

| Duplicate name | Actually dispatched to | Note |
|---|---|---|
| Setting / AppOptions / Config / XpCall | `Controller\ControllerHelper` | System does not declare it → Controller is hit first; equivalent to the Business version (both forward to App/Configer/CoreHelper) |
| FireGlobalEvent / OnGlobalEvent | `System\SystemHelper` | System is hit first; the three layers' implementations are identical (`GlobalEvent::_()->fire()`) |
| ThrowOn | `System\SystemHelper` | **The only duplicate whose semantics differ**: the System version is the Project version (`CoreHelper::_ProjectThrowOn`), while Business/Controller each have their own |
| header / setcookie / exit | `System\SystemHelper` | System is hit first; byte-for-byte identical to the Controller version (both go through `SystemWrapper`) |
| AdminService / UserService | `Controller\ControllerHelper` | System does not declare them → Controller is hit first |

> History: when the early version composed "four traits + `insteadof`", the `Setting` group was awarded to Business and the `header` group to Controller; after the switch to `__callStatic` order-based resolution the winners changed, but those 8 methods are **implementationally equivalent** between the competing layers, and the only one with different semantics, `ThrowOn`, resolves to the System (Project) version either way. This table is pinned by `tests/Foundation/HelperTest.php`; changing the dispatch order turns the test red.

### Other easy mistakes

- **Reflection cannot see the methods**: `method_exists(\DuckPhp\Foundation\Helper::class, 'Db')` is `false`, and `new ReflectionMethod(...)` throws; to enumerate the available methods, look at the `@method` annotations in the source or at the four layer Helpers.
- **Undefined methods**: `__callStatic` does `trigger_error("Call to undefined method …", E_USER_ERROR)`.
- The union class **no longer carries** the 10 `$EVENT_*` static properties (the early version got them free from a trait): 4 are on `Business\BusinessHelper` and 6 on `Controller\ControllerHelper`.
- `DuckPhpAllInOne` uses the same dispatch (with the same order), see [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md).

## Methods

### Public methods

    public static function __callStatic($method, $args)
Looks for the first layer Helper where `method_exists` is true in the order System → Controller → Business → Model and forwards the call; if none has it, `trigger_error(..., E_USER_ERROR)`.

> The signatures and descriptions of the 96 dispatchable methods are on the four layer-Helper pages (this class does not repeat them).

## Related links

- [DuckPhp\Foundation\System\SystemHelper](Foundation-System-SystemHelper.md) — the application/wiring layer helper
- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — the controller layer helper
- [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) — the business layer helper
- [DuckPhp\Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) — the data layer helper (a thin shell class)
- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — the data layer helper trait (shared by `Model\Base` and `Model\ModelHelper`)
- [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — the "single-class application" version of the same union dispatch
