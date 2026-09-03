# DuckPhp\Ext\SqlDumperSupporterBySqlite

## 简介

`SqlDumperSupporterBySqlite` 是 `SqlDumperSupporter` 的 **SQLite** 驱动实现：

- `getAllTable()`：查 `sqlite_master`（`type='table'`）收集表名，跳过系统表（`sqlite_*` 前缀）；
- `getSchemeByTable()`：取 `sqlite_master.sql` 中的建表语句，并把 `CREATE TABLE "名"` 转成反引号风格 `` CREATE TABLE `名` ``。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class SqlDumperSupporterBySqlite extends SqlDumperSupporter`

## 使用方式

（通常无需直接使用，由 `SqlDumperSupporter::Current()` 按驱动自动选择。）

```php
$s = SqlDumperSupporterBySqlite::_();
$tables = $s->getAllTable();
$ddl    = $s->getSchemeByTable('user');
```

## 方法列表

### 公共方法

    public function getAllTable(): array
查 `sqlite_master` 返回全部用户表名（排除 `sqlite_*` 系统表）。

    public function getSchemeByTable(string $table): string
返回建表 SQL（表名统一为反引号）。

## 相关链接

- [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) — 父类
- [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) — 使用方
