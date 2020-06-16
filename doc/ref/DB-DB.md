# DB\DB

## 简介

伪组件 DB 类是DuckPhp 自带的数据库类。 是 App::DB() 和 M::DB() 的实现。

## 选项

## 公开方法

public function init($options = [], $context = null)

    虽然是组件类，只被 DB::CreateDBInstance 使用
public static function CreateDBInstance($db_config)

    用于创建实例
public static function CloseDBInstance($db, $tag = null)

    用于关闭实例
public function fetchAll($sql, ...$args)

    运行SQL并获得所有行
public function fetch($sql, ...$args)

    运行SQL并获得单一行
public function fetchColumn($sql, ...$args)

    运行SQL并获得单一行
public function execute($sql, ...$args)

    运行SQL并获得单一行
protected function check_connect()

    用于 override ，连接的设置。
public function close()

public function getPDO()

    获得 相关 PDO 对象。
public function setBeforeQueryHandler($handler)

    在 query 前执行。
public function quote($string)

    编码
public function buildQueryString($sql, ...$args)

    合并带参数的sql.
public function rowCount()

    获得行数
public function lastInsertId()
    
    获得插入的ID.

### DBAdvance 的方法

public function quoteIn($array)
public function quoteSetArray($array)
public function qouteInsertArray($array)
public function findData($table_name, $id, $key = 'id')
public function insertData($table_name, $data, $return_last_id = true)
public function deleteData($table_name, $id, $key = 'id', $key_delete = 'is_deleted')
public function updateData($table_name, $id, $data, $key = 'id')

## 详解

DB()
    
#### DB 类的用法
DB
    close(); //关闭, 你一般不用关闭,系统会自动关闭
    PDO($new=null); //获取/设置 PDO 对象
    quote($string);
    fetchAll($sql, ...$args);
    fetch($sql, ...$args);
    fetchColumn($sql, ...$args);
    execute($sql, ...$args); //   执行某条sql ，不用 exec , execute 是为了兼容其他类。
#### 示例
使用数据库，在 设置里正确设置 database_list 这个数组，包含多个数据库配置
然后在用到的地方调用 DuckPhpDuckPhp::DB($tag=null) 得到的就是 DB 对象，用来做各种数据库操作。
$tag 对应 $setting['database_list'][$tag]。默认会得到最前面的 tag 的配置。

你不必担心每次框架初始化会连接数据库。只有第一次调用 DuckPhp::DB() 的时候，才进行数据库类的创建。


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
    $data=M::DB()->fetch($sql,2);
    DuckPhp::var_dump($data);
    DuckPhp::exit(0);
});
```

## 方法索引

public function init($options = [], $context = null)
public static function CreateDBInstance($db_config)
public static function CloseDBInstance($db, $tag = null)
protected function check_connect()
public function close()
public function PDO($new)
public function setBeforeQueryHandler($handler)
public function quote($string)
public function buildQueryString($sql, ...$args)
public function fetchAll($sql, ...$args)
public function fetch($sql, ...$args)
public function fetchColumn($sql, ...$args)
public function execute($sql, ...$args)
public function rowCount()
public function lastInsertId()