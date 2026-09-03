# DuckPhp\Ext\SqlDumper

## 简介

`SqlDumper` 是数据库“结构/数据导出与安装”扩展：它把当前数据库导出为若干 SQL 文件（可含表前缀占位符 `{prefix}`），并在需要时执行这些文件完成建库/装数据（`install`）。

导出内容按驱动命名三个文件（存到 `path_sql_dump`，默认 `config/`）：
- `{driver}.sql`：建表结构（`CREATE TABLE …`）；
- `{driver}.clean.sql`：清表（`DROP TABLE IF EXISTS …`，供强制重装）；
- `{driver}.data.sql`：数据（`INSERT INTO …`，仅对 `sql_dump_data_tables` 列出的表）。

支持按 Model 类自动搜集表、显式 include/exclude、以及前缀占位替换。驱动相关的取表/取结构逻辑由 `SqlDumperSupporter` 族实现。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class SqlDumper extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径（配合 `path_sql_dump` 定位导出目录）。 |
| `path_sql_dump` | `'config'` | SQL 文件存放子目录。 |
| `sql_dump_include_tables` | `[]` | 显式包含的表（支持 `@` 占位替换为表前缀）。 |
| `sql_dump_exclude_tables` | `[]` | 排除的表。 |
| `sql_dump_data_tables` | `[]` | 需要导出数据的表（写进 `.data.sql`）。 |
| `sql_dump_include_tables_all` | `false` | 为 `true` 时导出数据库全部表（忽略 by_model）。 |
| `sql_dump_include_tables_by_model` | `true` | 按工程 Model 类（`namespace\Model\Base` 同目录下类）自动搜集表。 |
| `sql_dump_debug_show_sql` | `false` | 执行 SQL 时打印每条语句。 |

## 使用方式

```php
\DuckPhp\Ext\SqlDumper::_()->init([
    'path' => __DIR__.'/../',
    'path_sql_dump' => 'config',
    'sql_dump_data_tables' => ['user'],
], $app);

SqlDumper::_()->dump();             // 导出 mysql.sql / mysql.clean.sql / mysql.data.sql
SqlDumper::_()->install();          // 执行 mysql.sql（+ data.sql）
SqlDumper::_()->install(true);      // 先执行 clean.sql 再装（force）
```

## 注意事项

- 表选择优先级：`sql_dump_include_tables_all` → 否则 = `sql_dump_include_tables_by_model` 搜出的表 ∪ `sql_dump_include_tables`，再剔除 `sql_dump_exclude_tables`，最后只保留带 `table_prefix`（若有）的表。
- 结构 SQL 与 clean/data SQL 中的真实前缀会被替换为 `{prefix}` 占位（`replacePrefixToPlaceholder`），`install` 时再替换回 `App::options['table_prefix']`。
- `searchTables` 通过反射 `namespace\Model\Base` 所在目录扫描 Model 类并调用 `table()`。
- `install($force)`：`$force=true` 时先执行 `{driver}.clean.sql`；随后执行 `{driver}.sql`，存在 `{driver}.data.sql` 时再执行。
- 分句执行按 `;\n` 拆 SQL；`DbManager::Db()->execute` 逐条跑（debug 开关可回显）。

## 方法列表

### 公共方法

    public function dump()
导出当前库：写 `{driver}.sql/.clean.sql/.data.sql` 三个文件；无数据库驱动时返回 `false`。

    public function install(bool $force = false): void
执行导出的 SQL 文件（可选先 clean），把 `{prefix}` 换回实际表前缀。

### 受保护方法

    protected function writeDumpFile(string $file, string $string): void
把内容写到 `path/path_sql_dump/file`。

    protected function executeSqlFile(string $file, string $prefix): void
读文件、替换前缀、按 `;\n` 分句逐条执行。

    protected function getTables(): array
计算本次要处理的表清单（合并/排除/前缀过滤/排序）。

    protected function getSchemes(): string
拼接各表的结构 SQL（前缀替换为 `{prefix}`）。

    protected function getCleanTableSql(): string
生成 `DROP TABLE IF EXISTS …` 语句（表名带 `{prefix}`）。

    protected function replacePrefixToPlaceholder(string $sql): string
把 SQL 中真实表前缀替换为 `{prefix}` 占位。

    protected function getInsertTableSql(): string
对 `sql_dump_data_tables` 逐表生成 INSERT 语句。

    protected function getDataSql(string $table): string
查询整表数据并生成 `INSERT INTO … VALUES` 语句。

    protected function getModelPath(): string
由 `namespace\Model\Base` 反射得到 Model 目录路径。

    protected function searchTables(): array
扫描 Model 类并通过 `table()` 收集表名。

    protected function searchModelClasses(string $path): array
递归扫描目录中的 `.php` 文件路径作为候选 Model 类。

## 相关链接

- [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) — 驱动相关的取表/结构实现
- [DuckPhp\Component\DbManager](Component-DbManager.md) — 数据库来源
