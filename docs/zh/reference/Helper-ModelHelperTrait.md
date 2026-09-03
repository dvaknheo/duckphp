# DuckPhp\Helper\ModelHelperTrait

## 简介

`ModelHelperTrait` 是面向 **Model（数据层）** 的静态助手集合。它把 `Component\DbManager` 的常用入口折叠成一组静态方法，使 Model 层代码可以写作 `self::Db(...)`、`self::DbForRead()` 这类调用，而无需直接接触 `DbManager` 组件。

该 Trait 仅 `use SingletonExTrait`（提供 `_()` 静态入口），不引入额外状态；框架的 `DuckPhpAllInOne` 会组合它，工程里也可由你自己的 Model 基类组合。

## 类信息

- 命名空间：`DuckPhp\Helper`
- 声明：`trait ModelHelperTrait`
- 使用的 Trait：`DuckPhp\Core\SingletonExTrait`

## 使用方式

```php
namespace MyProject\Model;

use DuckPhp\Helper\ModelHelperTrait;

class Base
{
    use ModelHelperTrait;
}

// 在 Model 内：
$rows = Base::Db()->fetchAll('select * from user');
$row  = Base::DbForRead()->fetch('select * from log where id = ?', 1);
```

## 注意事项

- 所有方法都是 `public static` 转发：`Db/DbForRead/DbForWrite` 委托 `DbManager`（分别对应 `_Db($tag)`、`_DbForRead()`、`_DbForWrite()`）。
- `SqlForPager`/`SqlForCountSimply`/`DatabaseDriver` 也委托 `DbManager`（即连接层），不是 `Db` 实例自身的方法。

## 方法列表

### 公共方法

    public static function Db($tag = null)
取数据库连接（`$tag` 指定库，null 用默认/主库），返回 `DuckPhp\Db\Db`。

    public static function DbForRead()
取只读连接（读写分离的读端），返回 `DuckPhp\Db\Db`。

    public static function DbForWrite()
取写连接（读写分离的写端），返回 `DuckPhp\Db\Db`。

    public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string
给 SQL 追加分页 `LIMIT`（经由 DbManager 的连接层辅助）。

    public static function SqlForCountSimply(string $sql): string
把简单 `select … from` 改写成 `SELECT COUNT(*) as c FROM`（经由 DbManager）。

    public static function DatabaseDriver(): string
返回当前数据库驱动名（如 `mysql`/`sqlite`）。

## 相关链接

- [DuckPhp\Component\DbManager](Component-DbManager.md) — 本 Trait 的主要转发目标
- [DuckPhp\Helper\BusinessHelperTrait](Helper-BusinessHelperTrait.md) — 业务层助手
- [DuckPhp\Foundation\Model\Helper](Foundation-Model-Helper.md) — 工程化 Model 助手类
