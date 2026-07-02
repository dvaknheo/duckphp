# DuckPhp\Foundation\Model\Helper

模型层 Helper 类。

## 简介

`DuckPhp\Foundation\Model\Helper` 聚合了 `DuckPhp\Helper\ModelHelperTrait`，为模型层提供统一的静态方法入口，用于获取数据库连接、读写分离数据库对象以及分页/计数 SQL 辅助方法。

## 选项

无。

## 使用方式

### 静态调用

```php
use DuckPhp\Foundation\Model\Helper;

// 获取数据库对象
$db = Helper::Db();

// 读写分离
$readDb = Helper::DbForRead();
$writeDb = Helper::DbForWrite();

// 分页 SQL
$sql = Helper::SqlForPager($sql, $pageNo, $pageSize);
$countSql = Helper::SqlForCountSimply($sql);
```

### 在 Model 类中使用

模型类通常使用 `DuckPhp\Foundation\SimpleModelTrait`，也可以直接通过 `Helper` 类访问这些数据库能力。

## 注意事项

1. 该类没有任何自有方法，所有方法均来自 `DuckPhp\Helper\ModelHelperTrait`。
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

- [DuckPhp\Helper\ModelHelperTrait](Helper-ModelHelperTrait.md)
