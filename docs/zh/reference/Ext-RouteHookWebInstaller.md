# DuckPhp\Ext\RouteHookWebInstaller

`DuckPhp\Ext\RouteHookWebInstaller` Web 安装器路由钩子。

## 简介

`RouteHookWebInstaller` 是 `DuckPhp\Ext` 命名空间下的 类，由 DuckPhp 框架提供。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `web_installer_use_database` | `true` | 是否启用数据库安装检查。 |
| `web_installer_use_redis` | `true` | 是否启用 Redis 安装检查。 |
| `web_installer_database_drivers` | `['sqlite' => true, 'pgsql' => true, 'duckdb' => false]` | 启用的数据库驱动。 |
| `web_installer_view` | `''` | 自定义视图类名。 |
| `web_installer_view_block_custom` | `null` | 自定义设置区块。 |
| `web_installer_force` | `false` | 是否强制重新安装。 |
| `web_installer_check_custom_callback` | `null` | 自定义环境检查回调。 |
| `web_installer_do_custom_callback` | `null` | 自定义安装步骤回调。 |
| `web_installer_render_custom_callback` | `null` | 自定义渲染回调。 |
| `web_installer_default_sentences` | `[]` | 默认语言句子。 |

## 使用方式

### 基本用法

```php
use DuckPhp\Ext\RouteHookWebInstaller;

$obj = RouteHookWebInstaller::_();
```

## 注意事项

1. 本类为框架内部或扩展组件，通常由框架自动加载。
2. 如需自定义行为，可继承本类并覆盖相应方法。

## 方法列表

### 公共方法

    static function Hook($path_info)

    function init(array $options, ?object $context = null)
@param array<string, mixed> $options
 @param object|null $context
 @return static

    function _Hook(string $path_info): bool

    function installAction()

    function installBusiness(array $post): array
@param array<string, mixed> $post
 @return array<string, mixed>

### 受保护方法

    function initContext(object $context): void

    function getInstallPath(): string

    function buildPageData(array $post, array $exceptions = [], bool $installed = false): array
@param array<string, mixed> $post
 @param array<string, mixed> $exceptions
 @param bool $installed
 @return array<string, mixed>

    function renderCustom(array $view_data): string
Override hook: render custom setting block (Customer Setting).
 Return HTML string, or '' to hide the Customer Setting section.
 @param array<string, mixed> $view_data filtered POST input (for echo-back on failure)
 @return string

    function checkRootHasRedis(): bool

    function checkRootHasDatabase(): bool

    function checkCustom(array $post): array
Override hook: extra validation after redis/database checks. Throw \Exception on failure.
 @param array<string, mixed> $post
 @return array<string, mixed>

    function doCustom(array $post, array $ext_data = []): array
Override hook: extra install steps after doSchema. Throw \Exception on failure.
 Can be replaced by option 'web_installer_do_custom_callback'.
 @param array<string, mixed> $post
 @param array<string, mixed> $ext_data
 @return array<string, mixed>

    function checkEnv(): array

    function checkRedis(array $post): array
@param array<string, mixed> $post
 @return array<string, mixed>

    function getEnabledDatabaseDrivers(): array

    function checkDatabase(array $post): array
@param array<string, mixed> $post
 @return array<string, mixed>

    function makeDsn(string $driver, array $config): ?string

    function getSchemaSqlFile(string $driver, string $suffix = ''): string
Locate schema sql file: {driver}{.suffix}.sql in the app config dir.
 suffix '' -> {driver}.sql (create), 'clean' -> {driver}.clean.sql (drop), 'data' -> {driver}.data.sql (seed data).

    function doSchema(array $post, array $ext_data = []): array
@param array<string, mixed> $post
 @param array<string, mixed> $ext_data
 @return array<string, mixed>

    function executeSqlFile(DuckPhp\Db\Db $db, string $file): void
Execute a schema sql file, replacing the {prefix} placeholder with the app table_prefix.

    function getCurrentDriver(): ?string

    function executeSql(DuckPhp\Db\Db $db, string $sql): void

    function show(array $data)
Show page: render all install info in a single page by built-in view.
 @param array<string, mixed> $data

## 相关链接

- [中文参考手册目录](index.md)
