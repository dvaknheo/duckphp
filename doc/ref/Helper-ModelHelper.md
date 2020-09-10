# DuckPhp\Helper\ModelHelper

## 简介
Model 助手类
## 选项

## 助手类公用方法
public static function IsDebug()
public static function Platform()
public static function trace_dump()
public static function var_dump(...$args)



## 详解

    
Db [DuckPhp\Ext\DBManager::Db](Ext-DBManager.md#Db)

    //
DbForRead [DuckPhp\Ext\DBManager::DbForRead](Ext-DBManager.md#DbForRead)

    //

DbForWrite [DuckPhp\Ext\DBManager::DbForWrite](Ext-DBManager.md#DbForWrite)

    //
SqlForPager($sql, $pageNo, $pageSize = 10)

    分页 limte 的 sql 
SqlForCountSimply($sql)
    
    简单的把 select ... from 替换成select count(*)as c from 
