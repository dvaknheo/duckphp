# DuckPhp\Foundation\Model\ModelHelper

## 简介

`Model\ModelHelper` 是数据层的**薄壳类**：本身不写方法，`use` [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) 拿到 6 个静态助手，供「想用类、不想动继承链」的场合（例如工具类里 `ModelHelper::Db()`）。

## 类信息

- 命名空间：`DuckPhp\Foundation\Model`
- 声明：`class ModelHelper`
- 使用的 Trait：`DuckPhp\Foundation\Model\ModelHelperTrait`

## 使用方式

```php
use DuckPhp\Foundation\Model\ModelHelper as Helper;

$rows = Helper::DbForRead()->fetchAll('select * from log where uid = ?', $uid);
$sql  = Helper::SqlForPager('select * from log', 1, 20);
```

## 注意事项

- 本类不保存状态；6 个方法全部来自 `ModelHelperTrait`（转发到 `DbManager`），因此 `Helper::Db()` 与 `$model->Db()` 都可用。

## 方法列表

本类方法全部由 `ModelHelperTrait` 提供（6 个静态方法），签名与说明见 [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md)。

## 相关链接

- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — 方法来源（唯一实现处）
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — 四层并集门面（`__callStatic`，本层是它的最后查找目标）
- [DuckPhp\Foundation\Model\Base](Foundation-Model-Base.md) — 数据模型基类
