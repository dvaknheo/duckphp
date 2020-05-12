# Ext\DBManager

## 简介

## 选项
'db_create_handler' => null,
'db_close_handler' => null,
'db_exception_handler' => null,
'before_get_db_handler' => null,

'database_list' => null,
'use_context_db_setting' => true,
'db_close_at_output' => true,
## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    protected function initContext($options = [], $context = null)
    public static function CloseAllDB()
    public static function DB($tag = null)
    public static function DB_W()
    public static function DB_R()
    public function setDBHandler($db_create_handler, $db_close_handler = null, $db_exception_handler = null)
    public function setBeforeGetDBHandler($before_get_db_handler)
    public function getDBHandler()
    public function _DB($tag = null)
    protected function getDatabase($db_config, $tag)
    public function _DB_W()
    public function _DB_R()
    public function _closeAllDB()
    

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


#### 使用 think-orm 的 DB

```php
<?php
use think\facade\Db;
use DuckPhp\Ext\DBManager;
use DuckPhp\App;
require_once('../vendor/autoload.php');

$options=[];
$options['override_class']='';      // 示例文件不要被子类干扰。
$options['skip_setting_file']=true;// 不需要配置文件。
$options['error_exception']=null; // 使用默认的错误视图
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
    //就这句话了
    DBManager::G()->setDBHandler(function(){return Db::class;});
    $sql="select * from Users where true limit 1";
    $data=DuckPhp::DB()::query($sql);
    var_dump($data);
});

```