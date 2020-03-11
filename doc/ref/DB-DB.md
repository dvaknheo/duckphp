# DB\DB

## 简介

## 选项

## 公开方法


## 详解

DB()
    是 App::DB 和 M::DB 的实现。
#### DB 类的用法
DB
    close(); //关闭, 你一般不用关闭,系统会自动关闭
    getPDO(); //获取 PDO 对象
    quote($string);
    fetchAll($sql, ...$args);
    fetch($sql, ...$args);
    fetchColumn($sql, ...$args);
    execute($sql, ...$args); //   执行某条sql ，不用 exec , execute 是为了兼容其他类。
#### 示例
使用数据库，在 设置里正确设置 database_list 这个数组，包含多个数据库配置
然后在用到的地方调用 DuckPHP::DB($tag=null) 得到的就是 DB 对象，用来做各种数据库操作。
$tag 对应 $setting['database_list'][$tag]。默认会得到最前面的 tag 的配置。

你不必担心每次框架初始化会连接数据库。只有第一次调用 DuckPHP::DB() 的时候，才进行数据库类的创建。

DB 的使用方法，看后面的参考。

## 示例如下

```php
<?php
use DuckPHP\App as DuckPHP;
use DuckPHP\Helper\ModelHelper as M;

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
DuckPHP::RunQuickly($options,function(){    
    $sql="select 1+? as t";
    $data=M::DB()->fetch($sql,2);
    var_dump($data);
    DuckPHP::exit_system(0);
});
```