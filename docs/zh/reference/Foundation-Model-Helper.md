# DuckPhp\Foundation\Model\Helper

## 简介

`Model\Helper` 是工程「数据层静态助手」的推荐实现：一个静态类，`use ModelHelperTrait`。Model 代码里 `Helper::Db()`、`Helper::DbForRead()`、`Helper::SqlForPager(...)` 即取到对应连接/工具方法。

## 类信息

- 命名空间：`DuckPhp\Foundation\Model`
- 声明：`class Helper`
- 使用的 Trait：`DuckPhp\Helper\ModelHelperTrait`

## 使用方式

```php
use DuckPhp\Foundation\Model\Helper;

$rows = Helper::DbForRead()->fetchAll('select * from log where uid = ?', $uid);
$sql  = Helper::SqlForPager('select * from log', 1, 20);
```

## 注意事项

- Helper 类不保存状态；方法全部来自 `ModelHelperTrait`（转发到 `DbManager`）。

## 方法列表

本类方法全部由 `ModelHelperTrait` 提供（6 个静态方法），签名与说明见 [Helper-ModelHelperTrait](Helper-ModelHelperTrait.md)。

## 相关链接

- [DuckPhp\Helper\ModelHelperTrait](Helper-ModelHelperTrait.md) — 方法来源
- [DuckPhp\Foundation\Model\Base](Foundation-Model-Base.md) — 数据模型基类
