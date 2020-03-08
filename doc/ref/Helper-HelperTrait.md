# Helper\HelperTrait

## 简介
所有助手类都有的trait

IsDebug()
IsRealDebug()
Platform()
Logger()
trace_dump()
var_dump(...$args)
    
    替代 var_dump ，在
ThrowOn($flag, $message, $code = 0, $exception_class = null)

    如果 $flag成立，如果未指定 $exception_class，则判断当前类是否是 Exception 类，如果不是，则默认为 Exception 类。

AssignExtendStaticMethod($key, $value = null)
GetExtendStaticMethodList()
__callStatic($name, $arguments)

## 详解

HelperTrait

`ThrowOn()` 是来自 `Core/ThrowOn` 用于跑异常

`__callStatic`,`GetExtendStaticMethodList`,`AssignExtendStaticMethod`, 是 来自 `Core/ExtendableStaticCallTrait`
