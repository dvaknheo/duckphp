# DuckPhp\FastInstaller\RedisInstaller

Redis 安装器组件。

## 简介

`RedisInstaller` 负责在命令行安装过程中引导用户配置 Redis 连接，并保存到扩展选项与 `RedisManager` 中。

该组件通常由 `FastInstaller` 在安装流程中自动调用，一般不需要手动实例化。

## 选项

`RedisInstaller` 当前没有自定义选项。

| 选项 | 默认值 | 说明 |
|---|---|---|
| （无） | — | 本组件暂无选项 |

## 使用方式

### 自动调用

在 `FastInstaller::doInstall()` 中会自动执行：

```php
RedisInstaller::_()->install($force);
```

### 手动调用

```php
use DuckPhp\FastInstaller\RedisInstaller;

RedisInstaller::_()->install(true);  // 强制重新安装 Redis 配置
```

## 配置示例

### 启用 Redis

```php
class App extends DuckPhp
{
    public $options = [
        'use_redis' => true,   // 或设置 'local_redis' => true
    ];
}
```

## 注意事项

1. 当前应用必须设置 `use_redis` 或 `local_redis` 为 `true`，否则 `install()` 直接返回。
2. 非强制模式下，如果 `RedisManager` 已存在配置，则跳过配置。
3. 配置过程中会循环提示用户输入 Redis 信息，直到成功连接至少一个 Redis 实例。
4. Redis 配置最终通过 `ExtOptionsLoader` 保存到扩展选项，并重新初始化 `RedisManager`。

## 全部选项

```php
public $options = [
    //
];
```

## 方法列表

### 公共方法

    public function install(bool $force = false)
执行 Redis 安装流程。如果当前应用未启用 Redis 或已有配置且非强制，则返回

### 受保护方法

    protected function callResetRedis(bool $force = false): bool
重置 Redis 配置，调用 `configRedis()` 获取用户输入并写入 Redis 连接

    protected function changeRedis(array $data): void
将配置保存到扩展选项并重新初始化 `RedisManager`。`$data` 为 Redis 连接列表

    protected function configRedis(array $ref_database_list = []): array
循环提示用户输入 Redis 连接信息，直到成功连接并确认不再添加更多 Redis

    protected function checkRedis(array $config): array
使用 `Redis` 扩展尝试连接，验证给定的 Redis 配置是否可用

## 相关链接

- [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md)
- [DuckPhp\Component\RedisManager](Component-RedisManager.md)
- [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md)
