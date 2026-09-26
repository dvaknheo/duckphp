# DuckPhp\Ext\SqlDumper

## Introduction

`SqlDumper` is the database "schema/data export and installation" extension: it exports the current database into several SQL files (which may contain the table-prefix placeholder `{prefix}`), and runs those files when needed to create the database/load the data (`install`).

The export is named after the driver, in three files (stored under `path_sql_dump`, `config/` by default):
- `{driver}.sql`: the table schema (`CREATE TABLE …`);
- `{driver}.clean.sql`: table cleanup (`DROP TABLE IF EXISTS …`, for a forced reinstall);
- `{driver}.data.sql`: the data (`INSERT INTO …`, only for the tables listed in `sql_dump_data_tables`).

It supports gathering tables automatically from Model classes, explicit include/exclude, and prefix-placeholder replacement. The driver-specific logic for fetching tables/schema is implemented by the `SqlDumperSupporter` family.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class SqlDumper extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Meaning |
|---|---|---|
| `path` | `''` | The project root path (used with `path_sql_dump` to locate the export directory). |
| `path_sql_dump` | `'config'` | The subdirectory the SQL files are stored in. |
| `sql_dump_include_tables` | `[]` | Tables explicitly included (supports replacing the `@` placeholder with the table prefix). |
| `sql_dump_exclude_tables` | `[]` | Tables excluded. |
| `sql_dump_data_tables` | `[]` | The tables whose data must be exported (written into `.data.sql`). |
| `sql_dump_include_tables_all` | `false` | When `true`, exports every table of the database (ignoring by_model). |
| `sql_dump_include_tables_by_model` | `true` | Gathers tables automatically from the project's Model classes (the classes in the same directory as `namespace\Model\Base`). |
| `sql_dump_debug_show_sql` | `false` | Prints every statement when running SQL. |

## Usage

```php
\DuckPhp\Ext\SqlDumper::_()->init([
    'path' => __DIR__.'/../',
    'path_sql_dump' => 'config',
    'sql_dump_data_tables' => ['user'],
], $app);

SqlDumper::_()->dump();             // exports mysql.sql / mysql.clean.sql / mysql.data.sql
SqlDumper::_()->install();          // runs mysql.sql (+ data.sql)
SqlDumper::_()->install(true);      // runs clean.sql first, then installs (force)
```

## Caveats

- Table-selection priority: `sql_dump_include_tables_all` → otherwise = the tables found by `sql_dump_include_tables_by_model` ∪ `sql_dump_include_tables`, then `sql_dump_exclude_tables` is removed, and finally only tables carrying `table_prefix` (if any) are kept.
- The real prefix in the schema SQL and the clean/data SQL is replaced with the `{prefix}` placeholder (`replacePrefixToPlaceholder`), and `install` replaces it back with `App::options['table_prefix']`.
- `searchTables` reflects on the directory `namespace\Model\Base` lives in, scans the Model classes and calls `table()` on them.
- `install($force)`: with `$force=true` it runs `{driver}.clean.sql` first; then it runs `{driver}.sql`, and `{driver}.data.sql` too when that file exists.
- Statements are split on `;\n`; each one is run with `DbManager::Db()->execute` (the debug switch echoes them).

## Methods

### Public methods

    public function dump()
Exports the current database: writes the three files `{driver}.sql/.clean.sql/.data.sql`; returns `false` when there is no database driver.

    public function install(bool $force = false): void
Runs the exported SQL files (optionally cleaning first), replacing `{prefix}` back with the real table prefix.

### Protected methods

    protected function writeDumpFile(string $file, string $string): void
Writes the content to `path/path_sql_dump/file`.

    protected function executeSqlFile(string $file, string $prefix): void
Reads the file, replaces the prefix, and runs the statements one by one after splitting on `;\n`.

    protected function getTables(): array
Computes the table list to handle this time (merge/exclude/prefix filter/sort).

    protected function getSchemes(): string
Concatenates the schema SQL of each table (with the prefix replaced by `{prefix}`).

    protected function getCleanTableSql(): string
Generates the `DROP TABLE IF EXISTS …` statements (table names carrying `{prefix}`).

    protected function replacePrefixToPlaceholder(string $sql): string
Replaces the real table prefix in the SQL with the `{prefix}` placeholder.

    protected function getInsertTableSql(): string
Generates INSERT statements for each table in `sql_dump_data_tables`.

    protected function getDataSql(string $table): string
Queries the whole table and generates `INSERT INTO … VALUES` statements.

    protected function getModelPath(): string
Obtains the Model directory path by reflecting on `namespace\Model\Base`.

    protected function searchTables(): array
Scans the Model classes and collects table names through `table()`.

    protected function searchModelClasses(string $path): array
Recursively scans the `.php` file paths in a directory as candidate Model classes.

## Related links

- [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) — the driver-specific table/schema implementation
- [DuckPhp\Component\DbManager](Component-DbManager.md) — the database source
