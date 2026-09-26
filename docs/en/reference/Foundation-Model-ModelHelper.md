# DuckPhp\Foundation\Model\ModelHelper

## Introduction

`Model\ModelHelper` is a **thin shell class** for the data layer: it declares no methods of its own, it just `use`s [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) to get six static helpers — for the cases where you want a class without touching an inheritance chain (e.g. `ModelHelper::Db()` inside a utility class).

## Class info

- Namespace: `DuckPhp\Foundation\Model`
- Declaration: `class ModelHelper`
- Uses trait: `DuckPhp\Foundation\Model\ModelHelperTrait`

## Usage

```php
use DuckPhp\Foundation\Model\ModelHelper as Helper;

$rows = Helper::DbForRead()->fetchAll('select * from log where uid = ?', $uid);
$sql  = Helper::SqlForPager('select * from log', 1, 20);
```

## Caveats

- This class keeps no state; all six methods come from `ModelHelperTrait` (which forwards to `DbManager`), so both `Helper::Db()` and `$model->Db()` are available.

## Methods

Every method here is provided by `ModelHelperTrait` (six static methods); see [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) for signatures and descriptions.

## Related links

- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — where the methods come from (the only implementation)
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — the four-layer union facade (`__callStatic`; this layer is its last lookup target)
- [DuckPhp\Foundation\Model\Base](Foundation-Model-Base.md) — the data model base class
