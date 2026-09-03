# DuckPhp\Db\DbInterface

## 简介

`DbInterface` 是 DuckPHP 数据库连接对象的契约接口。它规定了「一个 DB 连接对象必须提供」的最小方法集：连接（`PDO()`）、关闭（`close()`）、安全引用（`quote()`）、查询（`fetch*`）、执行（`execute()`）与元信息（`rowCount()`/`lastInsertId()`）。

框架中的 `DuckPhp\Db\Db` 实现了该接口；`Component\DbManager` 对上层返回的便是实现了本接口的连接对象，因此业务层代码只需要面向本接口编程即可（如 `Helper::Db()->fetchAll(...)`）。

## 类信息

- 命名空间：`DuckPhp\Db`
- 声明：`interface DbInterface`

## 使用方式

一般不需要直接 `implements` 本接口。需要自定义数据库连接实现时，实现这些方法即可与 `DbManager` 配合：

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

## 注意事项

- 本接口只列「基础读写」方法；更丰富的便捷方法（`quoteIn`/`findData`/`insertData`/`updateData`/`deleteData`/`fetchObject` 等）由 `Db` 与 `DbAdvanceTrait` 在接口之外提供，不属于本契约。

## 方法列表

### 公共方法

    public function close()
关闭连接（实现方通常置空 PDO 对象并复位行数）。

    public function PDO($object = null)
取得（或传入替换）底层 PDO 对象；`$object` 非空时替换。

    public function quote($string)
对字符串做安全引用（防止注入），返回引号包裹后的 SQL 片段。

    public function fetchAll($sql, ...$args)
执行查询并返回全部结果（关联数组行）。

    public function fetch($sql, ...$args)
执行查询并返回一行（关联数组）。

    public function fetchColumn($sql, ...$args)
执行查询并返回第一行第一列的值。

    public function execute($sql, ...$args)
执行写操作类 SQL，返回是否成功。

    public function rowCount()
返回最近一次受影响的行数。

    public function lastInsertId()
返回最近一次插入的自增 ID。

## 相关链接

- [DuckPhp\Db\Db](Db-Db.md) — 本接口的默认实现
- [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) — Db 附带的便捷方法 Trait
