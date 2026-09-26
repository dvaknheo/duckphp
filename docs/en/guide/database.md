# 2-7 Databases

> What this solves: how to configure database connections, how to use multiple databases and read/write splitting, how to write transactions, how to do pagination and export SQL, and what the table-name prefix macro in SQL is.
> Prerequisites: [Chapter 1-5 Configuration and settings](configuration.md), [Chapter 2-1 The four-layer architecture and calling rules](layers.md). About 20 minutes.
> Examples: `demo/public/dbtest.php` (a genuinely runnable SQLite CRUD + pagination example, 244 lines, database file `demo/runtime/dbtest.sqlite`).

```bash
php -S 127.0.0.1:8080 -t demo/public
# visit http://127.0.0.1:8080/dbtest.php directly (the "run standalone if not mounted" line at the end of the file starts its own entry point)
# it is also mounted as a child application of demo: http://127.0.0.1:8080/demo.php?_r=db_test/
```

## Minimal example

The configuration and queries from `demo/public/dbtest.php` (a real file, excerpted):

```php
class DbTestApp extends DuckPhp
{
    public $options = [
        'local_database' => true,          // this application uses a database connection of its own
        'database' => [
            'dsn' => 'sqlite:runtime/dbtest.sqlite',
            'username' => null,
            'password' => null,
            'driver_options' => [],
        ],
    ];
}
```

```php
class TestModel
{
    use ModelTrait;
    public function __construct()
    {
        $this->table_name = 'test';
    }
    public function getDataList($page, $pagesize)
    {
        $sql = "select * from `'TABLE'` order by id desc";       // `'TABLE'` is the table-name macro
        $total = $this->fetchColumn(Helper::SqlForCountSimply($sql));
        $list  = $this->fetchAll(Helper::SqlForPager($sql, $page, $pagesize));
        return [$total, $list];
    }
    public function addData($data)
    {
        $sql = "insert into `'TABLE'` (content) values(?)";
        $this->execute($sql, $data['content']);
        return Helper::Db()->lastInsertId();
    }
}
```

Three key points: configure the connection with the `database` option, write the `` `'TABLE'` `` macro in SQL instead of the real table name, and go through `fetch*` / `execute` for reads and writes (the model-layer wrapper is [Chapter 2-8](model.md)).

## How it works

### 1. Connection configuration: `DbManager` and `Db`

| Option | Default | Meaning |
|---|---|---|
| `database` | `null` | **A single** connection configuration: `['dsn'=>…, 'username'=>…, 'password'=>…, 'driver_options'=>…]` |
| `database_list` | `null` | **Several** connection configurations (for read/write splitting and multiple databases) |
| `database_list_try_single` | `true` | When only `database` is given, treat it as one entry automatically |
| `database_list_reload_by_setting` | `true` | Allow `database_list`/`database` in `Setting()` to override the options |
| `database_driver` | `''` | Driver name (`sqlite`/`mysql`/`pgsql`), usually inferred from the DSN |
| `database_class` | `''` | Swap out the [`Db`](../reference/Db-Db.md) implementation class |
| `database_log_sql_query` | `false` | Whether to write SQL to the log |
| `database_log_sql_level` | `'debug'` | The level of that log entry |
| `local_database` | `false` | Whether a child application gets a connection set of its **own** ([Chapter 3-4](component-sharing.md)) |

[`DbManager`](../reference/Component-DbManager.md) is the connection manager (`TAG_WRITE = 0` for writes, `TAG_READ = 1` for reads) and `Db` is the executor:

```php
Helper::Db();               // the write connection
Helper::DbForRead();        // the read connection
Helper::Db(1);              // fetch by tag (1 = TAG_READ)
Helper::Db()->PDO();        // the raw PDO (for complex SQL)
Helper::DatabaseDriver();   // the current driver name
```

**Connections are lazy**: the PDO object is created the first time SQL actually runs.

### 2. Read/write splitting

Just give several entries in `database_list` and `DbManager` distributes them by tag:

```php
$options = [
    'database_list' => [
        ['dsn' => 'mysql:host=master;dbname=app', 'username' => 'u', 'password' => 'p'],  // tag 0 = write
        ['dsn' => 'mysql:host=slave;dbname=app',  'username' => 'u', 'password' => 'p'],  // tag 1 = read
    ],
];
```

The convention: **reads go through the read connection, writes through the write connection** — the model layer's `fetch*` / `execute` already split them that way ([Chapter 2-8](model.md)).

### 3. Running SQL: the methods of `Db`

| Method | Purpose |
|---|---|
| `fetchAll($sql, ...$args)` | several rows (an array of arrays) |
| `fetch($sql, ...$args)` | a single row |
| `fetchColumn($sql, ...$args)` | a single value (often used for `count(*)`) |
| `fetchObject()` / `fetchObjectAll()` | return objects (the result class can be set with `setObjectResultClass()`) |
| `execute($sql, ...$args)` | run a write operation; **returns the affected row count on success and 0 on failure** |
| `rowCount()` / `lastInsertId()` | information about the last execution/insert |
| `quote($v)` / `quoteScheme($name)` | escape a value / escape an identifier (table name, column name) |
| `table($name)` | bind a table name (works with the macro below) |
| `PDO()` | the raw PDO |

Always pass parameters as placeholders; **never splice user input into SQL**:

```php
$rows = Helper::Db()->fetchAll('select * from note where status=? and id>?', 'on', 10); // ✅
$rows = Helper::Db()->fetchAll("select * from note where title='{$title}'");           // ❌ injection
```

### 4. Table prefixes and the `` `'TABLE'` `` macro

Once `table_prefix` is configured you do not have to write the prefix in SQL — write the `` `'TABLE'` `` macro and it is replaced before execution with "prefix + the current model's table name":

```php
$sql = "select * from `'TABLE'` where id=?";
// table_prefix='app_', model table note → select * from `app_note` where id=?
```

The macro's literal is `` `'TABLE'` `` (backticks included), and [`ModelTrait::prepare()`](../reference/Foundation-Model-ModelTrait.md) performs the substitution ([Chapter 2-8](model.md)); underneath it is `Db::doTableNameMacro()`.

### 5. Convenience methods for insert/update/delete

`Db` carries a set of "build SQL from arrays" tools ([`DbAdvanceTrait`](../reference/Db-DbAdvanceTrait.md)), handy for writing generic models:

```php
$db = Helper::Db();
$db->findData('note', 42);                        // fetch one row by primary key
$db->insertData('note', ['title' => 't']);        // insert (returns the auto-increment id)
$db->updateData('note', 42, ['title' => 't2']);   // update by primary key
$db->deleteData('note', 42);                      // soft delete (writes is_deleted by default)

$db->quoteAndArray(['status' => 'on']);           // a WHERE fragment (joined with AND)
$db->quoteIn([1, 2, 3]);                          // an IN (...) fragment
$db->quoteSetArray(['title' => 'x']);             // a SET fragment
$db->quoteInsertArray(['title' => 'x']);          // the column/value fragment of an INSERT
```

All values are escaped through `quote()`; but the bottom line that **table and column names may only come from code, never from user input** still holds.

### 6. Transactions

The framework does not wrap transactions in an API of its own; use PDO directly (the official examples do the same):

```php
$pdo = Helper::Db()->PDO();
try {
    $pdo->beginTransaction();
    NoteModel::_()->add(['title' => 'a']);
    NoteModel::_()->add(['title' => 'b']);
    $pdo->commit();
} catch (\Throwable $ex) {
    $pdo->rollBack();
    throw $ex;                 // hand it to the exception machinery (Chapter 2-12)
}
```

A transaction must be **opened on the same connection**: with read/write splitting, do not open a transaction on the read connection and then write to the master.

### 7. Pagination

Pagination is the [`Pager`](../reference/Component-Pager.md) component (implementing [`PagerInterface`](../reference/Component-PagerInterface.md)) working together with the SQL helpers (`demo/public/dbtest.php` is a complete example):

```php
$sql   = "select * from `'TABLE'` order by id desc";
$total = $this->fetchColumn(Helper::SqlForCountSimply($sql));                       // build the count SQL
$list  = $this->fetchAll(Helper::SqlForPager($sql, Helper::PageNo(), Helper::PageWindow(3)));
$pager = Helper::PageHtml($total);                                                  // the pager bar HTML
```

`Pager` options: `url` (the base of the pagination links, the current path by default), `current` (the current page, taken from `Helper::PageNo()` by default), `page_size` (30 by default), `page_key` (the URL parameter name, `page` by default), `rewrite` (rewrite rules).

### 8. Exporting schema/data as SQL

To export table schema or data as SQL (installers, backups), use [`Ext\SqlDumper`](../reference/Ext-SqlDumper.md); the dialect comes from a `SqlDumperSupporter*` (`mysql`/`sqlite`/`pgsql` are all in the default mapping). The exported SQL uses `{prefix}` for the table prefix, which the web installation flow ([Chapter 3-6](installer.md)) replaces with the real prefix when it runs.

It is an `Ext\*` extension (not wired automatically): for the full class table and how to swap the dialect implementation see [Chapter 4-13](ext-classes.md) §7.

## Common patterns

**① Several databases: give several entries in `database_list`**

```php
$logDb = Helper::Db(1);            // tag 1 = the second database (used as a read database in the example; with several databases the tag distinguishes them the same way)
```

**② Complex queries: write the SQL directly**

```php
$sql = 'select n.*, u.name from note n left join user u on u.id=n.user_id where n.status=?';
$rows = Helper::DbForRead()->fetchAll($sql, 'on');
```

**③ Log SQL (to chase slow queries or odd behaviour)**

```php
$options = ['database_log_sql_query' => true, 'database_log_sql_level' => 'debug'];
```

**④ Different databases in production and development: override through Setting**

`database_list_reload_by_setting` is true by default, so environment-dependent items such as passwords and DSNs can live in `DuckPhpSettings.config.php` or `.env` ([Chapter 1-5](configuration.md)).

**⑤ Write table creation/seed data as a model's `install()`**

`TestModel::init()` in `demo/public/dbtest.php` is exactly "`CREATE TABLE IF NOT EXISTS` + the macro", called once when the controller is constructed.

## Common errors

| Symptom | Cause | Fix |
| --- | --- | --- |
| `no such table` | The DSN points at another database, or the table has not been created | Check `database.dsn` and the initialisation SQL; a relative SQLite DSN needs a writable directory |
| The table has a prefix but cannot be found | The table name was hard-coded in SQL, or the macro is written wrongly | Use `` `'TABLE'` `` (with backticks); confirm `table_prefix` |
| `execute()` returns `0` and looks like "it did not run" | The semantics are **affected row count on success, 0 on failure** | Judge together with exceptions; an `UPDATE` that changes no column also returns 0 |
| A row just written cannot be read from the read database | Replication lag under read/write splitting | For write-then-immediately-read, use `Helper::Db()` (the write connection) explicitly |
| SQLite reports `unable to open database file` | The directory is not writable (`runtime/` permissions) | Fix the directory permissions ([Chapter 1-7](deployment.md)) |
| The transaction has no effect | The transaction and the write are not on the same connection | Do it on one connection; take extra care with read/write splitting |
| User input went straight into SQL | String splicing | Always placeholders; escape identifiers with `quoteScheme()` |
| The pagination total is wrong | The count used SQL that still carried `limit` | Build the count SQL with `Helper::SqlForCountSimply($sql)` |

## Next steps

- [Chapter 2-8 The model layer](model.md): wrap this chapter's capabilities into models so the business layer only sees models.
- [Chapter 2-10 Forms and data validation](validator.md): validating data before it reaches the database.
- [Chapter 3-6 The installer and the web installation flow](installer.md): how `SqlDumper` and `{prefix}` are actually used.
- Reference manual: [DuckPhp\Db\Db](../reference/Db-Db.md), [DuckPhp\Db\DbAdvanceTrait](../reference/Db-DbAdvanceTrait.md), [DuckPhp\Component\DbManager](../reference/Component-DbManager.md), [DuckPhp\Component\Pager](../reference/Component-Pager.md), [DuckPhp\Component\PagerInterface](../reference/Component-PagerInterface.md). The `Ext\SqlDumper*` used for SQL export is in [Chapter 4-13](ext-classes.md) §7.
