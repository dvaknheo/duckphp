# DuckPhp\FastInstaller\SqlDumper

SQL 导出与导入组件。

## 简介

`SqlDumper` 负责根据当前数据库驱动导出表结构（与可选数据）到 SQL 文件，并在安装时将 SQL 文件导入数据库。它依赖 `Supporter` 获取表结构与数据 SQL，支持通过模型自动发现表。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 基础路径，用于拼接 SQL 文件路径 |
| `path_sql_dump` | `'config'` | SQL 文件存放目录，相对于 `path` |
| `sql_dump_file` | `'install.sql'` | 默认 SQL 文件名（当前主要按驱动名生成文件名） |
| `sql_dump_include_tables` | `[]` | 额外包含的表名列表，支持 `@` 作为 `table_prefix` 占位符 |
| `sql_dump_exclude_tables` | `[]` | 需要排除的表名列表 |
| `sql_dump_data_tables` | `[]` | 需要导出数据的表名列表 |
| `sql_dump_include_tables_all` | `false` | 为 `true` 时导出数据库中所有表 |
| `sql_dump_include_tables_by_model` | `true` | 为 `true` 时自动从 `Model` 目录发现表 |
| `sql_dump_install_replace_prefix` | `true` | 安装 SQL 时是否替换表前缀 |
| `sql_dump_prefix` | `''` | 导出 SQL 时使用的表前缀 |
| `sql_dump_debug_show_sql` | `false` | 安装 SQL 时是否在控制台打印 SQL |

## 使用方式

### 导出 SQL

```php
use DuckPhp\FastInstaller\SqlDumper;

SqlDumper::_()->dump();
```

导出文件位于 `config/{driver}.sql`，例如 `config/mysql.sql`。

### 导入 SQL

```php
use DuckPhp\FastInstaller\SqlDumper;

SqlDumper::_()->install(true);  // 强制导入，会先执行 DROP TABLE IF EXISTS
SqlDumper::_()->install();      // 普通导入
```

## 配置示例

### 导出所有表

```php
class App extends DuckPhp
{
    public $options = [
        'sql_dump_include_tables_all' => true,
        'sql_dump_data_tables' => ['@users'],  // @ 会被替换为 table_prefix
    ];
}
```

### 仅导出模型中的表

```php
class App extends DuckPhp
{
    public $options = [
        'sql_dump_include_tables_by_model' => true,
        'sql_dump_include_tables' => [],
        'sql_dump_exclude_tables' => ['@migrations'],
    ];
}
```

## 注意事项

1. 导出时表前缀会被替换为空，便于安装时按当前 `table_prefix` 重新替换。
2. 强制安装时会在 `CREATE TABLE` 前自动插入 `DROP TABLE IF EXISTS`。
3. 如果数据库驱动为空，`dump()` 会直接返回 `false`。
4. 导出数据表时仅导出 `sql_dump_data_tables` 中指定的表。

## 全部选项

```php
public $options = [
    'path' => '',
    'path_sql_dump' => 'config',
    'sql_dump_file' => 'install.sql',

    'sql_dump_include_tables' => [],
    'sql_dump_exclude_tables' => [],
    'sql_dump_data_tables' => [],

    'sql_dump_include_tables_all' => false,
    'sql_dump_include_tables_by_model' => true,

    'sql_dump_install_replace_prefix' => true,
    'sql_dump_prefix' => '',
    'sql_dump_debug_show_sql' => false,
];
```

## 方法列表

### 公共方法

    public function dump()
导出当前数据库的表结构（与数据）到 `config/{driver}.sql`

    public function install($force = false)
从 SQL 文件读取并执行导入，`$force` 为 `true` 时会先执行 `DROP TABLE IF EXISTS`

### 受保护方法

    protected function getSchemes()
获取所有需要导出的表结构 SQL，支持通过模型发现或导出所有表

    protected function getInsertTableSql()
获取 `sql_dump_data_tables` 中指定表的数据插入 SQL

    protected function getDataSql($table)
获取指定表的数据插入 SQL

    protected function getModelPath()
获取当前应用 `Model` 目录的绝对路径

    protected function searchTables()
扫描 `Model` 目录，获取所有模型对应的表名

    protected function searchModelClasses($path)
递归扫描指定目录下的 PHP 文件

## 相关链接

- [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md)
- [DuckPhp\FastInstaller\Supporter](FastInstaller-Supporter.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
