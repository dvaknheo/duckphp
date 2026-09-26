# DuckPhp\Ext\SqlDumperSupporterByPgsql

## Introduction

`SqlDumperSupporterByPgsql` is the **PostgreSQL** driver implementation of `SqlDumperSupporter`:

- `getAllTable()`: queries `pg_tables` (`schemaname='public'`) for table names;
- `getSchemeByTable()`: reads the column definitions, primary key (`key_column_usage`) and so on from `information_schema.columns` and assembles a `CREATE TABLE "table" (…)` statement by hand.

The source is marked `@codeCoverageIgnore`. This class **is already in the default `database_driver_SqlDumperSupporter_map`** (under the key `pgsql`), so it works as soon as the DSN is `pgsql:…` (or `database_driver` is explicitly set to `pgsql`) — no manual registration needed.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class SqlDumperSupporterByPgsql extends SqlDumperSupporter`

## Usage

```php
// works out of the box when the DSN is pgsql:…
$supporter = \DuckPhp\Ext\SqlDumperSupporter::Current();   // -> SqlDumperSupporterByPgsql
$tables = $supporter->getAllTable();

// to use your own implementation, override the map (keep mysql/sqlite in it, do not drop them)
\DuckPhp\Ext\SqlDumperSupporter::_()->init([
    'database_driver_SqlDumperSupporter_map' => [
        'mysql' => \DuckPhp\Ext\SqlDumperSupporterByMysql::class,
        'sqlite' => \DuckPhp\Ext\SqlDumperSupporterBySqlite::class,
        'pgsql' => \MyProj\Ext\PgsqlSupporter::class,
    ],
], $app);
```

## Methods

### Public methods

    public function getAllTable(): array
Queries `pg_tables` and returns every table name in the public schema.

    public function getSchemeByTable(string $table): string
Builds a `CREATE TABLE` statement from `information_schema` (column types / nullability / defaults / primary key).

## Related links

- [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) — the parent class
- [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) — the consumer
