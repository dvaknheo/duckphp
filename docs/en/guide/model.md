# 2-8 The model layer

> What this solves: what actually belongs in a model, what [`ModelTrait`](../reference/Foundation-Model-ModelTrait.md) gives you for free, why its CRUD methods are `protected`, how the table name is derived, and what a model should expose to the business layer.
> Prerequisites: [Chapter 2-1 The four-layer architecture and calling rules](layers.md), [Chapter 2-7 Databases](database.md). About 20 minutes.
> Examples: `demo/src/Model/` (`Base.php` / `DemoModel.php`), `demo/public/dbtest.php` (fully runnable: model + pagination + CRUD), `skeleton/src/Model/` (the model skeleton in the scaffold).

```bash
php -S 127.0.0.1:8080 -t demo/public
# open http://127.0.0.1:8080/dbtest.php to watch the model layer run a full set of CRUD
```

## Minimal example

The two-layer minimal skeleton (real files from `demo/src/Model/`):

```php
// demo/src/Model/Base.php —— the project's own model base class
namespace MyProj\Model;

use DuckPhp\Foundation\Model\ModelTrait;

class Base
{
    use ModelTrait;
}
```

```php
// demo/src/Model/DemoModel.php —— one concrete model
namespace MyProj\Model;

class DemoModel extends Base
{
    public function testdb()
    {
        $sql = "select 1+? as t";
        return Helper::Db()->fetch($sql, 2);       // a model may use Db directly
    }
}
```

A model with full table operations looks like this (`TestModel` from `demo/public/dbtest.php`; visit that page to run it for real):

```php
class TestModel
{
    use ModelTrait;
    public function __construct()
    {
        $this->table_name = 'test';                // the table name (without the prefix)
    }
    public function getDataList($page, $pagesize)
    {
        $sql = "select * from `'TABLE'` order by id desc";
        $total = $this->fetchColumn(Helper::SqlForCountSimply($sql));            // read connection
        $list  = $this->fetchAll(Helper::SqlForPager($sql, $page, $pagesize));   // read connection
        return [$total, $list];
    }
    public function addData($data)
    {
        $this->execute("insert into `'TABLE'` (content) values(?)", $data['content']); // write connection
        return Helper::Db()->lastInsertId();
    }
}
```

## How it works

### 1. What a model is for: data access only

The model layer is the narrowest of the four: **turn a table into methods**, with no business decisions, no business exceptions and no reading of the request context — business rules belong to Business (the violation matrix in [Chapter 2-1](layers.md)).

```php
// ✅ a model: it only answers "what is the data"
public function findByStatus(string $status): array
{
    return $this->fetchAll("select * from `'TABLE'` where status=?", $status);
}

// ❌ what must not be in a model: business decisions, permission checks, business exceptions, reading $_GET
```

### 2. Two routes

| Route | How to write it | Use it for |
| --- | --- | --- |
| **Use `ModelTrait`** | `use ModelTrait;` | Ordinary single-table models: the table-name macro, read/write splitting and pagination are all wired up |
| **Use [`Db`](../reference/Db-Db.md) directly** | Call `Helper::Db()` / `Helper::DbForRead()` in your own class | Complex multi-table queries, reports, cases that need fine control over the SQL |
|  |  |  |

The two mix freely: `demo/src/Model/DemoModel.php` both extends a `Base` that carries `ModelTrait` and calls `Helper::Db()->fetch(...)` directly in a method.

You can use `ModelTrait` without extending `Base` (that is what `TestModel` in `dbtest.php` does) — it is a trait, not a required base class.

### 3. What `ModelTrait` gives you

| Member | Visibility | Meaning |
|---|---|---|
| `$table_name` | protected property | The table name (without prefix); if unset it is derived from the class name |
| `$table_prefix` | protected property | The table prefix; if unset the application option `table_prefix` is used |
| `$table_pk` | protected property | The primary key name, `'id'` by default |
| `table()` | **public** | Returns "prefix + table name", e.g. `app_note` |
| `prepare($sql)` | **public** | Replaces the `` `'TABLE'` `` macro in SQL with `table()` |
| `getList($where, $page, $page_size)` | protected | Returns `[$total, $data]` |
| `find($id_or_where)` | protected | Fetches one row by primary key or by a condition array |
| `add($data)` | protected | Inserts and returns the auto-increment id |
| `update($id, $data, $key)` | protected | Updates by primary key |
| `execute($sql, ...$args)` | protected | A write operation (over the **write** connection) |
| `fetchAll` / `fetch` / `fetchColumn` / `fetchObject` / `fetchObjectAll` | protected | Read operations (over the **read** connection, binding the table-name macro automatically) |
| `::_()` (from [`SingletonExTrait`](../reference/Core-SingletonExTrait.md)) | public | A replaceable singleton, so it can be overridden ([Chapter 3-5](overriding.md)) |

Table-name derivation (`getTableNameByClass()`): drop the trailing `Model` from the class name and lower-case it — `NoteModel` → `note`, `UserProfileModel` → `userprofile`. If that does not fit, set `$this->table_name` explicitly in the constructor.

### 4. Why the CRUD methods are `protected` (important)

```php
NoteModel::_()->add($data);        // ❌ Call to protected method
```

This is **deliberate**: the framework only provides primitives for "building SQL inside a model", and what you expose outward is your decision. So every model should write its own public methods:

```php
class NoteModel extends Base
{
    public function __construct() { $this->table_name = 'note'; }

    public function create(array $data): int                  // the one entrance exposed outward
    {
        return (int)$this->add($data);
    }
    public function paginate(int $page, int $size = 10): array
    {
        return $this->getList([], $page, $size);              // [total, data]
    }
    public function findById(int $id): ?array
    {
        return $this->find($id) ?: null;
    }
}
```

The benefit is very practical: if Business cannot reach a catch-all entrance such as `execute()`, it cannot bypass the model and write raw SQL — the boundary moves from "a convention" to "visibility at the language level".

### 5. Static tools available to models: `ModelHelperTrait`

| Method | Purpose |
|---|---|
| `Helper::Db($tag)` | Get a connection (omitted = write connection, `1` = read connection) |
| `Helper::DbForRead()` / `Helper::DbForWrite()` | Explicit read/write connections |
| `Helper::SqlForPager($sql, $page, $size)` | Add pagination to SQL |
| `Helper::SqlForCountSimply($sql)` | Turn SQL into count SQL |
| `Helper::DatabaseDriver()` | The current driver name (for writing driver branches) |

The project-side `Model\Helper` (`demo/src/Model/Helper.php`) is the one-liner [`extends Model\ModelHelper`](../reference/Foundation-Model-ModelHelper.md), while the model base class [`Model\Base`](../reference/Foundation-Model-Base.md) simply `use`s [`ModelHelperTrait`](../reference/Foundation-Model-ModelHelperTrait.md), so both `$this->Db()` and `$model->Db()` work.

### 6. Cross-database/multiple connections

The model layer has no such thing as a "cross-database model" — to reach a second database, fetch the connection by tag ([Chapter 2-7](database.md)):

```php
class LogModel extends Base
{
    public function __construct() { $this->table_name = 'log'; }

    public function recent(int $n): array
    {
        return Helper::Db(1)->fetchAll('select * from `log` order by id desc limit ' . (int)$n);
    }
}
```

> A reminder: `demo/src/Model/CrossModelEx.php` sounds like a "cross-database model", but its content is an empty `foo()` sample shell — **do not infer usage from the name**; do cross-database work with the tag pattern above.

### 7. The interface contract with the business layer

Name a model's outward methods after "what the business needs", rather than spreading every column of the table outward:

```php
// in Business
$total = NoteModel::_()->countByUser($userId);
$rows  = NoteModel::_()->listByUser($userId, $page);
// the view layer works with pagination: Helper::PageHtml($total) (Chapter 2-7)
```

## Common patterns

**① One model per table, method names that speak business**

```php
public function countByUser(int $userId): int
{
    return (int)$this->fetchColumn("select count(*) from `'TABLE'` where user_id=?", $userId);
}
```

**② Soft delete**

```php
Helper::Db()->deleteData('note', $id);          // DbAdvanceTrait's soft delete, writes is_deleted by default
// or write it yourself in the model: update ... set is_deleted=1 where id=?
```

**③ Put common queries in the project base class instead of copy-pasting**

```php
abstract class Base
{
    use ModelTrait;

    public function paginate(int $page, int $size = 10): array
    {
        return $this->getList([], $page, $size);       // shared by every model
    }
}
```

**④ A read-only model goes explicitly through the read connection**

```php
public function hotList(int $n): array
{
    return Helper::DbForRead()->fetchAll("select * from `'TABLE'` order by views desc limit " . (int)$n);
}
```

**⑤ Transactions belong to the business layer; models only offer atomic operations**

```php
// in Business
$pdo = Helper::Db()->PDO();
$pdo->beginTransaction();
try {
    OrderModel::_()->create($order);
    OrderItemModel::_()->createMany($items);
    $pdo->commit();
} catch (\Throwable $ex) {
    $pdo->rollBack();
    throw $ex;                 // hand it to the exception machinery (Chapter 2-12)
}
```

## Common errors

| Symptom | Cause | Fix |
| --- | --- | --- |
| `Call to protected method ...::add()` | `ModelTrait`'s CRUD is protected (deliberately) | Write a public wrapper method in the model to expose it |
| The table cannot be found | The class-name derivation is not what you expected (`UserProfileModel` → `userprofile`) | Set `$this->table_name = 'user_profile'` explicitly in the constructor |
| The table prefix has no effect | The table name was written out in SQL instead of going through the `` `'TABLE'` `` macro | Use the macro, or call `$this->prepare($sql)` |
| The model throws business exceptions / checks permissions | Out of bounds: that is Business's job | Move it to Business; a model only returns data |
| `Helper::Db()` with raw SQL appears in Business | Out of bounds: it bypassed the model layer | Collect the SQL into the model and let business call model methods only |
| The read connection cannot see data that was just written | Replication lag under read/write splitting; `fetch*` uses the read connection by default | Use the write connection explicitly when you need strong consistency |
| The model has become a several-hundred-line "god class" | Too much business meaning piled onto one table | Send the decision logic back to Business and split methods by business |
| Overriding the model class had no effect | `new NoteModel()` was used instead of `NoteModel::_()` | Always `::_()` (overriding depends on the container, [Chapter 3-5](overriding.md)) |

## Next steps

- [Chapter 2-10 Forms and data validation](validator.md): validation before storage belongs to the business layer.
- [Chapter 2-12 Exceptions and error handling](exception.md): how data-layer errors become a response users can understand.
- [Chapter 3-5 Rewriting and overriding](overriding.md): overriding and replacing models/controllers.
- Reference manual: [DuckPhp\Foundation\Model\ModelTrait](../reference/Foundation-Model-ModelTrait.md), [DuckPhp\Foundation\Model\ModelHelperTrait](../reference/Foundation-Model-ModelHelperTrait.md), [DuckPhp\Db\DbAdvanceTrait](../reference/Db-DbAdvanceTrait.md).
