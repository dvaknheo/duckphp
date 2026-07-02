# DuckPhp\Db\DbInterface

数据库接口。

## 简介

`DbInterface` 定义了 DuckPHP 数据库对象需要实现的最小方法集合。`DuckPhp\Db\Db` 类实现了该接口，并可通过 `database_class` 选项替换为自定义数据库类。

## 接口定义

```php
namespace DuckPhp\Db;

interface DbInterface
{
    public function close();
    public function PDO($object = null);
    public function quote($string);
    public function fetchAll($sql, ...$args);
    public function fetch($sql, ...$args);
    public function fetchColumn($sql, ...$args);
    public function execute($sql, ...$args);
    public function rowCount();
    public function lastInsertId();
}
```

## 方法说明

    public function close()
关闭数据库连接

    public function PDO($object = null)
获取或设置 PDO 实例

    public function quote($string)
转义字符串或字符串数组

    public function fetchAll($sql, ...$args)
执行查询并返回所有结果

    public function fetch($sql, ...$args)
执行查询并返回单行结果

    public function fetchColumn($sql, ...$args)
执行查询并返回单列值

    public function execute($sql, ...$args)
执行写操作（INSERT/UPDATE/DELETE）

    public function rowCount()
返回最近一次写操作影响的行数

    public function lastInsertId()
返回最后一次插入的自增 ID

## 相关链接

- [DuckPhp\Db\Db](Db-Db.md)
- [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
