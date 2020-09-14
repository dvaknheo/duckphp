# DuckPhp\Ext\DBManager
[toc]

## 简介
`组件类` Db 的 管理类 已经重写。
## 选项

'database' => null,

    //
'database_list' => null,

    //
'database_list_reload_by_setting' => true,

    //
'database_list_try_single' => true,

    //
'database_log_sql_query' => false,

    //
'database_log_sql_level' => 'debug',

    //
## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)

## 其他
### 默认开启。DbManager 类是用来使用数据库的
M::Db() 用到了这个组件。
