# DuckPhp\Ext\SimpleModelTrait

## 简介

简单的模型类 Trait, 封装常见操作,帮助你少写代码的

## 选项

无选项

## 方法
### 公开方法

    public function getList(int $page = 1, int $page_size = 10)

返回数据 ['data','total'=>]; 这样的数据结构


    public function find($a)
根据条件查找特定数据,如果 a 为标量，则 按 ID 来

    public function add($data)
添加数据，返回 LastInsertId

    public function update($id, $data)
更新

    public function delete($id)
删除没被实现

### 内部方法
    public function prepare($sql)
把  `'table'` 转成 表名

    protected function getTablePrefix()
获取表名前缀, table() 调用到。

    protected function table()
表名，

    protected function getTableByClass($class)
getTablePrefix 的实现

## 详解
简单模型类。适用于不想手写 sql 的情况

SimpleModelTrait 是帮助你少写代码的，而不是作为 orm 模型用的。复杂 sql 请自己手写

你扩展这个  Trait 适应你的场景。

SimpleModelTrait  find 的返回结果是 数组，而不是当前类。

留有 delete 接口，但只是报异常，因为删除操作要谨慎，各地都不同。
    

