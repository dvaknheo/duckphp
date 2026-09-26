# 2-14 Cache and Redis

> What this solves: giving the application a unified cache entry point ([`Cache`](../reference/Component-Cache.md)), and getting real distributed cache capability through Redis; how to configure multiple Redis instances and how child apps each use their own.
> Prerequisites: [Chapter 1-5 Configuration and Settings](configuration.md), [Chapter 2-9 Helpers and Global Functions](helper.md). About 15 minutes.
> This chapter **has no runnable example project**: `demo/` and `tests/data_for_tests/` contain only configuration samples and unit tests, no complete cache demo page. The code segments marked "illustrative" below are **pattern demonstrations**, not ready-made files in the repository.

## Minimal example

Enabling the Redis cache takes declaring two components in `ext` plus a `redis_list` configuration:

```php
// config/DuckPhpSettings.config.php (real file: demo/config/DuckPhpSettings.config.php has a commented sample of the same shape)
return [
    'redis_list' => [
        ['host' => '127.0.0.1', 'port' => 6379, 'auth' => '123456', 'select' => 2],
    ],
];

// application options (illustrative: there is no ready-made example project in the repository; this shows the standard pattern)
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Component\RedisManager::class => true,   // connection manager
            \DuckPhp\Component\RedisCache::class  => true,   // cache implementation (automatically replaces Cache)
        ],
    ];
}
```

After that the business layer can directly call `Helper::Cache()->set(...)` / `Helper::Cache()->get(...)`. `tests/Component/RedisCacheTest.php` initializes exactly this way (lines 19–27); it just depends on a local Redis and can't run as a demo page.

## How it works

### `Cache`: unified entry point + default no-op implementation

`DuckPhp\Component\Cache` (`src/Component/Cache.php`) is the **abstract entry point** of caching. It declares a set of PSR-16-style methods, but **the default implementations are all no-ops**:

| Method | Default behavior |
|---|---|
| `get($key, $default = null)` | Always returns `$default` |
| `set($key, $value, $ttl = null)` | Returns `false` (nothing stored) |
| `delete($key)` | Returns `false` |
| `has($key)` | Returns `false` |
| `clear()` | No-op |
| `getMultiple($keys, $default = null)` | Calls `get` per key; all return the default value |
| `setMultiple($values, $ttl = null)` | Calls `set` per key; returns `true` |
| `deleteMultiple($keys)` | Directly calls `delete($keys)` (note: the base class treats it as a single key) |

The benefit of this design: business code programs against `Cache`, and **when Redis is not configured it degrades automatically to "no cache"** without errors. To make the cache actually take effect, replace `Cache`'s singleton with a real implementation.

### `RedisCache`: the real cache implementation

[`DuckPhp\Component\RedisCache`](../reference/Component-RedisCache.md) (`src/Component/RedisCache.php`) implements the same set of methods on Redis:

- Values are stored with `json_encode(JSON_UNESCAPED_UNICODE)` and read with `json_decode` — arrays/objects are stored and read directly;
- All keys get the `redis_cache_prefix` prefix, avoiding cross-talk when multiple apps share a Redis;
- **`initContext()` automatically does `Cache::_($this)`** (unless you set `'redis_cache_skip_replace' => true`) — in other words, "once `RedisCache` initializes, the global `Cache` points to it";
- `delete()` accepts an array (delete multiple keys at once); `clear()` is a no-op (a placeholder — no database flush is implemented).

### `RedisManager`: a multi-instance connection pool

[`DuckPhp\Component\RedisManager`](../reference/Component-RedisManager.md) (`src/Component/RedisManager.php`) manages a set of Redis connections:

| Option | Default | Description |
|---|---|---|
| `redis` | `null` | A single connection config (wrapped into an array when `redis_list_try_single=true`) |
| `redis_list` | `null` | An array of multiple configs, each `['host'=>…, 'port'=>…, 'auth'=>…, 'select'=>…]` |
| `redis_list_reload_by_setting` | `true` | Allows re-reading `redis_list` / `redis` from `Setting()` to override option values |
| `redis_list_try_single` | `true` | When `redis_list` isn't given, wraps the single `redis` |

Get a connection with `RedisManager::Redis($tag)`: `TAG_WRITE = 0` (write), `TAG_READ = 1` (read). Connections are **lazily established**: the first `getServer($tag)` does `new \Redis()` and `connect()`, after which they are cached in `pool` and reused.

### Child apps each use their own Redis

A child app shares the root app's `RedisManager` by default (one connection pool per process). For independence, add to its `app` option:

```php
ThirdApp::class => [
    'local_redis' => true,                  // let the child app use its own RedisManager
    'redis_list'  => [['host' => '…', 'port' => 6379]],
],
```

When [`DuckPhp::initComponentsOfInner()`](../reference/DuckPhp.md) detects `local_redis`, it does `createLocalObject(RedisManager::class)` and re-runs `init()` (`src/DuckPhp.php` lines 146–149), so `RedisManager::_()` in the child app's phase is an independent instance.

### When **not** to use the framework cache

- **Temporary data within a request**: use [`Runtime`](../reference/Core-Runtime.md) (cleared with the request, no consistency burden).
- **Cross-request but single-point deployment**: PHP's `APCu` or a simple file cache may be cheaper — `RedisCache` suits "multiple apps, shared across machines".
- **Complex invalidation strategies** (tag-based batch invalidation, LRU): the framework cache has only key→value + TTL, no tag concept; for such needs use `RedisManager::Redis()` directly to get a native `\Redis` and implement it yourself.

## Common patterns

```php
// 1) Cache a piece of data in the business layer (Helper::Cache comes from Business\BusinessHelper)

public function hotProducts(): array
{
    $key = 'hot_products';
    $data = Helper::Cache()->get($key);
    if ($data === null) {
        $data = ProductModel::_()->getHot(20);
        Helper::Cache()->set($key, $data, 300);   // 5 minutes
    }
    return $data;
}

// 2) Take a Redis connection directly (bypassing the Cache abstraction)
$redis = RedisManager::Redis();                   // write connection (tag 0)
$redis->hSet('order:42', 'status', 'paid');

// 3) Read/write split: the read connection is tag 1
$redis_read = RedisManager::Redis(RedisManager::TAG_READ);

// 4) Batch delete
Helper::Cache()->delete(['hot_products', 'hot_categories']);

// 5) Independent Redis for a child app (in the parent app's app option)
ThirdApp::class => ['local_redis' => true, 'redis_list' => [/* … */]],
```

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| `Helper::Cache()->set()` returns `false`, `get()` always returns the default | `RedisCache` isn't enabled; `Cache` is still the default no-op implementation | Add `RedisCache::class => true` in `ext` (and configure `RedisManager`) |
| Two apps write the same key and overwrite each other | Sharing a Redis with no prefix set | Configure `'redis_cache_prefix' => 'shop_'` for one of the apps |
| A child app changed its Redis config but the main app changed too | The child app changed the **shared** `RedisManager` | Give the child app `'local_redis' => true` |
| `RedisManager::Redis(1)` errors | `redis_list` has only one entry; there is no tag 1 | Configure two entries in `redis_list` (the second as read), or use only tag 0 |
| Numbers/arrays come back with the wrong types | `RedisCache` goes through JSON encode/decode | This is a feature: complex structures round-trip directly; for exact type control use native `RedisManager::Redis()` |
| Redis-related unit tests falsely fail on Windows | Windows-side PHP has no redis extension | Run all tests under WSL (see [Chapter 2-17](testing.md)) |

## Next steps

- [Chapter 2-15 i18n and Wording](i18n.md): if a cache key carries a language code, remember to bake `lang_final` into the key.
- [Chapter 3-4 Component Sharing and Inter-app Communication](component-sharing.md): the full discussion of `local_redis` and shared containers.
- The reference manual: [DuckPhp\Component\Cache](../reference/Component-Cache.md), [DuckPhp\Component\RedisCache](../reference/Component-RedisCache.md), [DuckPhp\Component\RedisManager](../reference/Component-RedisManager.md).
