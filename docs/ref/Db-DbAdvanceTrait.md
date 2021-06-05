# DuckPhp\Db\DbAdvanceTrait
[toc]

## 简介
DbAdvanceTrait 这个 trait 给 Db 类提供了常用的 高级的 Db 方法
## 选项

## 方法
### 编码方法

    public function quoteIn($array)
    public function quoteSetArray($array)
    public function qouteInsertArray($array)
    public function quoteAndArray($array)
###  其他方法
    public function findData($table_name, $id, $key = 'id')
    public function insertData($table_name, $data, $return_last_id = true)
    public function deleteData($table_name, $id, $key = 'id', $key_delete = 'is_deleted')
    public function updateData($table_name, $id, $data, $key = 'id')
## 详解


