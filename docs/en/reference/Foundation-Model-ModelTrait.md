# DuckPhp\Foundation\Model\ModelTrait

## Introduction

`ModelTrait` is the packaging of the common capabilities of a data model (Model), composed by `Foundation\Model\Base`. It provides:

- **The table-name convention**: `table()` infers the table name from the class name (the short class name with a trailing `Model` removed and lower-cased, e.g. `UserModel` → `user`), optionally with `table_prefix` (from `App::options['table_prefix']`); it also supports setting `$table_name/$table_prefix/$table_pk` by hand.
- **`'TABLE'` macro substitution**: `prepare()` replaces `` `'TABLE'` `` in SQL with the real table name.
- **Generic queries/write operations** (all going through read/write splitting): `getList()` (paging), `find()`, `add()`, `update()`, `execute()`, `fetch*()` (fetchAll/fetch/fetchColumn/fetchObject/fetchObjectAll).

## Class info

- Namespace: `DuckPhp\Foundation`
- Declaration: `trait ModelTrait`
- Traits used: `DuckPhp\Core\SingletonExTrait`
- Used by: `DuckPhp\Foundation\Model\Base`

## Usage

```php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\ModelTrait;

class OrderModel
{
    use ModelTrait;

    public function myOrders($uid)
    {
        return $this->fetchAll("select * from `'TABLE'` where uid = ?", $uid);
    }
}

$m = OrderModel::_();
echo $m->table();                  // 'order' (may carry table_prefix)
$m->myOrders(7);
```

## Caveats

- **Read/write splitting**: the query side (`fetch*`/`getList`/`find`) goes through `_DbForRead()`; the write side (`add`/`update`/`execute`) goes through `_DbForWrite()`.
- The table name/prefix properties are initialised lazily on the first call to `table()`: `$table_prefix` comes from `App::options['table_prefix']` and `$table_name` is inferred from the class name; a subclass may simply override it with `protected $table_name = 'user';`.
- `getList($where,$page,$page_size)` returns `[$total, $data]`; `$where` is generated with `quoteAndArray` (key=value) and the default sort is `id desc` (hard-coded in the SQL).
- `find($a)`: a scalar is treated as a lookup by `$table_pk` (`id` by default); an array is treated as equality conditions (joined with `and `).
- `fetchObject/fetchObjectAll` set the result class to `static::class` (the model class itself, usually a simple model with public properties).
- Table-level writes reuse the semantics of `DbAdvanceTrait`'s `insertData/updateData` directly (`add` returns the auto-increment ID and so on).

## Methods

### Public methods

    public function table(): string
Returns the real table name (`$table_prefix + $table_name`, inferred lazily).

    public function prepare(string $sql): string
Replaces the `` `'TABLE'` `` macro in SQL with the real table name.

### Protected methods

    protected function getTableNameByClass(string $class): string
Infers the table name from the class name (the short class name with a trailing `Model` removed, then lower-cased).

    protected function getTablePrefixByClass(string $class): string
Gets the table prefix (`App::options['table_prefix']`).

    protected function getList(array $where = [], int $page = 1, int $page_size = 10): array
Paged query (over the read database), returning `[$total, $data]`.

    protected function find($a)
Fetches one row by primary key or by equality conditions (over the read database).

    protected function add(array $data)
Inserts one row (over the write database, returning the auto-increment ID or the execute result).

    protected function update($id, array $data, ?string $key = null)
Updates one row by primary key (over the write database).

    protected function execute(string $sql, ...$args)
Runs a write SQL statement (over the write database, with the table name pre-set).

    protected function fetchAll(string $sql, ...$args): array
Fetches all rows (read database + table name).

    protected function fetch(string $sql, ...$args)
Fetches one row (read database + table name).

    protected function fetchColumn(string $sql, ...$args)
Fetches a single column value (read database + table name).

    protected function fetchObject(string $sql, ...$args)
Fetches one row as an object (the result class is `static::class`).

    protected function fetchObjectAll(string $sql, ...$args): array
Fetches several rows as objects (the result class is `static::class`).

## Related links

- [DuckPhp\Foundation\Model\Base](Foundation-Model-Base.md) — the model base class that composes this Trait
- [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) — the underlying implementations such as insertData/updateData
- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — the companion static helper
