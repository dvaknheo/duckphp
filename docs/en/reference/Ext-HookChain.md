# DuckPhp\Ext\HookChain

## Introduction

`HookChain` represents a "chain of callbacks": on `__invoke()` the callbacks in the chain run in order, and any callback returning a truthy value interrupts the chain; it can also be used as an array (implements `ArrayAccess`). It is commonly used for the "a group of hooks run by priority, stop on first hit" pattern (used internally by the framework for organizing hooks such as status checks).

`Hook::Hook(&$var, $callable, ...)` is a convenience entry: it merges an existing callback/chain with a new callback into one chain and writes it back to `$var`.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class HookChain implements \ArrayAccess`

## Usage

```php
use DuckPhp\Ext\HookChain;

$chain = new HookChain();
$chain->add(function () { /* 1 */ }, true, true);
$chain->add(function () { return true; }, true, true); // returns true → interrupt
$chain(); // calls in order, stops at true

// Convenience merge:
HookChain::Hook($target, function () { /* … */ });
// whether $target is a callback/array/chain/empty, it is organized into one HookChain
```

## Caveats

- `add($callable, bool $append, bool $once)`: `$append=true` appends to the tail, `false` inserts at the head; with `$once=true` duplicate callbacks are not added.
- `__invoke()` executes one by one and `break`s when a callback returns a "truthy" value.
- `ArrayAccess`: `$chain[$i]` reads/writes elements on the chain; `offsetSet(null,…)` counts as appending.
- `Hook()` static: when `$var` is a `HookChain` it adds directly; when `null` it creates a new chain and adds; for other values (e.g. an existing callback) it puts the old value and the new callback together into a new chain.

## Methods

### Public methods

    public function __construct()
Constructs an empty chain.

    public function __invoke(): void
Runs the callbacks on the chain in order; any truthy return interrupts.

    public static function Hook(&$var, $callable, $append = true, $once = true)
Merges `$callable` with the existing `$var` into one chain and writes it back to `$var`.

    public function add(callable $callable, bool $append, bool $once)
Appends/prepends a callback to the chain (deduped when `once`).

    public function remove(callable $callable): void
Removes the given callback from the chain.

    public function has(callable $callable): bool
Whether the chain contains the given callback.

    public function all(): array
Returns the whole callback chain as an array.

    public function offsetSet($offset, $value): void
Array write (a null offset appends).

    public function offsetExists($offset): bool
Whether the array key exists.

    public function offsetUnset($offset): void
Deletes an array key.

    public function offsetGet($offset)
Gets an array key (returns `null` when missing).

## Related links

- [Chapter 4-13 `Ext\*` extensions](../guide/ext-classes.md) §15 — where this class sits, and why it is off the recommended path
