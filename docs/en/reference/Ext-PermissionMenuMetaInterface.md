# DuckPhp\Ext\PermissionMenuMetaInterface

## Introduction

`PermissionMenuMetaInterface` is the contract interface for "permission menu metadata": a controller implementing it describes its own menu entries in **code** rather than in annotations, for [DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) to take directly when it runs `build()`.

As long as a controller has this interface (more precisely: as long as it has a `__permissionMenuMeta()` method returning a non-null array), [DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) **no longer parses** the `@menu*` annotations on that class — the whole class's menu table is taken over by that method. Use it when the menu must be decided dynamically from runtime state (permissions, configuration, the database); it is more flexible than annotations.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `interface PermissionMenuMetaInterface`
- Its only member is `__permissionMenuMeta()`; it has no constants and no inheritance.

## Usage

```php
use DuckPhp\Ext\PermissionMenuMetaInterface;
use DuckPhp\GlobalAdmin\AdminControllerInterface;

class AdminController implements AdminControllerInterface, PermissionMenuMetaInterface
{
    public function __permissionMenuMeta(): array
    {
        return [
            // menu node: type defaults to 1
            ['name' => 'Dashboard', 'type' => 1, 'url' => 'Admin/index', 'weight' => 5],
            // a name alone is fine: url defaults to null, type to 1, weight to 0
            ['name' => 'Reports'],
            // directory node: type=0, may carry children
            ['name' => 'System', 'type' => 0, 'url' => 'Admin/#'],
        ];
    }

    public function action_index() { }
}
```

## Caveats

1. **What is returned is an array of entries, not a tree of nodes**: each element supports the four keys `name`, `url`, `type`, `weight`, whose defaults are `''`, `null`, `1`, `0` respectively; `PermissionMenu` only normalises them and will not recurse into `children` for you (to group things, use `type=0` entries and arrange the levels yourself, or switch to annotation mode).
2. **The interface is a "suggestion", not a hard requirement**: `PermissionMenu::getClassMenuMeta()` recognises it by duck typing with `method_exists($controller, '__permissionMenuMeta')` — not `implements`ing this interface but having a method of the same name works just as well; implementing it is what makes the contract explicit.
3. **Instantiation deliberately "does not run the constructor"**: `getClassMenuMeta()` creates an instance with `ReflectionClass::newInstanceWithoutConstructor()` and then calls it — **the constructor does not run**, so the method must not depend on properties the constructor initialised (building the menu is a read-only scan and should not trigger the side effects of a controller constructor). By contrast, the command hook of [CommandMetaInterface](Component-CommandMetaInterface.md) uses `new`, so the constructor does run there.
4. **Both an exception and null count as "nothing"**: when the method throws or returns `null`, `PermissionMenu` quietly falls back to annotation mode (it does not throw the exception at the caller).
5. **The interface declares a return of `array`**: returning `null` is only a convention for "fall back to annotation mode" and conflicts with the interface's type declaration, so if you really want to fall back, do not implement this interface (or leave the method without a return type).
6. Unlike the nodes of annotation mode, metadata entries **do not go through** the `\` layering of `@menu_directory` or the `@menu_permission` logic; weight sorting and `weight` removal still apply as before.

## Methods

### Public methods

    public function __permissionMenuMeta(): array
Returns this controller's array of menu entries: each item is `['name' => …, 'url' => …, 'type' => …, 'weight' => …]`, with `url=null`, `type=1` and `weight=0` by default.

## Related links

- [DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) — the consumer (`getClassMenuMeta()` / `processMenuMetaForController()`)
- [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) — the marker interface also needed to appear in the menu
