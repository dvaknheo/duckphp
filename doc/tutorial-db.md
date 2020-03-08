# 数据库教程
## 相关类和配置。
[`Ext/DBManager`]()

DB/DB

DB/DBAdvance

DB/DBInterface




## 开始

### DBManager
默认开启。DBManager 类是用来使用数据库的
M::DB() 用到了这个组件。

#### 选项
    'db_create_handler'=>null,  // 默认用 [DB::class,'CreateDBInstance']
    'db_close_handler'=>null,   // 默认等于 [DB::class,'CloseDBInstance']
    'before_get_db_handler'=>null, // 在调用 DB 前调用
    'use_context_db_setting'=>true, //使用 setting 里的。
    'database_list'=>null,      //DB 列表
    db_create_handler
#### 说明

database_list 的示例：
```php
    [[
		'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
		'username'=>'???',
		'password'=>'???',
    ]],
```
#### 方法
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
$tag 对应 $setting\['database_list'\][$tag]。默认会得到最前面的 tag 的配置。

你不必担心每次框架初始化会连接数据库。只有第一次调用 DuckPHP::DB() 的时候，才进行数据库类的创建。

DB 的使用方法，看后面的参考。
示例如下

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

## 使用 think-orm 的 DB

```php
<?php
use think\facade\Db;
use DuckPHP\Ext\DBManager;
use DuckPHP\App;
require_once('../vendor/autoload.php');

$options=[];
$options['override_class']='';      // 示例文件不要被子类干扰。
$options['skip_setting_file']=true;// 不需要配置文件。
$options['error_exception']=null; // 使用默认的错误视图
DuckPHP::RunQuickly($options,function(){
    Db::setConfig([
        'default'     => 'mysql',
        'connections' => [
            'mysql'     => [
                'type'     => 'mysql',
                'hostname' => '127.0.0.1',
                'username' => 'root',
                'password' => '123456',
                'database' => 'DnSample',
            ]
        ]
    ]);
    //就这句话了
    DBManager::G()->setDBHandler(function(){return Db::class;});
    $sql="select * from Users where true limit 1";
    $data=DuckPHP::DB()::query($sql);
    var_dump($data);
});

```