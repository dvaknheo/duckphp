# DuckPhp\Ext\SqlDumperSupporterByPgsql

## 简介

`SqlDumperSupporterByPgsql` 是 `SqlDumperSupporter` 的 **PostgreSQL** 驱动实现：

- `getAllTable()`：查 `pg_tables`（`schemaname='public'`）收集表名；
- `getSchemeByTable()`：从 `information_schema.columns` 读取列定义、主键（`key_column_usage`）等信息，手工拼出 `CREATE TABLE "表名" (…)` 语句。

源码标记为 `@codeCoverageIgnore`。注意默认 `SqlDumperSupporter::$options` 的驱动映射只含 `mysql`/`sqlite`，用 pgsql 时需要把本类加进 `database_driver_SqlDumperSupporter_map`。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class SqlDumperSupporterByPgsql extends SqlDumperSupporter`

## 使用方式

```php
\DuckPhp\Ext\SqlDumperSupporter::_()->init([
    'database_driver_SqlDumperSupporter_map' => [
        'pgsql' => \DuckPhp\Ext\SqlDumperSupporterByPgsql::class,
        // mysql / sqlite 的映射保留或自行补充
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
