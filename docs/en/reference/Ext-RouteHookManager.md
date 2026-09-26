# DuckPhp\Ext\RouteHookManager

## Introduction

`RouteHookManager` is a manager for a route-hook list: it binds `Route`'s `pre_run_hook_list` or `post_run_hook_list` to itself by reference (`attachPreRun`/`attachPostRun`), and then uses `append/insertBefore/moveBefore/removeAll/setHookList/getHookList` and friends to fine-tune the hook order (instead of every `addRouteHook` only being able to add at one of the two ends).

Used together with: other extensions (such as `MyMiddlewareManager`) attach themselves to the pre-run chain with `RouteHookManager::_()->attachPreRun()->append([…,'Hook'])`.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class RouteHookManager extends DuckPhp\Core\ComponentBase`

## Options

This component adds no options (`$options = []`, it follows the component base class).

## Usage

```php
use DuckPhp\Ext\RouteHookManager;

$mgr = RouteHookManager::_();
$mgr->attachPreRun();                      // bind Route's pre_run list
$mgr->append([MyHook::class, 'run']);      // append
$mgr->insertBefore([A::class, 'run'], [B::class, 'run']);
$mgr->removeAll([B::class, 'run']);

$mgr->attachPostRun();                     // rebind to the post_run list
$mgr->append([AnotherHook::class, 'run']);
```

## Caveats

- After `attachPreRun`/`attachPostRun`, `$this->hook_list` is **a reference** to the corresponding property of `Route`, so later additions, removals and edits show up directly in the route execution chain.
- `moveBefore($new,$old)` = `removeAll($new)` + `insertBefore($new,$old)`; elements are compared with "===".
- `dump()` delegates to `Route::dumpAllRouteHooksAsString()`.

## Methods

### Public methods

    public function attachPreRun(): self
Binds Route's `pre_run_hook_list` to this manager.

    public function attachPostRun(): self
Binds Route's `post_run_hook_list` to this manager.

    public function detach(): void
Unbinds (empties the local hook_list so it no longer points at Route).

    public function getHookList(): array
Returns the current hook list.

    public function setHookList(array $hook_list): void
Sets the whole hook list.

    public function moveBefore($new, $old): self
Moves `$new` to before `$old`.

    public function insertBefore($new, $old): self
Inserts `$new` before `$old`.

    public function removeAll($name): self
Removes every hook equal to `$name`.

    public function append($name): void
Appends a hook to the end of the list.

    public function dump(): string
Outputs the string with all of Route's hook information.

## Related links

- [DuckPhp\Core\Route](Core-Route.md) — the hook host (pre/post_run_hook_list)
- [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md) — an example that uses attachPreRun
