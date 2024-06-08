# 异常处理
[toc]
## 相关类
App

[DuckPhp\Core\ExceptionManager](ref/Core-ExceptionManager.md) 异常管理类

## 404 的处理，

        'error_404' => null,
404 视图/闭包
        'skip_404' => false,
跳过错误处理

Event On404

## 异常处理的常规处理




## 相关选项


    'error_500' => null,          //'_sys/error-500',
异常视图/闭包


        'exception_reporter' => null,
        
        'exception_for_project' => null,
以下是高级的选项。

'skip_exception_check'=>false,
    运行阶段，跳过异常检查抛出给上层
'handle_all_dev_error' => true,

接管调试错误

'handle_all_exception' => true,
    接管所有异常管理

## 基本的异常处理

默认异常， 会调用 error_500 选项里设置的 view ，如果值是闭包则回调闭包而不是显示。


### 控制器处理异常

SimpleExceptionTrait.php -> ThrowOnTrait
ThrowOn 静态方法
ExceptionReporterTrait
用于接管错误处理


Helper::assignExceptionHandler($classes, $callback = null);
Helper::assignExceptionHandler([$class=>$callback]);

    为特定 异常类设置回调， 发生异常的时候 $callback($exception);这个方法是 assign 方法，两种调用形式。

Helper::setMultiExceptionHandler(array $classes, $callback)

    为了方便多个异常统一调用， 添加了这种错误回调的形势，  $classes 为异常类名列表
Helper::setDefaultExceptionHandler($callback)

    没在异常类列表里的默认调用 $callback($exception);

### 异常处理的一般守则

Model 不抛出异常。

Business 抛出自己错误的时候，来个同名 exception 类。如 SessionBusiness => SessionException 。由调用者自行解决异常问题。

建议这些 Exception 类继承  BaseExeption 类，并实现一个 `display($ex)` 方法，以便于出错的时候显示

### 异常的高级处理

'handle_all_dev_error' => true,
'handle_all_exception' => true, 选项用于接管系统错误。

ExceptionReporterTrait

'skip_exception_check'=>false, 

开启时候 运行阶段，跳过异常检查抛出给上层。如果你不打算自己管理错误的话。 技巧，管理错误的时候，把这个选项 打开 throw $ex ；则由上层管理错误
    
'default_exception_do_log' => true,
'default_exception_self_display' => true,

如果没调用 C::setDefaultExceptionHandler  则由 App::OnDefaultException 处理 exception 。你可以重写 App::\_OnDefaultException 来实现自己的异常管理，如加日志等等。

### SimpleExceptionTrait/ThrowOnTrait

DuckPhp 工程几乎不得不引用的类之一就是 ThrowOnTrait 这个 trait



ThrowOnTrait 提供个静态方法:

* `public static function ThrowOn($flag, $message, $code)`

这个方法用于如果 $flag 成立，则抛出当前异常类

PHP 有个函数 assert ， ThrowOn 和他逻辑相反。ThrowOn的方式会更直接些


## 给核心工程师的话
DuckPhp 的异常处理，从使用者角度来说，没什么需要的地方
但是 实现确实十分的复杂

有那么几种情况
1 init() 接管错误处理前的错误
2 init() 接管错误处理后的错误。

3 run() 的错误，其中又详细分为：
5 console 下的运行错误
4 其他子应用run 处理完了的错误
5 切换道其他 子应用相位的错误处理 -> 运行时 标记问题， 比如 Ob start 了，没合并怎么办。
