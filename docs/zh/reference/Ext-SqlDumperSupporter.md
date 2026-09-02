# DuckPhp\Ext\SqlDumperSupporter

`DuckPhp\Ext\SqlDumperSupporter` 数据库结构导出组件。

## 简介

`SqlDumperSupporter` 是 `DuckPhp\Ext` 命名空间下的 类，由 DuckPhp 框架提供。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `database_driver_SqlDumperSupporter_map` | `['mysql' => SqlDumperSupporterByMysql::class, 'sqlite' => SqlDumperSupporterBySqlite::class]` | 数据库驱动到支持类映射。 |

## 使用方式

### 基本用法

```php
use DuckPhp\Ext\SqlDumperSupporter;

$obj = SqlDumperSupporter::_();
```

## 注意事项

1. 本类为框架内部或扩展组件，通常由框架自动加载。
2. 如需自定义行为，可继承本类并覆盖相应方法。

## 方法列表

### 公共方法

    static function Current()

    function getSqlDumperSupporter(): self

    function getAllTable(): array

    function getSchemeByTable(string $table): string

## 相关链接

- [中文参考手册目录](index.md)
