# DuckPhp\Db\Db
[toc]

## 简介

`伪组件` Db 类是DuckPhp 自带的数据库类。 是 App::Db() 和 M::Db() 的实现。

## 选项

## 公开方法

### sql 方法
    public function fetchAll($sql, ...$args)
运行SQL并获得所有行

    public function fetch($sql, ...$args)
运行SQL并获得单一行

    public function fetchColumn($sql, ...$args)
运行SQL并获得单个数据

    public function execute($sql, ...$args)
运行SQL 返回 true false

    protected function exec($sql, ...$args)
内部执行 sql

    public function fetchObjectAll($sql, ...$args)
运行SQL并获得所有行(对象数组)

    public function fetchObject($sql, ...$args)
运行SQL并获得单一行(对象形式)

    public function setObjectResultClass($resultClass)
设置返回的类，配合 fetchObject fetchObjectAll 使用。

    public function doTableNameMacro($sql)
默认把执行查询 sql 里的 `{TABLE}` 展开成 table($table_name) 里的设置

    public function table($table_name)
设置 `'TABLE'` 要替换的表名，并返回 Db 类。拼接 sql 的时候要注意第三方数据可能会有 `'TABLE'` 其实是对 qoute('TABLE');

### 其他方法

    public function init($options = [], $context = null)
初始化

    protected function check_connect()
用于 override ，连接的设置。

    public function close()
关闭数据库

    public function PDO($pdo = null)
获得/设置 相关 PDO 对象。

    public function setBeforeQueryHandler($handler)
在 query 前执行。($handler)($this,$sql, ...$args)

    public function quote($string)
编码

    public function buildQueryString($sql, ...$args)
合并带参数的sql.

    public function rowCount()
获得行数

    public function lastInsertId()
获得插入的ID.


### DbAdvanceTrait 的方法

    public function quoteIn($array)
    public function quoteSetArray($array)
    public function qouteInsertArray($array)
    public function findData($table_name, $id, $key = 'id')
    public function insertData($table_name, $data, $return_last_id = true)
    public function deleteData($table_name, $id, $key = 'id', $key_delete = 'is_deleted')
    public function updateData($table_name, $id, $data, $key = 'id')

## 详解

Db()
    
#### Db 类的用法
Db
    close(); //关闭, 你一般不用关闭,系统会自动关闭
    PDO($new=null); //获取/设置 PDO 对象
    quote($string);
    fetchAll($sql, ...$args);
    fetch($sql, ...$args);
    fetchColumn($sql, ...$args);
#### 示例
使用数据库，在 设置里正确设置 database_list 这个数组，包含多个数据库配置
然后在用到的地方调用 DuckPhp::Db($tag=null) 得到的就是 Db 对象，用来做各种数据库操作。
$tag 对应 $setting['database_list'][$tag]。默认会得到最前面的 tag 的配置。

你不必担心每次框架初始化会连接数据库。只有第一次调用 DuckPhp::Db() 的时候，才进行数据库类的创建。


## 示例如下

```php
<?php
use DuckPhp\DuckPhp;

require_once('../vendor/autoload.php');

$options=[];
$options['override_class']='';      // 示例文件不要被子类干扰。
$options['skip_setting_file']=true; // 不需要配置文件。
$options['error_exception']=true; // 使用默认的错误视图

$options['database_list']=[[
    'dsn'=>'mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;',
    'username'=>'root',
    'password'=>'123456',
]]; // 这里用选项里的
DuckPhp::RunQuickly($options,function(){    
    $sql="select 1+? as t";
    $data=M::Db()->fetch($sql,2);
    DuckPhp::var_dump($data);
    DuckPhp::exit(0);
});
```

## 方法索引


完毕。






