# DuckPhp\Ext\SqlDumperSupporterBySqlite

## Introduction

`SqlDumperSupporterBySqlite` is the **SQLite** driver implementation of `SqlDumperSupporter`:

- `getAllTable()`: queries `sqlite_master` (`type='table'`) for table names, skipping system tables (the `sqlite_*` prefix);
- `getSchemeByTable()`: takes the `CREATE TABLE` statement from `sqlite_master.sql` and rewrites `CREATE TABLE "name"` into the backtick style `` CREATE TABLE `name` ``.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class SqlDumperSupporterBySqlite extends SqlDumperSupporter`

## Usage

(Usually you do not use it directly; `SqlDumperSupporter::Current()` picks it by driver.)

```php
$s = SqlDumperSupporterBySqlite::_();
$tables = $s->getAllTable();
$ddl    = $s->getSchemeByTable('user');
```

## Methods

### Public methods

    public function getAllTable(): array
Queries `sqlite_master` and returns every user table name (the `sqlite_*` system tables are excluded).

    public function getSchemeByTable(string $table): string
Returns the `CREATE TABLE` SQL (the table name is normalised to backticks).

## Related links

- [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) — the parent class
- [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) — the consumer
