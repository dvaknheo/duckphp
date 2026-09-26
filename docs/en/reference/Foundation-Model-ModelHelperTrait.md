# DuckPhp\Foundation\Model\ModelHelperTrait

## Introduction

`ModelHelperTrait` is a collection of static helpers aimed at the **Model (data) layer**. It folds the common entry points of `Component\DbManager` into a set of static methods, so that Model-layer code can be written as calls such as `self::Db(...)` and `self::DbForRead()` without touching the `DbManager` component directly.

The trait only `use`s `SingletonExTrait` (which provides the `_()` static entry point) and brings in no extra state. In the framework both [Foundation\Model\Base](Foundation-Model-Base.md) and [Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) use it; in a project your own Model base class can `use` it as well. `DuckPhpAllInOne` / `Foundation\Helper` do not hold it directly — the union is dispatched to `Model\ModelHelper` through `__callStatic`.

## Class info

- Namespace: `DuckPhp\Foundation\Model`
- Declaration: `trait ModelHelperTrait`
- Traits used: `DuckPhp\Core\SingletonExTrait`

## Usage

```php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\ModelHelperTrait;

class Base
{
    use ModelHelperTrait;
}

// inside a Model:
$rows = Base::Db()->fetchAll('select * from user');
$row  = Base::DbForRead()->fetch('select * from log where id = ?', 1);
```

## Caveats

- Every method is a `public static` forward: `Db/DbForRead/DbForWrite` delegate to `DbManager` (corresponding to `_Db($tag)`, `_DbForRead()`, `_DbForWrite()`).
- `SqlForPager`/`SqlForCountSimply`/`DatabaseDriver` also delegate to `DbManager` (that is, the connection layer); they are not methods of the `Db` instance itself.

## Methods

### Public methods

    public static function Db($tag = null)
Gets a database connection (`$tag` names the database; null uses the default/master), returning `DuckPhp\Db\Db`.

    public static function DbForRead()
Gets the read-only connection (the read end of read/write splitting), returning `DuckPhp\Db\Db`.

    public static function DbForWrite()
Gets the write connection (the write end of read/write splitting), returning `DuckPhp\Db\Db`.

    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
Appends a paging `LIMIT` to SQL (through the connection-layer helper of DbManager).

    public static function SqlForCountSimply(string $sql): string
Rewrites a simple `select … from` into `SELECT COUNT(*) as c FROM` (through DbManager).

    public static function DatabaseDriver(): string
Returns the current database driver name (such as `mysql`/`sqlite`).

## Related links

- [DuckPhp\Component\DbManager](Component-DbManager.md) — the main forwarding target of this Trait
- [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) — the business layer helper
- [DuckPhp\Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) — the engineered Model helper class
