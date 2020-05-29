# Helper\ModelHelper

## 简介
Model 助手类
## 选项

## 助手类公用方法
public static function IsDebug()
public static function Platform()
public static function trace_dump()
public static function var_dump(...$args)

## 由 DBManager 扩展的方法

- DB [DuckPhp\Ext\DBManager::DB](Ext-DBManager.md#DB)
- DB_R [DuckPhp\Ext\DBManager::DB_R](Ext-DBManager.md#DB_R)
+ DB_W [DuckPhp\Ext\DBManager::DB_W](Ext-DBManager.md#DB_W)

## 详解

SqlForPager($sql, $pageNo, $pageSize = 10)

    分页 limte 的 sql 
SqlForCountSimply($sql)
    
    简单的把 select ... from 替换成select count(*)as c from 
    