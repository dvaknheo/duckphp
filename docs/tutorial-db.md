# 数据库教程
[toc]
## 相关类和配置。

- [DuckPhp\Component\DbManager](ref/Component-DbManager.md) 管理数据库
- [DuckPhp\Db\Db](ref/Db-Db.md) 数据库类
    - 使用 [DuckPhp\Db\DbAdvanceTrait](ref/Db-DbAdvanceTrait.md)
    - 实现 [DuckPhp\Db\DbInterface](ref/Db-DbInterface.md)

## 相关选项


'database_list'=>[],      //DB 列表

## 相关设置
setting.php 以下配置，注意的是 DuckPhp 默认是支持多个数据库的，所以是database_list
```php
[
'database_list' =>[[
        'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
        'username'=>'???',
        'password'=>'???',
    ]],
],
```
## 开始

### DBManager
默认开启。DBManager 类是用来使用数据库的
M::DB() 用到了这个组件。

#### 方法
DB()
    是 App::DB 和 M::DB 的实现。

#### DB 类的用法
DB
    close(); //关闭, 你一般不用关闭,系统会自动关闭
    PDO();
    quote($string);
    fetchAll($sql, ...$args);
    fetch($sql, ...$args);
    fetchColumn($sql, ...$args);
    execute($sql, ...$args); //   执行某条sql

例子 full/dbtest.php 演示了这些增删改查怎么用。

```php

```
#### 示例
使用数据库，在 设置里正确设置 database_list 这个数组，包含多个数据库配置
然后在用到的地方调用 App::DB($tag=null) 得到的就是 DB 对象，用来做各种数据库操作。
$tag 对应 $setting\['database_list'\][$tag]。默认会得到最前面的 tag 的配置。

你不必担心每次框架初始化会连接数据库。只有第一次调用 DuckPhp::DB() 的时候，才进行数据库类的创建。

DB 的使用方法，看后面的参考。
示例如下

```php
<?php
use DuckPhp\DuckPhp;
use DuckPhp\Helper\ModelHelper as M;

require_once('../vendor/autoload.php');

$options=[];
$options['override_class']='';      // 示例文件不要被子类干扰。
$options['skip_setting_file']=true; // 不需要配置文件。

$options['database_list']=[[
    'dsn'=>'mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;',
    'username'=>'root',
    'password'=>'123456',
]]; // 这里用选项里的
DuckPhp::RunQuickly($options,function(){    
    $sql="select 1+? as t";
    $data=M::DB()->fetch($sql,2);
    var_dump($data);
    DuckPhp::exit_system(0);
});
```


### 数据库

DuckPhp 提供了一个默认的数据库类。如果在项目中有不满意，则可以替换之。

建议只在  Model 层用 数据库。

示例如下

```php
<?php
// app/Model/MiscModel.php
namespace MY\Model;

use MY\Base\BaseModel;
use MY\Base\Helper\ModelHelper as M;

class DBModel extends BaseModel
{
    public function first()
    {
        $sql="select 1+? as t";
        $data=M::DB()->fetch($sql,2);
        var_dump($data);
    }
}
```

要使用数据库,需要在 config/setting.php 里做相关配置添加.

```php
'database_list' =>
    [[
		'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
		'username'=>'???',
		'password'=>'???',
    ]],
```

注意到配置是  database_list ,是支持多个数据库的。
1.2.6 版本变更，你可以直接使用 database 配置。


`M::DB($tag)` 的 $tag 对应 $setting\['database_list'\][$tag]。默认会得到最前面的 tag 的配置。

DbForWrite() 则的对应第0 号数库 ,DbForRead() 对应第 1号数据库。

你不必担心每次框架初始化会连接数据库。只有第一次调用 DuckPhp::DB() 的时候，才进行数据库类的创建。

## 使用 think-orm 的 DB

```php
<?php
use think\facade\Db;
use DuckPhp\Ext\DBManager;
use DuckPhp\DuckPhp;
require_once('../vendor/autoload.php');

$options=[];
$options['override_class']=App::class;

class App extends DuckPhp
{
    public function _Db($tag)
    {
        return Db::class;
    }
}

App::RunQuickly($options,function(){
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
