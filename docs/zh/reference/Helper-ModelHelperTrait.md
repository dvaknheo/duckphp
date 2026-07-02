# DuckPhp\Helper\ModelHelperTrait

模型层 Helper Trait。

## 简介

`DuckPhp\Helper\ModelHelperTrait` 提供模型层访问数据库的静态方法，包括获取数据库对象、读写分离数据库对象以及分页/计数 SQL 辅助方法。

## 选项

无。

## 使用方式

### 在类中引入

```php
use DuckPhp\Helper\ModelHelperTrait;

class MyModelHelper
{
    use ModelHelperTrait;
}
```

### 常用操作

```php
use DuckPhp\Foundation\Model\Helper;

// 获取数据库对象
$db = Helper::Db();

// 读写分离
$readDb = Helper::DbForRead();
$writeDb = Helper::DbForWrite();

// 分页与计数
$pageSql = Helper::SqlForPager($sql, 1, 10);
$countSql = Helper::SqlForCountSimply($sql);
```

## 注意事项

1. 该 Trait 使用 `DuckPhp\Core\SingletonTrait`，引入类后具备单例访问能力。
2. 方法依赖 `DuckPhp\Component\DbManager`，需确保数据库组件已配置。

## 方法列表

### 公共方法

| 方法 | 说明 |
|---|---|
| `Db($tag = null)` | 获取指定标签的数据库对象 |
| `DbForRead()` | 获取读库数据库对象 |
| `DbForWrite()` | 获取写库数据库对象 |
| `SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string` | 为 SQL 添加分页子句 |
| `SqlForCountSimply(string $sql): string` | 生成简单的计数 SQL |

## 相关链接

- [DuckPhp\Foundation\Model\Helper](Foundation-Model-Helper.md)
