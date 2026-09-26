# DuckPhp\Ext\SqlDumperSupporter

## Introduction

`SqlDumperSupporter` is the driver-adapter base class for `SqlDumper`: it isolates the differences between "list every table for this driver" and "get the DDL of one table" inside subclasses. It ships mappings for three drivers — `mysql`, `sqlite` and `pgsql`; other drivers need either an extension of `database_driver_SqlDumperSupporter_map` or a subclass of their own.

The base class's `getAllTable()`/`getSchemeByTable()` throw a `No Impelement` placeholder; the real implementations live in the subclasses.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class SqlDumperSupporter extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Description |
|---|---|---|
| `database_driver_SqlDumperSupporter_map` | `['mysql' => SqlDumperSupporterByMysql::class, 'sqlite' => SqlDumperSupporterBySqlite::class, 'pgsql' => SqlDumperSupporterByPgsql::class]` | Driver name → adapter subclass map (the driver name is the part before `:` in the DSN, or the value of the `database_driver` option). |

## Usage

```php
$supporter = SqlDumperSupporter::Current();   // pick the adapter for the current driver
$tables = $supporter->getAllTable();
$ddl    = $supporter->getSchemeByTable($table);
```

## Caveats

- `Current()` (static) and `getSqlDumperSupporter()`: look the map up by `DbManager::getDatabaseDriver()`; when no driver matches they throw an `Exception` (shaped like `[driver]  No getSqlDumperSupporter`).
- To support a new driver, extend this class, implement the two methods and add your class to `database_driver_SqlDumperSupporter_map`.

## Methods

### Public methods

    public static function Current()
Static: returns the supporter instance for the current driver.

    public function getSqlDumperSupporter(): self
Looks up the map by the current driver and returns an instance of the matching subclass.

    public function getAllTable(): array
Base-class placeholder (throws "No Impelement"); subclasses implement the table listing.

    public function getSchemeByTable(string $table): string
Base-class placeholder (throws "No Impelement"); subclasses implement the DDL lookup.

## Related links

- [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) — the consumer
- [DuckPhp\Ext\SqlDumperSupporterByMysql](Ext-SqlDumperSupporterByMysql.md) / [BySqlite](Ext-SqlDumperSupporterBySqlite.md) / [ByPgsql](Ext-SqlDumperSupporterByPgsql.md) — the per-driver implementations
