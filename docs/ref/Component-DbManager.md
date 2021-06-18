# DuckPhp\Component\DBManager
[toc]

## 简介
[DuckPhp\Db\Db](Db-Db.md) 的管理组件

## 选项
全部选项

        'database' => null,
数据库，单一数据库配置

        'database_list' => null,
数据库，多数据库配置

        'database_list_reload_by_setting' => true,
数据库，从设置里再入数据库配置

        'database_list_try_single' => true,
数据库，尝试使用单一数据库配置

        'database_log_sql_level' => 'debug',
数据库，记录sql 错误等级

        'database_log_sql_query' => false,
数据库，记录sql 查询

        'database_auto_extend_method' => true,
数据库，是否扩充方法至助手类
扩充 setBeforeGetDbHandler 入助手类。

        'database_class' => '',
数据库，默认为 Db::class。
如果你扩展了 DB 类，可以调用这个。更高级的可以调整 getDb 方法


## 方法
### 公开方法

    public static function Db($tag = null)
    public function _Db($tag = null)
获得数据库

    public static function DbForWrite()
    public function _DbForWrite()
获取写入的数据库

    public static function DbForRead()
    public function _DbForRead()
获取读入数据库

    public static function CloseAll()
    public function _CloseAll()
关闭数据库连接

    public static function OnQuery($db, $sql, ...$args)
    public function _OnQuery($db, $sql, ...$args)
查询事件

    public function setBeforeGetDbHandler($db_before_get_object_handler)
获取得到数据库之前的Handle

### 内部方法

    protected function initOptions(array $options)
    protected function initContext(object $context)
    protected function getDatabase($tag)
    protected function getDb($db_config)

## 详解

DbManager 类是用来使用数据库的。M::Db() 用到了这个组件。

`database` 选项是个 array();
```
[
    'dsn'=>"???",
    'username'=>'???',
    'password'=>'???'
]
```

`database_list` 是项目为 `database` 数组。

`database_list_try_single` 选项 用于如果 `database_list` 选项没设置的时候找`database`

`database_list_reload_by_setting` 用于在 Setting() 里找设置。

Db($tag); $tag 对应的是  `database_list[$tag]` 定的数据库


DbForWrite 对应 `database_list[0]`  DbForRead 对应 `database_list[1]`

`database_log_sql_query`, `database_log_sql_level` 用于记录 sql.
