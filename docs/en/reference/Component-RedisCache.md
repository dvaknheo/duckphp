# DuckPhp\Component\RedisCache

Redis cache implementation (fits the simple Cache handshake: values are JSON-encoded on set, set takes an optional TTL); by default it can replace itself in as the real implementation behind the global `Cache`.

## Introduction

`RedisCache extends ComponentBase` (comments align with `Psr\SimpleCache`) is a component that implements the simple-cache facade on top of Redis:

- Reuses the `RedisManager::Redis()` connection;
- Keys get the `redis_cache_prefix` prefix to avoid cross-app interference;
- Values are JSON-encoded (UNESCAPED_UNICODE) and pulled back out with json_decode;
- Provides get/set/delete/has/…multiple…, clear (unimplemented/placeholder);
- In its constructor/init (unless `redis_cache_skip_replace`) it runs `Cache::_($this)` to replace the reference on the parent Cache — meaning "the cache" the default components talk about is this Redis implementation; otherwise the base Cache's empty implementation is used, or an upper layer replaces it.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class RedisCache extends ComponentBase`
- Related connection: RedisManager::Redis().

## Options

`RedisCache::$options`:

| Option | Default | Description |
|---|---|---|
| `redis_cache_skip_replace` | false | If true, do not replace the global Cache (your call). |
| `redis_cache_prefix` | `''` | Key prefix for this cache. |

## Usage

Via the cache DuckPhp provides (RedisCache is the implementation when enabled):

```php
use DuckPhp\Component\RedisCache;
use DuckPhp\Component\Cache;

RedisCache::_()->init([...]);      // default skip=false → Cache::_(this)
Cache::_()->set('k','v');
$ret = Cache::_()->get('k');
```

## Caveats

- Values are stored as JSON strings; numbers come back json-decoded — reading the data as arrays reproduces the structure.
- clear is currently just an empty implementation (return) — extend it yourself if you really need to flush.
- delete accepts an array (del multiple keys).

## Methods

### Public methods

    public function get($key, $default = null)
Get the raw value → json decode (otherwise return default / handle the false boolean case — the implementation fetches first, then json_decodes or falls back).

    public function set($key, $value, $ttl = null)
JSON-encode + redis set (TTL optional).

    public function delete($key)
del (array keys are handled by default).

    public function has($key)
redis exists.

    public function clear()
Not implemented (placeholder return).

    public function getMultiple($keys, $default = null)
Assembles by getting each key one by one.

    public function setMultiple($values, $ttl = null)
set in a loop.

    public function deleteMultiple($keys)
delete with an array.

### Protected methods

    protected function initContext(object $context): void
When skip=false, `Cache::_($this)` replaces the framework's default cache implementation.

    protected function redis(): \Redis
The connection obtained from RedisManager::Redis().

## Related links

- [DuckPhp\Component\Cache](Component-Cache.md)
- [DuckPhp\Component\RedisManager](Component-RedisManager.md) — Redis connection management
- Redis cache: set skip if you do not want the global replacement.
