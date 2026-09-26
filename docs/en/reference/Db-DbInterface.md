# DuckPhp\Db\DbInterface

## Introduction

`DbInterface` is the contract interface for DuckPHP's database connection objects. It defines the minimal method set "a DB connection object must provide": connecting (`PDO()`), closing (`close()`), safe quoting (`quote()`), querying (`fetch*`), execution (`execute()`) and metadata (`rowCount()`/`lastInsertId()`).

`DuckPhp\Db\Db` in the framework implements this interface, and what `Component\DbManager` returns to the layer above is a connection object implementing it, so business-layer code only has to program against this interface (for example `Helper::Db()->fetchAll(...)`).

## Class info

- Namespace: `DuckPhp\Db`
- Declaration: `interface DbInterface`

## Usage

Normally there is no need to `implements` this interface directly. When you need a custom database connection implementation, implementing these methods is enough to work with `DbManager`:

```php
namespace My\Db;

use DuckPhp\Db\DbInterface;

class MyDb implements DbInterface
{
    public function close() { /* … */ }
    public function PDO($object = null) { /* … */ }
    public function quote($string) { /* … */ }
    public function fetchAll($sql, ...$args) { /* … */ }
    public function fetch($sql, ...$args) { /* … */ }
    public function fetchColumn($sql, ...$args) { /* … */ }
    public function execute($sql, ...$args) { /* … */ }
    public function rowCount() { /* … */ }
    public function lastInsertId() { /* … */ }
}
```

## Caveats

- This interface lists only the "basic read/write" methods; the richer convenience methods (`quoteIn`/`findData`/`insertData`/`updateData`/`deleteData`/`fetchObject` and so on) are provided by `Db` and `DbAdvanceTrait` outside the interface and are not part of this contract.

## Methods

### Public methods

    public function close()
Closes the connection (an implementation usually empties the PDO object and resets the row count).

    public function PDO($object = null)
Gets the underlying PDO object (or passes one in to replace it); when `$object` is not null it replaces it.

    public function quote($string)
Safely quotes a string (guarding against injection) and returns the quoted SQL fragment.

    public function fetchAll($sql, ...$args)
Executes a query and returns all results (rows as associative arrays).

    public function fetch($sql, ...$args)
Executes a query and returns one row (an associative array).

    public function fetchColumn($sql, ...$args)
Executes a query and returns the value in the first column of the first row.

    public function execute($sql, ...$args)
Executes a write-style SQL statement and returns whether it succeeded.

    public function rowCount()
Returns the affected row count of the most recent execution.

    public function lastInsertId()
Returns the auto-increment id of the most recent insert.

## Related links

- [DuckPhp\Db\Db](Db-Db.md) — the default implementation of this interface
- [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) — the convenience-method Trait that comes with Db
