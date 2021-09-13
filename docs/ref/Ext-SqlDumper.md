# DuckPhp\Ext\SqlDumper
[toc]

## 简介

数据库迁移类，只支持 MySQL，但你可以做自己的其他语言实现。
目前比较简陋，后续会加更多功能。

一般不直接使用，而是通过 Installer 来使用。
## 选项

        'path' => '',
基础路径
        'path_sql_dump' => 'config',
SqlDump, 导出数据的路径

        'sql_dump_file' => 'sql',
SqlDump, 导出的数据文件名

        'sql_dump_prefix' => '',
SqlDump, 表名前缀

        'sql_dump_data_tables' => [],
SqlDump, 只用到的表

        'sql_dump_exclude_tables' => [],
SqlDump, 忽略表

        'sql_dump_include_tables' => [],
SqlDump, 包括的表

        'sql_dump_install_replace_prefix' => false,
SqlDump,  安装的时候是否要替换 sql_dump_prefix

        'sql_dump_install_new_prefix' => '',
SqlDump,  安装时候的新表前缀

        'sql_dump_install_drop_old_table' => false,
SqlDump， 安装时删除旧表

        'sql_dump_include_tables_all' => true,
SqlDump， 包含所有表

        'sql_dump_include_tables_by_model' => false,
SqlDump， 高级选项， 搜索 Model 类 table() 下的所有表

## 方法

### 公开方法


    public function run()
导出 返回的是错误语句字符串

    public function install()
安装

### 内部方法

    protected function initContext(object $context)
初始流程方法

    protected function load()
载入数据文件

    protected function getData()
获取sql 数据

    protected function getDataSql($table)
获取数据

    protected function getInsertTableSql()
获得所有插入 sql 语句

    protected function getSchemes()
获得数据库表信息

    protected function getSchemeByTable($table)
获得单个数据库表信息

    protected function save($data)
保存数据到文件

    protected function installScheme($sql, $table)
安装单个数据表


    protected function searchTables()

    protected function searchModelClasses($path)

## 详解

简单的便于数据库迁移。

```php
SqlDumper::G()->run(); // 导出到配置文件，默认是 config/sql.php
SqlDumper::G()->install(); // 从配置文件安装 sql


SqlDumper 一般不直接用，而是让 [DuckPhp\Ext\Installer](Ext-Installer.md) 使用