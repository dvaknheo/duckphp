# DuckPhp\Foundation\Model\Base

## Introduction

`Model\Base` is the recommended base class (abstract) for a project's Model (data) layer. It composes two traits:

- `ModelTrait`: table-name conventions and the usual query/create/update/delete wrappers (`table()/prepare()/getList()/find()/add()/update()/execute()/fetch*` and so on);
- `ModelHelperTrait`: folds the `DbManager` entry point into static methods such as `Db()/DbForRead()/…`.

A project's data model classes extend this base to get "table name derived from the class name + read/write splitting + `'TABLE'` macro substitution" and so on.

## Class info

- Namespace: `DuckPhp\Foundation\Model`
- Declaration: `abstract class Base`
- Uses traits: `DuckPhp\Foundation\Model\ModelTrait`, `DuckPhp\Foundation\Model\ModelHelperTrait`

## Usage

```php
namespace MyProject\Model;

use DuckPhp\Foundation\Model\Base;

class UserModel extends Base
{
    public function getUser($id)
    {
        return $this->find($id);          // look up by primary key (table inferred from UserModel -> user)
    }
    public function page($where = [], $page = 1)
    {
        return $this->getList($where, $page, 10); // [total, rows]
    }
}
```

## Caveats

- The table-name/table-prefix rules live in `ModelTrait` (strip the trailing `Model` from the class name, lower-case it, then the `table_prefix` option).
- Reads go through `DbForRead()`, writes through `DbForWrite()`, following the read/write splitting convention.

## Methods

This class is a composition base and declares no extra methods (the methods come from `ModelTrait` and `ModelHelperTrait` — see their own pages).

## Related links

- [DuckPhp\Foundation\Model\ModelTrait](Foundation-Model-ModelTrait.md) — table-level CRUD/query wrappers
- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — the data-layer static helpers
- [DuckPhp\Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) — the Model static helper class
