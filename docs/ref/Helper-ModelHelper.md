# DuckPhp\Helper\ModelHelper

## 简介
Model 助手类,全静态方法
## 详解
Model Helper 只有 5个方法


public static function Db($tag = null) 

    获得 Db 对象 [DuckPhp\Ext\DBManager::Db](Ext-DBManager.md#Db)
public static function DbForRead() 

    获得只读用的 Db 对象 public static function DbForRead() 
参见 [DuckPhp\Ext\DBManager::DbForRead](Ext-DBManager.md#DbForRead)
public static function DbForWrite() 

    获得读写用的 Db 对象
参见 [DuckPhp\Ext\DBManager::DbForWrite](Ext-DBManager.md#DbForWrite)
public static function SqlForPager(string $sql, int $pageNo, int $pageSize = 10): string

    分页 limit 的 sql,补齐 sql用
public static function SqlForCountSimply(string $sql): string
    
    简单的把 select ... from 替换成select count(*)as c from 
