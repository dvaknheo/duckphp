# DuckPhp\Component\RedisManager

Redis 连接管理器。

## 简介

`RedisManager` 组件负责管理 Redis 连接的创建与访问。它支持单个 Redis 配置和 Redis 列表配置，并支持读写分离。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `redis` | `null` | 单 Redis 配置。当 `redis_list` 未设置且 `redis_list_try_single` 为 `true` 时，会自动将其作为唯一 Redis。 |
| `redis_list` | `null` | Redis 配置列表。索引 `0` 为写库，索引 `1` 为读库。 |
| `redis_list_reload_by_setting` | `true` | 是否允许从 `DuckPhpSettings.config.php` 的 `redis_list` 或 `redis` 重新加载配置。 |
| `redis_list_try_single` | `true` | 当 `redis_list` 未设置时，是否尝试将 `redis` 作为单 Redis 使用。 |

## Redis 配置格式

单 Redis 配置：

```php
class App extends DuckPhp
{
    public $options = [
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'auth' => 'password',
            'select' => 0,
        ],
    ];
}
```

多 Redis 配置（读写分离）：

```php
class App extends DuckPhp
{
    public $options = [
        'redis_list' => [
            [ // 索引 0：写库
                'host' => '127.0.0.1',
                'port' => 6379,
                'auth' => 'password',
                'select' => 0,
            ],
            [ // 索引 1：读库
                'host' => '127.0.0.2',
                'port' => 6379,
                'auth' => 'password',
                'select' => 0,
            ],
        ],
    ];
}
```

也支持从 `config/DuckPhpSettings.config.php` 中配置：

```php
<?php
return [
    'redis_list' => [
        [
            'host' => '127.0.0.1',
            'port' => 6379,
        ],
    ],
];
```

## 读写分离规则

| 方法 | 使用的 tag | 说明 |
|---|---|---|
| `Redis()` / `Redis(0)` | `TAG_WRITE` (0) | 默认返回写库。 |
| `Redis(1)` | `TAG_READ` (1) | 返回读库。 |

常量定义：

```php
const TAG_WRITE = 0;
const TAG_READ = 1;
```

## 使用方式

### 通过 Business Helper

```php
use DuckPhp\Foundation\Business\Helper;

$redis = Helper::Redis();      // 写库
$redis = Helper::Redis(1);     // 读库
```

### 通过 RedisManager 组件

```php
use DuckPhp\Component\RedisManager;

$redis = RedisManager::Redis();   // 写库
$redis = RedisManager::Redis(1);  // 读库
```

### 直接操作 Redis

```php
use DuckPhp\Foundation\Business\Helper;

Helper::Redis()->set('user:1', json_encode($user));
$user = json_decode(Helper::Redis()->get('user:1'), true);
```

## 自定义连接行为

`RedisManager` 提供的 `createServer()` 方法可以被子类重写，以支持连接池、哨兵等高级场景：

```php
class MyRedisManager extends DuckPhp\Component\RedisManager
{
    public function createServer(array $config): object
    {
        // 自定义创建 Redis 连接
        return parent::createServer($config);
    }
}
```

## 注意事项

1. 使用 Redis 扩展前需要确保 PHP 已安装 `redis` 扩展。
2. `Redis()` 方法默认返回 tag 为 `0` 的连接，即写库。
3. 未配置 `redis_list` 且未配置 `redis` 时，调用 `Redis()` 会抛出异常。
4. 目前 `RedisManager` 不会主动关闭连接，连接随 PHP 进程结束而释放。

## 全部选项

```php
public $options = [
    'redis' => null,
    'redis_list' => null,
    'redis_list_reload_by_setting' => true,
    'redis_list_try_single' => true,
];
```

## 方法列表

### 公共方法

    public static function Redis($tag = 0)
获取指定 tag 的 Redis 实例，默认返回写库

    public function getRedisConfigList(): array
获取最终解析后的 Redis 配置列表

    public function getServer($tag = 0)
根据 tag 创建或返回缓存的 Redis 实例

    public function createServer(array $config): object
使用配置创建 Redis 连接

### 受保护方法

    protected function initOptions(array $options): void
合并 `redis` 和 `redis_list` 配置

    protected function initContext(object $context): void
根据 `DuckPhpSettings.config.php` 重新加载配置

## 相关链接

- [DuckPhp\Component\RedisCache](Component-RedisCache.md)
- [DuckPhp\Foundation\Business\Helper](Foundation-Business-Helper.md)
