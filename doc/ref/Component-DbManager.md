# DuckPhp\Ext\DBManager
[toc]

## 简介
`组件类` [DuckPhp\Db\Db](Db-Db.md) 的管理类。默认使用。
## 选项

'database' => null,

    单一数据库配置
'database_list' => null,

    数据库列表
'database_list_reload_by_setting' => true,

    是否从设置里读取数据库列表
'database_list_try_single' => true,

    尝试使用单一数据库配置
'database_log_sql_level' => 'debug',

    记录sql 错误等级
'database_log_sql_query' => false,

    记录sql 查询

'database_auto_extend_method' => true,

    是否扩充 setBeforeGetDbHandler 入助手类。
## 公开方法

    public static function Db($tag = null)
    public static function DbForWrite()
    public static function DbForRead()
    public static function CloseAll()
    public static function OnQuery($db, $sql, ...$args)
    
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

`database_list` 是`database` 数组。

`database_list_try_single` 选项 用于如果 `database_list` 选项没设置的时候找`database`

`database_list_reload_by_setting` 用于在 Setting() 里找设置。

Db($tag); $tag 对应的是  `database_list[$tag]` 定的数据库


DbForWrite 对应 `database_list[0]`  DbForRead 对应 `database_list[1]`

`database_log_sql_query`, `database_log_sql_level` 用于记录 sql.
