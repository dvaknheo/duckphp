# DuckPhp\Ext\SqlDumperSupporterByPgsql

## 简介

`SqlDumperSupporterByPgsql` 是 `SqlDumperSupporter` 的 **PostgreSQL** 驱动实现：

- `getAllTable()`：查 `pg_tables`（`schemaname='public'`）收集表名；
- `getSchemeByTable()`：从 `information_schema.columns` 读取列定义、主键（`key_column_usage`）等信息，手工拼出 `CREATE TABLE "表名" (…)` 语句。

源码标记为 `@codeCoverageIgnore`。本类**已在默认 `database_driver_SqlDumperSupporter_map` 里**（键 `pgsql`），只要 DSN 是 `pgsql:…`（或显式配 `database_driver` 为 `pgsql`）就能直接用，不必手工注册。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class SqlDumperSupporterByPgsql extends SqlDumperSupporter`

## 使用方式

```php
// DSN 是 pgsql:… 时开箱即用
$supporter = \DuckPhp\Ext\SqlDumperSupporter::Current();   // → SqlDumperSupporterByPgsql
$tables = $supporter->getAllTable();

// 要改成自己的实现，就覆盖映射（注意把 mysql/sqlite 一起写上，别丢）
\DuckPhp\Ext\SqlDumperSupporter::_()->init([
    'database_driver_SqlDumperSupporter_map' => [
        'mysql' => \DuckPhp\Ext\SqlDumperSupporterByMysql::class,
        'sqlite' => \DuckPhp\Ext\SqlDumperSupporterBySqlite::class,
        'pgsql' => \MyProj\Ext\PgsqlSupporter::class,
    ],
], $app);
```

## 方法列表

### 公共方法

    public function getAllTable(): array
查 `pg_tables` 返回 public schema 下全部表名。

    public function getSchemeByTable(string $table): string
基于 `information_schema` 生成 `CREATE TABLE` 语句（列类型/可空/默认值/主键）。

## 相关链接

- [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) — 父类
- [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) — 使用方
