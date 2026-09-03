# DuckPhp\Ext\SqlDumperSupporter

## 简介

`SqlDumperSupporter` 是 `SqlDumper` 的驱动适配基类：把“按驱动取全部表 / 取某表建表结构”的差异隔离到子类。默认提供 `mysql`/`sqlite` 两个驱动的映射；其它驱动需扩展 `database_driver_SqlDumperSupporter_map` 或实现子类。

基类 `getAllTable()`/`getSchemeByTable()` 抛 `No Impelement` 占位，真正实现见各子类。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class SqlDumperSupporter extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `database_driver_SqlDumperSupporter_map` | `['mysql' => SqlDumperSupporterByMysql::class, 'sqlite' => SqlDumperSupporterBySqlite::class]` | 驱动名 → 适配子类映射。 |

## 使用方式

```php
$supporter = SqlDumperSupporter::Current();   // 按当前驱动取适配实例
$tables = $supporter->getAllTable();
$ddl    = $supporter->getSchemeByTable($table);
```

## 注意事项

- `Current()`（静态）与 `getSqlDumperSupporter()`：以 `DbManager::getDatabaseDriver()` 为键查映射；无匹配驱动抛 `Exception`（例如 pgsql 需自己把 `SqlDumperSupporterByPgsql` 加入 map）。
- 需要支持新驱动时，继承本类实现两个方法，并把类加进 `database_driver_SqlDumperSupporter_map`。

## 方法列表

### 公共方法

    public static function Current()
静态：取当前驱动对应的 supporter 实例。

    public function getSqlDumperSupporter(): self
按当前驱动查映射并返回对应子类实例。

    public function getAllTable(): array
基类占位（抛 “No Impelement”），子类实现取全部表。

    public function getSchemeByTable(string $table): string
基类占位（抛 “No Impelement”），子类实现取建表结构。

## 相关链接

- [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) — 使用方
- [DuckPhp\Ext\SqlDumperSupporterByMysql](Ext-SqlDumperSupporterByMysql.md) / [BySqlite](Ext-SqlDumperSupporterBySqlite.md) / [ByPgsql](Ext-SqlDumperSupporterByPgsql.md) — 各驱动实现
