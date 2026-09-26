# DuckPhp\Component\Cache

Cache component abstraction / default empty implementation: declares the skeleton of a PSR-16-like (`Psr\SimpleCache\CacheInterface`-style docblock, not an actual `implements`) set of cache operations; without a real replacement it is a "stores nothing" empty implementation.

## Introduction

`Cache extends ComponentBase` is the fallback for the whole tree's cache interface. Using `Cache::_()` directly is a **no-op** (get→default; set/delete/has→false; clear does nothing). The point is to let code target a stable cache API without pulling in an uncontrolled default store.

Real caching comes from a concrete cache wrapper (such as `Component\RedisCache`) or your own implementation of the same interface: swap it into `Component\Cache`'s place wholesale, and business code keeps using one consistent `Cache` interface + methods at the call layer.

Think of it as "the contract of the cache entry point + no-cache degradation". When you want `Cache` (or the default) to act as a global entry point in the system, wire a real implementation into it via `Manager`/ext.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class Cache extends ComponentBase` (the source docblock says `implements Psr\SimpleCache\CacheInterface` — without the `implements` keyword)

## Methods

> No options of its own.

### Public methods

    public function get($key, $default = null)
Returns $default (the empty implementation stores nothing).

    public function set($key, $value, $ttl = null)
Returns false (nothing persisted).

    public function delete($key)
Returns false.

    public function has($key)
Returns false.

    public function clear(): void
No content; returns directly (void).

    public function getMultiple($keys, $default = null)
Delegates to get per key: only assembles and returns each key's default.

    public function setMultiple($values, $ttl = null)
Calls set for each {key=>value} (all false); honors the empty-implementation semantics by returning true so the outer logic continues.

    public function deleteMultiple($keys)
The base implementation is just `return $this->delete($keys);` — it hands the whole key array to `delete()` as **one key** (this stub deletes nothing anyway, so the difference is invisible); override this method for a real bulk delete. The implementation passes the whole $keys to the single-key delete (the empty implementation returning false is consistent). Note this base implementation actually passes $keys as a single key to delete ... override it in a subclass if you need real multi-delete.

> Semantics: this base class is only a contractual empty shell; to add caching, override these methods or switch to another implementation.

## Related links

- [DuckPhp\Component\RedisCache](Component-RedisCache.md) — the real Redis implementation
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- PSR reference: `Psr\SimpleCache\CacheInterface`
