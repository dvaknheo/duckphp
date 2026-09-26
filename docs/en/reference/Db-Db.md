# DuckPhp\Db\Db

## Introduction

`Db` is DuckPHP's default database connection object; it implements `DbInterface` and composes `DbAdvanceTrait`. It wraps a PDO instance directly (the `$pdo` property) and offers:

- connection management: `init()`/`check_connect()`/`close()`/`PDO()`;
- safe quoting and SQL-building helpers: `quote()`/`quoteScheme()`/`buildQueryString()`/`doTableNameMacro()`;
- querying: the `fetch*` family (associative arrays / single columns / objects);
- execution and results: `execute()`/`rowCount()`/`lastInsertId()`;
- a before-query hook: `setBeforeQueryHandler()`;
- the object result class / the table-name shortcut: `setObjectResultClass()`/`table()`.

This class **does not extend `ComponentBase`** (it does not depend on the phase container); instances are usually created by `Component\DbManager`, which injects the configuration (`dsn`/`username`/`password`/`driver_options`), and the business layer calls them directly after getting them from `Helper::Db()` and friends.

## Class info

- Namespace: `DuckPhp\Db`
- Declaration: `class Db implements DbInterface`
- Traits used: `DuckPhp\Db\DbAdvanceTrait` (its methods are in [Db-DbAdvanceTrait](Db-DbAdvanceTrait.md) and are not repeated here)

## Configuration

`Db` has no `ComponentBase`-style `$options` property; `init(array $options)` stores the array it is given as `public $config`, and connecting uses the following keys:

| Key | Default | Meaning |
|---|---|---|
| `dsn` | none (required) | The PDO DSN, e.g. `mysql:host=127.0.0.1;dbname=test`; after connecting, the `driver` is recognised from the part before the `:` (`sqlite`/`mysql`/`pgsql`…). |
| `username` | `null` | The database user name. |
| `password` | `null` | The database password. |
| `driver_options` | `[]` | Extra PDO options, deeply merged with the defaults (defaults: `\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION`, `\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC`). |

## Usage

```php
use DuckPhp\Db\Db;

$db = (new Db())->init([
    'dsn'      => 'mysql:host=127.0.0.1;dbname=test',
    'username' => 'root',
    'password' => 'secret',
]);

$rows = $db->fetchAll('select * from user where age > ?', 18);
$one  = $db->fetch('select * from user where id = :id', ['id' => 3]);
$name = $db->fetchColumn('select name from user where id = ?', 3);
```

### The table-name macro and chained setup

```php
$db->table('user');                       // from now on `'TABLE'` in SQL is replaced by `user`
$db->fetchAll("select * from `'TABLE'` where status = 1");
// what actually runs: select * from `user` where status = 1

$db->setObjectResultClass(MyUser::class); // the result class of fetchObject(All)
```

### The before-query hook

```php
$db->setBeforeQueryHandler(function ($db, $sql, ...$args) {
    // called before every prepare; good for slow-query logging and the like
});
```

### Quoting and building SQL

```php
$idList = $db->quote([1, 2, 3]);          // each array element quoted → "'1','2','3'"
$frag   = $db->buildQueryString(
    'select * from t where a = :a and b = :b',
    ['a' => 1, 'b' => 'x']                // a single array argument: replaces :key
);
```

## Caveats

- `quote()` quotes an array element by element recursively and returns a comma-joined string; values that are neither strings nor arrays are returned as they are.
- `buildQueryString()`: with a single array argument it replaces the `:key` placeholders with the quoted values; with several arguments it replaces the `?` placeholders in the SQL in order; with no arguments it returns the SQL as it is.
- `quoteScheme()` adds the identifier shell according to `driver`: double quotes for `sqlite`, backticks for `mysql`, single quotes for `pgsql`, and no change otherwise.
- `exec()` is the single internal execution point: it fires `beforeQueryHandler` first, then does the table-name macro substitution, `prepare` + `execute`, and records `$this->success`.
- When looking at `execute()`, note that it returns **whether it succeeded** (`$this->success`), while `rowCount()` gives the **affected row count** — on success it takes `$sth->rowCount()`, on failure it is 0 (a failure never touches the statement).
- `fetchAll/fetch/fetchColumn` use `\PDO::FETCH_ASSOC`; `fetchObject/fetchObjectAll` use the result class set by `setObjectResultClass()` (`\stdClass` by default).

## Methods

### Public methods

    public function init($options = [], ?object $context = null)
Takes the configuration array into `$config` and fires `check_connect()` to establish the connection (it does not follow the component base-class flow; the `$context` argument is ignored).

    public function close(): void
Closes the connection: clears the row count and sets `$pdo` to `null`.

    public function PDO($pdo = null)
When `$pdo` is not null it replaces the internal PDO; afterwards it makes sure the connection exists and returns the current `$this->pdo`.

    public function setBeforeQueryHandler($handler)
Sets the before-query callback: `($handler)($this, $sql, ...$args)`, fired before every `exec()` prepares SQL.

    public function quote($string)
Safe quoting: an array is quoted recursively element by element and joined with `,`; a string goes through `$pdo->quote()`; any other type is returned as it is.

    public function quoteScheme($name)
Adds the identifier shell according to the driver: double quotes for `sqlite`, backticks for `mysql`, single quotes for `pgsql`, unchanged by default.

    public function buildQueryString($sql, ...$args)
Replaces the SQL placeholders with quoted values according to the arguments: a single array argument replaces `:key`, several arguments replace `?`, and no arguments returns the SQL unchanged.

    public function table($table_name)
Records the current table name (for the `` `'TABLE'` `` macro substitution) and returns itself for chaining.

    public function doTableNameMacro($sql)
When a table name has been set, replaces `` `'TABLE'` `` in the SQL with `` `the table name` ``.

    public function setObjectResultClass($resultClass)
Sets the result class for `fetchObject`/`fetchObjectAll` and returns itself.

    public function fetchAll($sql, ...$args)
Executes and returns all rows (`\PDO::FETCH_ASSOC`).

    public function fetch($sql, ...$args)
Executes and returns one row (`\PDO::FETCH_ASSOC`).

    public function fetchColumn($sql, ...$args)
Executes and returns the first column of the first row.

    public function fetchObject($sql, ...$args)
Executes and returns one `$resultClass` object (`\stdClass` by default).

    public function fetchObjectAll($sql, ...$args)
Executes and returns an array of `$resultClass` objects (`\PDO::FETCH_CLASS`).

    public function execute($sql, ...$args)
Executes SQL and returns whether it succeeded (`$this->success`); internally it also records `rowCount` (affected row count on success, 0 on failure).

    public function rowCount(): int
Returns the affected row count recorded by the most recent execution.

    public function lastInsertId()
Returns the auto-increment id of the most recent insert (passed straight through to PDO).

### Protected methods

    protected function check_connect(): void
When `$pdo` is empty, creates the PDO from `$config`: merges `driver_options` and recognises `driver` from the `:`.

    protected function exec(string $sql, ...$args): \PDOStatement
The single execution point: fires the before-query hook → normalises the arguments (unwrapping a single array) → substitutes the table-name macro → `prepare` + `execute` → records `$this->success` and returns the statement.

## Related links

- [DuckPhp\Db\DbInterface](Db-DbInterface.md) — the contract interface this class implements
- [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) — convenience methods such as findData/insertData/quoteIn
- [DuckPhp\Component\DbManager](Component-DbManager.md) — the database management component (it creates and injects this class)
