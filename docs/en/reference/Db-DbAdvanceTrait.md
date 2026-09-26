# DuckPhp\Db\DbAdvanceTrait

## Introduction

`DbAdvanceTrait` is a set of methods added to a `Db` connection for "convenient data operations"; `DuckPhp\Db\Db` composes it with `use DbAdvanceTrait`. It relies on capabilities the host class already has: `quote()` (quoting), `quoteScheme()` (the identifier shell), `$pdo` (the PDO instance) and `fetch()`/`execute()`/`fetchAll()` (basic reads and writes).

Its content falls into two groups:

- **SQL fragment generation**: `quoteIn()`, `quoteSetArray()`, `quoteAndArray()`, `quoteInsertArray()`;
- **table-level convenience operations**: `findData()`, `insertData()`, `updateData()`, `deleteData()` (soft delete `is_deleted=1` by default);
- pagination/count helpers: `_SqlForPager()`, `_SqlForCountSimply()`.

## Class info

- Namespace: `DuckPhp\Db`
- Declaration: `trait DbAdvanceTrait`
- Used by: `DuckPhp\Db\Db` (`use DbAdvanceTrait`)

## Usage

```php
// assume $db is a Db instance (which composes this Trait)
$db->table('user');

$id = $db->insertData('user', ['name' => 'duck', 'age' => 18]);   // returns lastInsertId
$db->updateData('user', $id, ['name' => 'duck2']);
$row = $db->findData('user', $id);
$db->deleteData('user', $id);                                      // soft delete by default: set is_deleted=1
$db->deleteData('user', $id, 'id', null);                          // passing null deletes for real

$sql = $db->_SqlForPager('select * from log', 2, 10);              // … LIMIT 10,10
$cnt = $db->fetchColumn($db->_SqlForCountSimply('select * from log'));
```

### Building SQL fragments

```php
$in   = $db->quoteIn([1, 2, 3]);          // '1','2','3' (an empty array returns NULL)
$set  = $db->quoteSetArray(['name' => 'a', 'age' => 1]);   // `name`='a',`age`='1'
$and  = $db->quoteAndArray(['a' => 1]);   // `a`='1'
$ins  = $db->quoteInsertArray(['name' => 'a', 'age' => 1]); // (`name`,`age`)VALUES('a','1')
```

## Caveats

- This Trait is "purely additive": it does not require the host to extend `ComponentBase`, only to provide the members listed above.
- The 4th argument `$key_delete` of `deleteData()` defaults to `'is_deleted'`: when it is not empty, `update … set is_deleted=1` runs (a soft delete); only an empty value (such as `null`) performs a real `delete`.
- When the 3rd argument `$return_last_id` of `insertData()` is `true` it returns `lastInsertId()`, otherwise the boolean result of `execute()`.
- `updateData()` first removes the primary-key field from the data (by default `$key='id'`) before generating the `SET` fragment.
- `quoteSetArray()` and `quoteAndArray()` differ only in the fragment joiner (`,` versus `and`), and `quoteAndArray` joins with `and` (the separator carries no leading space, matching the source's behaviour as written).
- `_SqlForCountSimply()` uses a regex to replace the `select … from` head with `SELECT COUNT(*) as c FROM` (it does not handle subqueries or complex SQL; it only suits simple queries).

## Methods

### Public methods

    public function quoteIn(array $array): string
Turns an array into a "comma-joined string of quoted values" (e.g. `'a','b'`); an empty array returns `NULL`.

    public function quoteSetArray(array $array): string
Generates a SET fragment: `identifier=quoted value,identifier=quoted value…` (for `update … set`).

    public function quoteAndArray(array $array): string
Generates a WHERE fragment: `identifier=quoted value and identifier=quoted value…` (the source joins with `and`, with no leading space).

    public function quoteInsertArray(array $array): string
Generates the INSERT value fragment: `(`key`,`key`)VALUES('v','v')`; an empty array returns an empty string.

    public function findData($table_name, $id, $key = 'id')
Fetches one row by primary key: `select * from table where key=? limit 1`, returning the associative array from `fetch()`.

    public function insertData($table_name, $data, $return_last_id = true)
Inserts one row: returns `lastInsertId()` by default; with `$return_last_id=false` it returns the result of `execute()`.

    public function deleteData($table_name, $id, $key = 'id', $key_delete = 'is_deleted')
Deletes one row: when `$key_delete` is not empty it is a soft delete (`set is_deleted=1`), otherwise a real delete.

    public function updateData($table_name, $id, $data, $key = 'id')
Updates one row by primary key: removes the primary-key field from `$data` first, then `update … set … where key=?`.

    public function _SqlForPager($sql, $page_no, $page_size = 10)
Appends pagination to SQL: `LIMIT start,page_size` (`start=(page_no-1)*page_size`).

    public function _SqlForCountSimply($sql)
Replaces the `select … from` head of a simple query with `SELECT COUNT(*) as c FROM`, for fetching a total count quickly.

## Related links

- [DuckPhp\Db\Db](Db-Db.md) — the connection implementation that composes this Trait
- [DuckPhp\Db\DbInterface](Db-DbInterface.md) — Db's basic contract
