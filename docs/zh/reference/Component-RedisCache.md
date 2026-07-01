# DuckPhp\Component\RedisCache

基于 Redis 的缓存组件。

## 简介

`RedisCache` 组件实现了一套符合 PSR-16 风格的缓存接口，底层依赖 `DuckPhp\Component\RedisManager` 获取 Redis 连接。启用后，它会自动替换默认的 `DuckPhp\Component\Cache` 实例，使 `Cache::_()` 指向 Redis 缓存。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `redis_cache_skip_replace` | `false` | 是否跳过替换默认 `Cache` 实例。为 `true` 时，`Cache::_()` 不会指向 Redis 缓存。 |
| `redis_cache_prefix` | `''` | 所有 Redis 缓存键的前缀。 |

## 启用方式

在 `App` 的 `ext` 选项中启用 `RedisCache`：

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Component\RedisCache::class => true,
        ],
    ];
}
```

或者启用数组形式的配置：

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Component\RedisCache::class => [
                'redis_cache_prefix' => 'app:',
            ],
        ],
    ];
}
```

启用后，框架中所有通过 `Cache::_()` 访问的缓存都会自动使用 Redis。

## 使用方式

### 通过 Cache 组件

```php
use DuckPhp\Component\Cache;

Cache::_()->set('user:1', $userData, 3600);
$user = Cache::_()->get('user:1');
$exists = Cache::_()->has('user:1');
Cache::_()->delete('user:1');
```

### 通过 Business Helper

```php
use DuckPhp\Foundation\Business\Helper;

Helper::Cache()::set('config', $config, 3600);
$config = Helper::Cache()::get('config');
```

### 批量操作

```php
use DuckPhp\Component\Cache;

$items = ['key1' => 'value1', 'key2' => 'value2'];
Cache::_()->setMultiple($items, 3600);

$values = Cache::_()->getMultiple(['key1', 'key2']);
Cache::_()->deleteMultiple(['key1', 'key2']);
```

## 存储格式

`RedisCache` 使用 JSON 序列化存储值，因此存储的数据类型会被转换为 JSON 可表示的类型：

- 数组和对象 → JSON 字符串
- 字符串、数字、布尔值 → JSON 字符串
- `null` → `'null'` 字符串

读取时通过 `json_decode($ret, true)` 还原为数组。如果缓存值为对象或需要保留原始类型，需要自行处理序列化。

## 注意事项

1. `RedisCache` 依赖 `RedisManager`，请确保已正确配置 `redis` 或 `redis_list`。
2. 所有键都会自动加上 `redis_cache_prefix` 前缀。
3. `clear()` 方法当前未实现，调用无效果。
4. `setMultiple()` 内部会调用 `set()` 逐条写入，不是原子操作。
5. 存储复杂对象前，建议先自行序列化为字符串。

## 全部选项

```php
public $options = [
    'redis_cache_skip_replace' => false,
    'redis_cache_prefix' => '',
];
```

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
初始化组件，并根据 `redis_cache_skip_replace` 决定是否替换 `Cache::_()` 实例

    public function get($key, $default = null)
从 Redis 获取缓存值，不存在则返回 `default`

    public function set($key, $value, $ttl = null)
将值以 JSON 格式写入 Redis，支持设置过期时间（秒）

    public function delete($key)
删除单个或多个缓存键。参数可以是字符串或数组

    public function has($key)
判断缓存键是否存在

    public function clear()
清空缓存。当前未实现

    public function getMultiple($keys, $default = null)
批量获取缓存值

    public function setMultiple($values, $ttl = null)
批量设置缓存值

    public function deleteMultiple($keys)
批量删除缓存键

### 受保护方法

    protected function initContext(object $context)
初始化上下文，替换默认 `Cache` 实例

    protected function redis()
获取 Redis 连接实例，来自 `RedisManager::Redis()`

## 相关链接

- [DuckPhp\Component\Cache](Component-Cache.md)
- [DuckPhp\Component\RedisManager](Component-RedisManager.md)
- [DuckPhp\Foundation\Business\Helper](Foundation-Business-Helper.md)
