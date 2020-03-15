# Helper\ViewHelper

## 简介

视图助手类

助手类，全静态方法
## 方法

public static function H($str)

HTML 编码
public static function L($str, $args = [])
多语言编码
public static function HL($str, $args = [])
多语言编码
public static function ShowBlock($view, $data = null)
显示块
public static function URL($url)
获得 URL
public static function Domain()
获得当前域名

## 助手类公用方法
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
## 详解


为什么 ShowBlock 的 view 在前面， 而Show() 的 View 在后面？ 因为可省略啊。
为什么没有 Show() ? 避免在 View里调用啊。


    public static function H($str)
    public static function L($str, $args = [])
    public static function HL($str, $args = [])
    public static function ShowBlock($view, $data = null)
    public static function URL($url)
    public static function Domain()
