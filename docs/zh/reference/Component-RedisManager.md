# DuckPhp\Component\RedisManager

Redis 连接管理器：把一组连接配置（host/port/auth/select）管理成带写(tag0)/读(tag1)的连接池，惰性建链并缓存 `\Redis` 对象。

## 简介

`RedisManager extends ComponentBase` 是 DuckPHP 拿 Redis 句柄的管理器（RedisCache 也依赖 `RedisManager::Redis()`）：

- 配置来源：`redis` 单条 或 `redis_list` 数组（每项 host/port/auth/select）；可为空让 `reload_by_setting` 从 context 的 setting 取；
- 按 tag（0 写、1 读）惰性 `connect`, auth/select 等按需；
- `getServer(tag)` 池化缓存一个 `\Redis` 实例；超出 config 抛错。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class RedisManager extends ComponentBase`
- 常量：`TAG_WRITE=0`、`TAG_READ=1`

## 选项

`RedisManager::$options`：

| 选项 | 默认 | 说明 |
|---|---|---|
| `redis` | null | 单条连接（对象/数组 host,port,…）。 |
| `redis_list` | null | 多条配置数组（每项 `['host'=>…,'port'=>…,'auth'=>…,'select'=>…]`）。 |
| `redis_list_reload_by_setting` | true | 未给主机要时允许由 setting (`redis_list`/`redis`) 提供。 |
| `redis_list_try_single` | true | 列表缺时按单条 redis 包装。 |

样例配置块（源码注释里的形状）：

```php
'redis_list' => [
    ['host'=>'127.0.0.1','port'=>6379,'auth'=>null,'select'=>0],
]
```

## 使用方式

```php
use DuckPhp\Component\RedisManager;
$r = RedisManager::_()->init([
  'redis_list' => [['host'=>'127.0.0.1','port'=>6379]],
]);
$r = RedisManager::Redis();               // 写连接(tag0，默认)
$r->set('k','v');
// RedisCache 组件本身 get/set 即自行用 RedisManager
```

## 注意事项

- 连接首次 getServer 才建；会缓存 pool 直到 clear/进程结束。
- select 需 Redis 有 database；auth 空就跳过。
- tag 越界走 createServer(config(null)) → 会抛错——设计是 read 写都至少覆盖 tag0。

## 方法列表

### 公共静态方法

    public static function Redis($tag = 0)
取某 tag 连接（→getServer）。

### 公共实例方法

    public function getRedisConfigList(): array
（当前解析出来的）redis 配置列表。

    public function getServer($tag = 0)
惰性取/建连接（池缓存）。

    public function createServer(array $config): object
new \Redis + connect（auth/select 按存在设置）并返回。

### 受保护方法

    protected function initOptions(array $options): void
组装 redis_config_list：list 或（try single）按 redis。

    protected function initContext(object $context): void
reload_by_setting=true 时，用 context `_Setting()`取 redis_list/redis 覆盖 config list。

## 相关链接

- [DuckPhp\Component\RedisCache](Component-RedisCache.md) — 上层 cache（用 Redis()）
- Redis 基础库：`\Redis` 扩展/ predis 由宿主提供
