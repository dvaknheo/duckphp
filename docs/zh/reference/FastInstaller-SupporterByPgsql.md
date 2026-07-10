# DuckPhp\FastInstaller\SupporterByPgsql

PostgreSQL 数据库安装支持器。

## 简介

`SupporterByPgsql` 继承自 `Supporter`，是 PostgreSQL 数据库的安装支持器。当前实现基本参照 MySQL 的结构，但部分 SQL 方言和默认端口需要按 PostgreSQL 实际情况调整。

## 选项

`SupporterByPgsql` 没有自定义选项，依赖父类的 `database_driver_supporter_map`。

## 使用方式

```php
use DuckPhp\FastInstaller\Supporter;

// 当 DbManager 的驱动为 pgsql 时，返回 SupporterByPgsql 实例
$supporter = Supporter::Current();

$desc = $supporter->getInstallDesc();
$options = $supporter->readDsnSetting($options);
$options = $supporter->writeDsnSetting($options);
$tables = $supporter->getAllTable();
$sql = $supporter->getSchemeByTable($table);
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'database_driver_supporter_map' => [
            'mysql' => \DuckPhp\FastInstaller\SupporterByMysql::class,
            'pgsql' => \DuckPhp\FastInstaller\SupporterByPgsql::class,
            'sqlite' => \DuckPhp\FastInstaller\SupporterBySqlite::class,
        ],
    ];
}
```

## 注意事项

1. 当前实现中默认端口仍为 `3306`，实际使用 PostgreSQL 时应调整为 `5432`。
2. `getAllTable()` 和 `getSchemeByTable()` 当前使用 MySQL 的 SQL 语法，需要针对 PostgreSQL 修改。
3. 生成 DSN 的格式为 `pgsql:host=...;port=...;dbname=...;charset=utf8mb4;`。

## 全部选项

```php
public $options = [
    // 继承自 Supporter，无独立选项
];
```

## 方法列表

### 公共方法

    public function readDsnSetting($options)
解析 `dsn` 并补全 `host` 和 `port` 默认值

    public function writeDsnSetting($options)
根据 `host`、`port`、`dbname` 生成 PostgreSQL DSN 字符串

    public function getAllTable(): array
获取所有表名（当前实现需要适配 PostgreSQL 方言）

    public function getSchemeByTable(string $table): string
获取表结构（当前实现需要适配 PostgreSQL 方言）

    public function getInstallDesc(): string
返回 PostgreSQL 安装时的交互提示文本

## 相关链接

- [DuckPhp\FastInstaller\Supporter](FastInstaller-Supporter.md)
- [DuckPhp\FastInstaller\SupporterByMysql](FastInstaller-SupporterByMysql.md)
- [DuckPhp\FastInstaller\SupporterBySqlite](FastInstaller-SupporterBySqlite.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
