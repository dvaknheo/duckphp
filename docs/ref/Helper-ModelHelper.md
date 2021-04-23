# DuckPhp\Helper\ModelHelper

## 简介
Model 助手类
## 详解
Model Helper 只有 5个方法


Db [DuckPhp\Ext\DBManager::Db](Ext-DBManager.md#Db)

    // 
DbForRead [DuckPhp\Ext\DBManager::DbForRead](Ext-DBManager.md#DbForRead)

    //

DbForWrite [DuckPhp\Ext\DBManager::DbForWrite](Ext-DBManager.md#DbForWrite)

    //
SqlForPager($sql, $pageNo, $pageSize = 10)

    分页 limit 的 sql 
SqlForCountSimply($sql)
    
    简单的把 select ... from 替换成select count(*)as c from 
