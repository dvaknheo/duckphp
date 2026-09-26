# DuckPhp\Ext\SqlDumperSupporterByMysql

## Introduction

`SqlDumperSupporterByMysql` is the **MySQL** driver implementation of `SqlDumperSupporter`:

- `getAllTable()`: runs `SHOW TABLES` and collects every table name;
- `getSchemeByTable()`: runs `SHOW CREATE TABLE` for the DDL and normalises `AUTO_INCREMENT=value` to `AUTO_INCREMENT=1`.

The source is marked `@codeCoverageIgnore` (it needs a real MySQL environment).

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class SqlDumperSupporterByMysql extends SqlDumperSupporter`

## Usage

(Usually you do not use it directly; `SqlDumperSupporter::Current()` picks it by driver.)

```php
$s = SqlDumperSupporterByMysql::_();
$tables = $s->getAllTable();
$ddl    = $s->getSchemeByTable('user');
```

## Methods

### Public methods

    public function getAllTable(): array
Returns every table name in the database via `SHOW TABLES`.

    public function getSchemeByTable(string $table): string
Returns the DDL via `SHOW CREATE TABLE` (the auto-increment value is normalised to 1).

## Related links

- [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) — the parent class
- [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) — the consumer
