# 20 缓存与 Redis

> 解决什么问题：给应用一个统一的缓存入口（`Cache`），以及通过 Redis 拿到真正的分布式缓存能力；多 Redis 实例怎么配、子应用怎么各用各的。
> 前置：[第 5 章 配置与设置](configuration.md)、[第 14 章 Helper 与全局函数](helper.md)。预计 15 分钟。
> 本章**没有可跑的示例工程**：`demo/` 与 `tests/data_for_tests/` 里只有配置样例与单元测试，没有完整的缓存演示页面。下文标注「示意」的代码段是**写法示范**，不是仓库里现成的文件。

## 最小示例

启用 Redis 缓存只需在 `ext` 里声明两个组件，并给出 `redis_list` 配置：

```php
// config/DuckPhpSettings.config.php（真实文件：demo/config/DuckPhpSettings.config.php 里有同形状的注释样例）
return [
    'redis_list' => [
        ['host' => '127.0.0.1', 'port' => 6379, 'auth' => '123456', 'select' => 2],
    ],
];

// 应用选项（示意：仓库里没有现成示例工程，这段展示标准写法）
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Component\RedisManager::class => true,   // 连接管理器
            \DuckPhp\Component\RedisCache::class  => true,   // 缓存实现（自动替换 Cache）
        ],
    ];
}
```

之后业务层就能直接 `Helper::Cache()->set(...)` / `Helper::Cache()->get(...)`。`tests/Component/RedisCacheTest.php` 就是这么初始化的（第 19–27 行），只是它依赖本机 Redis，不能作为演示页面跑。

## 机制说明

### `Cache`：统一入口 + 默认空实现

`DuckPhp\Component\Cache`（`src/Component/Cache.php`）是缓存的**抽象入口**。它声明了一组 PSR-16 风格的方法，但**默认实现全是空操作**：

| 方法 | 默认行为 |
|---|---|
| `get($key, $default = null)` | 永远返回 `$default` |
| `set($key, $value, $ttl = null)` | 返回 `false`（什么都没存） |
| `delete($key)` | 返回 `false` |
| `has($key)` | 返回 `false` |
| `clear()` | 无操作 |
| `getMultiple($keys, $default = null)` | 逐键调 `get`，全返回默认值 |
| `setMultiple($values, $ttl = null)` | 逐键调 `set`，返回 `true` |
| `deleteMultiple($keys)` | 直接调 `delete($keys)`（注意：基类把它当单键处理） |

这样设计的好处：业务代码面向 `Cache` 编程，**没配 Redis 时自动降级为「无缓存」**，不会报错。要让缓存真正生效，把 `Cache` 的单例替换成真实现即可。

### `RedisCache`：真正的缓存实现

`DuckPhp\Component\RedisCache`（`src/Component/RedisCache.php`）在 Redis 上实现了同一组方法：

- 值经 `json_encode(JSON_UNESCAPED_UNICODE)` 存、`json_decode` 取——数组/对象直接存取；
- 所有 key 加 `redis_cache_prefix` 前缀，避免多个应用共用 Redis 时串扰；
- **`initContext()` 里会自动 `Cache::_($this)`**（除非设 `'redis_cache_skip_replace' => true`），也就是「`RedisCache` 一初始化，全局 `Cache` 就指向它」；
- `delete()` 接受数组（一次删多键）；`clear()` 是空实现（占位，没做清库）。

### `RedisManager`：多实例连接池

`DuckPhp\Component\RedisManager`（`src/Component/RedisManager.php`）管理一组 Redis 连接：

| 选项 | 默认 | 说明 |
|---|---|---|
| `redis` | `null` | 单条连接配置（`redis_list_try_single=true` 时包装成数组） |
| `redis_list` | `null` | 多条配置数组，每项 `['host'=>…, 'port'=>…, 'auth'=>…, 'select'=>…]` |
| `redis_list_reload_by_setting` | `true` | 允许从 `Setting()` 里再读 `redis_list` / `redis` 覆盖选项值 |
| `redis_list_try_single` | `true` | 没给 `redis_list` 时按单条 `redis` 包装 |

取连接用 `RedisManager::Redis($tag)`：`TAG_WRITE = 0`（写）、`TAG_READ = 1`（读）。连接是**惰性建立**的：第一次 `getServer($tag)` 才 `new \Redis()` 并 `connect()`，之后缓存在 `pool` 里复用。

### 子应用各用各的 Redis

子应用默认共享根应用的 `RedisManager`（整个进程一份连接池）。要独立，给它的 `app` 选项加：

```php
ThirdApp::class => [
    'local_redis' => true,                  // 让子应用各用一份 RedisManager
    'redis_list'  => [['host' => '…', 'port' => 6379]],
],
```

`DuckPhp::initComponentsOfInner()` 检测到 `local_redis` 后会 `createLocalObject(RedisManager::class)` 并重新 `init()`（`src/DuckPhp.php` 第 146–149 行），于是子应用相位里的 `RedisManager::_()` 是独立实例。

### 什么时候**别**用框架缓存

- **请求内的临时数据**：用 `Runtime`（随请求清空，无一致性负担）。
- **跨请求但单点部署**：PHP 的 `APCu` 或简单的文件缓存可能更省——`RedisCache` 适合「多应用、多机共享」的场景。
- **需要复杂失效策略**（如按 tag 批量失效、LRU）：框架缓存只有 key→value + TTL，没有 tag 概念；这种需求请直接用 `RedisManager::Redis()` 拿原生 `\Redis` 自己实现。

## 常见写法

```php
// 1) 业务层里缓存一段数据（Helper::Cache 来自 BusinessHelperTrait）
public function hotProducts(): array
{
    $key = 'hot_products';
    $data = Helper::Cache()->get($key);
    if ($data === null) {
        $data = ProductModel::_()->getHot(20);
        Helper::Cache()->set($key, $data, 300);   // 5 分钟
    }
    return $data;
}

// 2) 直接拿 Redis 连接（不走 Cache 抽象）
$redis = RedisManager::Redis();                   // 写连接（tag 0）
$redis->hSet('order:42', 'status', 'paid');

// 3) 读写分离：读连接是 tag 1
$redis_read = RedisManager::Redis(RedisManager::TAG_READ);

// 4) 批量删
Helper::Cache()->delete(['hot_products', 'hot_categories']);

// 5) 子应用独立 Redis（在父应用 app 选项里）
ThirdApp::class => ['local_redis' => true, 'redis_list' => [/* … */]],
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `Helper::Cache()->set()` 返回 `false`，`get()` 永远拿默认值 | 没启用 `RedisCache`，`Cache` 还是默认空实现 | 在 `ext` 里加 `RedisCache::class => true`（并配好 `RedisManager`） |
| 两个应用写了同一个 key 互相覆盖 | 共用 Redis 且没设前缀 | 给其中一个应用配 `'redis_cache_prefix' => 'shop_'` |
| 子应用改了 Redis 配置但主应用也变了 | 子应用改的是**共享**的 `RedisManager` | 给子应用加 `'local_redis' => true` |
| `RedisManager::Redis(1)` 报错 | `redis_list` 只有一项，没有 tag 1 | 给 `redis_list` 配两项（第二项作读），或只用 tag 0 |
| 存进去的数字/数组取出来类型不对 | `RedisCache` 走 JSON 编解码 | 这是特性：复杂结构直接存取；要精确控制类型就用原生 `RedisManager::Redis()` |
| 单元测试在 Windows 下 Redis 相关假失败 | Windows 侧 PHP 没有 redis 扩展 | 测试一律走 WSL（见 [第 23 章](testing.md)） |

## 下一步

- [第 21 章 国际化与文案](i18n.md)：缓存键里如果带语言代码，记得把 `lang_final` 编进 key。
- [第 28 章 组件共享与应用间通信](component-sharing.md)：`local_redis` 与共享容器的完整讨论。
- 参考手册：[DuckPhp\Component\Cache](../reference/Component-Cache.md)、[DuckPhp\Component\RedisCache](../reference/Component-RedisCache.md)、[DuckPhp\Component\RedisManager](../reference/Component-RedisManager.md)。
