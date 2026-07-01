# DuckPhp\Db\DbInterface
[toc]

## 简介
Db接口类

## 方法

    public function close();
    public function PDO($object = null);
    public function quote($string);
    public function fetchAll($sql, ...$args);
    public function fetch($sql, ...$args);
    public function fetchColumn($sql, ...$args);
    public function execute($sql, ...$args);
    public function rowCount();
    public function lastInsertId();
最后插入的ID
