# DuckPhp\Ext\SqlDumperSupporterByMysql

## 简介

`SqlDumperSupporterByMysql` 是 `SqlDumperSupporter` 的 **MySQL** 驱动实现：

- `getAllTable()`：执行 `SHOW TABLES` 收集全部表名；
- `getSchemeByTable()`：执行 `SHOW CREATE TABLE` 取建表语句，并把 `AUTO_INCREMENT=值` 归一为 `AUTO_INCREMENT=1`。

源码标记为 `@codeCoverageIgnore`（依赖真实 MySQL 环境）。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class SqlDumperSupporterByMysql extends SqlDumperSupporter`

## 使用方式

（通常无需直接使用，由 `SqlDumperSupporter::Current()` 按驱动自动选择。）

```php
$s = SqlDumperSupporterByMysql::_();
$tables = $s->getAllTable();
$ddl    = $s->getSchemeByTable('user');
```

## 方法列表

### 公共方法

    public function getAllTable(): array
经 `SHOW TABLES` 返回数据库全部表名。

    public function getSchemeByTable(string $table): string
经 `SHOW CREATE TABLE` 返回建表语句（自增值归一为 1）。

## 相关链接

- [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) — 父类
- [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) — 使用方
