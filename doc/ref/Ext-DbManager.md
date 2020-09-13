# DuckPhp\Ext\DBManager
[toc]

## 简介
`组件类` Db 的 管理类 已经重写。
## 选项

'database_list' => null,
'db_before_get_object_handler' => null,
'db_database_list_from_setting' => true,

'log_sql_query' => false,
'log_sql_level' => 'debug',

## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)

## 其他
### DBManager

默认开启。DBManager 类是用来使用数据库的
M::DB() 用到了这个组件。
#### 选项

    'before_get_db_handler'=>null, // 在调用 DB 前调用
    'use_context_db_setting'=>true, //使用 setting 里的。
    'database_list'=>null,      //DB 列表
    db_create_handler
#### 说明


#### 使用 think-orm 的 DB

```php
<?php
use think\facade\Db;
use DuckPhp\Ext\DBManager;
use DuckPhp\DuckPhp;
require_once('../vendor/autoload.php');

$options=[];
$options['override_class']=App::class;
$options['skip_setting_file']=true;// 不需要配置文件。

class App extends DuckPhp
{
    public function _Db($tag)
    {
        return Db::class;
    }
}

DuckPhp::RunQuickly($options,function(){
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
});

```
