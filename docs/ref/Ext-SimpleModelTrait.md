# DuckPhp\Ext\SimpleModelTrait

## 简介

简单的模型类 Trait, 封装常见操作。

## 选项

无选项

## 方法
### 公开方法

    public function getList(int $page = 1, int $page_size = 10)

返回数据 ['data','total'=>]; 这样的数据结构

    public function get($id)
根据 id 获得数据

    public function find($a)
根据条件查找特定数据

    public function add($data)
添加数据，返回 LastInsertId

    public function update($id, $data)
更新

    public function delete($id)
删除没被实现

### 内部方法
    protected function prepare($sql)
把  'table' 转成 表名

    protected function getTablePrefix()
获取表名前缀, table() 调用到。

    protected function table()
表名，

    private function getTableByClass($class)
getTablePrefix 的实现

## 详解

简单模型类。适用于不想手写 sql 的情况
你扩展这个  Trait 适应你的场景。
