# DB\DBInterface

## 简介
DB接口类
## 选项

## 公开方法


## 详解

    public function close();
    public function getPDO();
    public function quote($string);
    public function fetchAll($sql, ...$args);
    public function fetch($sql, ...$args);
    public function fetchColumn($sql, ...$args);
    public function execute($sql, ...$args);