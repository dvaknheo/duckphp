# DuckPhp\Component\RedisManager

Redis connection manager: manages a set of connection configs (host/port/auth/select) as a connection pool with a write (tag0)/read (tag1) split, lazily connecting and caching `\Redis` objects.

## Introduction

`RedisManager extends ComponentBase` is the manager DuckPHP uses to obtain Redis handles (RedisCache also relies on `RedisManager::Redis()`):

- Config sources: a single `redis` entry or a `redis_list` array (each item host/port/auth/select); may be left empty so `reload_by_setting` pulls from the context's setting;
- Lazily `connect`s per tag (0 = write, 1 = read); auth/select are applied on demand;
- `getServer(tag)` pools and caches one `\Redis` instance; out-of-range tags throw.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class RedisManager extends ComponentBase`
- Constants: `TAG_WRITE=0`, `TAG_READ=1`

## Options

`RedisManager::$options`:

| Option | Default | Description |
|---|---|---|
| `redis` | null | Single connection (object/array host,port,…). |
| `redis_list` | null | Array of multiple configs (each `['host'=>…,'port'=>…,'auth'=>…,'select'=>…]`). |
| `redis_list_reload_by_setting` | true | When no hosts are given, allow the setting (`redis_list`/`redis`) to provide them. |
| `redis_list_try_single` | true | When the list is missing, wrap the single `redis` entry. |

Sample config block (the shape from the source comments):

```php
'redis_list' => [
    ['host'=>'127.0.0.1','port'=>6379,'auth'=>null,'select'=>0],
]
```

## Usage

```php
use DuckPhp\Component\RedisManager;
$r = RedisManager::_()->init([
  'redis_list' => [['host'=>'127.0.0.1','port'=>6379]],
]);
$r = RedisManager::Redis();               // write connection (tag0, default)
$r->set('k','v');
// the RedisCache component's own get/set uses RedisManager internally
```

## Caveats

- The connection is built only on the first getServer; it stays cached in the pool until clear/process end.
- select requires Redis databases to exist; empty auth is skipped.
- Out-of-range tags go through createServer(config(null)) → throws — the design expects both read and write to at least cover tag0.

## Methods

### Public static methods

    public static function Redis($tag = 0)
Get the connection for a tag (→ getServer).

### Public instance methods

    public function getRedisConfigList(): array
The (currently resolved) redis config list.

    public function getServer($tag = 0)
Lazily get/create a connection (pool-cached).

    public function createServer(array $config): object
new \Redis + connect (auth/select applied when present), then return it.

### Protected methods

    protected function initOptions(array $options): void
Assemble redis_config_list: from list, or (try single) from `redis`.

    protected function initContext(object $context): void
When reload_by_setting=true, use the context's `_Setting()` to read redis_list/redis and override the config list.

## Related links

- [DuckPhp\Component\RedisCache](Component-RedisCache.md) — upper-level cache (uses Redis())
- The Redis base library: the `\Redis` extension / predis is provided by the host
