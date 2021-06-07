# DuckPhp\Ext\SqlDumper
[toc]

## 简介

数据库迁移类，只支持 MySQL，但你可以做自己的其他语言实现。
目前比较简陋，后续会加更多功能。

## 选项

        'path' =>'',
路径
        'path_sql_dumper_data' => 'config',
导出数据的路径

        'sql_dumper_data_file' => 'sql_struct',
导出的文件名

        'sql_dumper_prefix' => '',
表名前缀
        'sql_dumper_ignore_tables' => [],
忽略表


## 方法

### 公开方法


    public function run()
运行导出

    public function install($force = false)
安装

### 内部方法

    protected function initContext(object $context)
初始流程方法

    protected function load()
载入数据文件

    protected function getData()
获取数据

    protected function getTables()
获取表名

    protected function getCreate($table)
获得数据库表信息

    protected function save($data)
保存数据到文件

## 详解

简单的便于数据库迁移。

```php
SqlDumper::G()->run(); // 导出到配置文件，默认是 config/sql_struct.php
SqlDumper::G()->install(); // 从配置文件安装 sql

```

