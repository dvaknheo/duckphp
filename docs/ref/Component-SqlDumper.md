# DuckPhp\Ext\SqlDumper
[toc]

## 简介

数据库迁移类，目前只支持 MySQL，但你可以做自己的其他语言实现。

## 选项

        'path' => '',
基础路径
        'path_sql_dump' => 'config',
SqlDump, 导出数据的路径

        'sql_dump_file' => 'sql.php',
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

    public function run()
Dump 数据库

    public function install()
安装数据

    protected function installScheme($sql, $table)

    protected function installData($sql, $table)

    protected function getData()

    protected function getSchemes()

    protected function getSchemeByTable($table)

    protected function getInsertTableSql()

    protected function getDataSql($table)

    protected function load()
载入数据文件

    protected function save($data)
保存数据到文件

    protected function searchTables()

    protected function searchModelClasses($path)

## 详解

简单的便于数据库迁移。

```php
SqlDumper::G()->run(); // 导出到配置文件，默认是 config/sql.php
SqlDumper::G()->install(); // 从配置文件安装 sql

## 完毕
