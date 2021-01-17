# DuckPhp\Helper\ModelHelper

## 简介
Model 助手类
## 详解

    
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

## 助手类公用方法

来自 HelperTrait，

- IsDebug()

    判断是否在调试状态，App 的  `is_debug` 选项 ,`duckphp_is_debug` 设置项。
    
- IsRealDebug()
    这个用于调试标识开，但是实际还是调试状态。用于特定用处。
    
- Platform()
    获得平台标志，App 的  `platform` 选项 ,`duckphp_platform` 设置项。
    
- Logger($object=null)
    返回Logger类。
    $object 是替换入的新的 Logger 类。
    
- trace_dump()
    显示调用堆栈
    
- var_dump(...$args)
    替代 var_dump ，在非调试状态下不显示。
    
- ThrowOn($flag, $message, $code = 0, $exception_class = null) 详见 [Core/ThrowOn](Core-ThrowOn.md)

    如果 $flag成立则抛出异常，如果未指定 $exception_class，抛则判断当前类是否是 Exception 类的子类，如果不是，则默认为 Exception 类。    
- AssignExtendStaticMethod($key, $value = null)   详见 [Core/ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
    分配固定方法。

- GetExtendStaticMethodList() 详见 [Core/ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
    获得
- \_\_callStatic($name, $arguments) 详见 [Core/ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
    静态方法已经被接管。