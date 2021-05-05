# 异常处理
[toc]
## 相关类
App

[DuckPhp\ThrowOn\ThrowOnTrait](ref/ThrowOn-ThrowOnTrait.md) 可抛类
[DuckPhp\Core\ExceptionManager](ref/Core-ExceptionManager.md) 异常管理类




## 相关配置
'error_404' => null,          //'_sys/error-404',

    404 视图/闭包
'error_500' => null,          //'_sys/error-500',

    异常视图/闭包
'error_debug' => null,        //'_sys/error-debug',

    调试视图/闭包

以下是高级的选项。

'skip_exception_check'=>false,

    运行阶段，跳过异常检查抛出给上层
'handle_all_dev_error' => true,

    接管调试错误
'handle_all_exception' => true,

    接管所有异常管理

## 基本的异常处理

默认异常， 会调用 error_500 选项里设置的 view ，如果值是闭包则回调闭包而不是显示。
默认异常处理会做以下选项：

### 控制器处理异常

C::assignExceptionHandler($classes, $callback = null);
C::assignExceptionHandler([$class=>$callback]);

    为特定 异常类设置回调， 发生异常的时候 $callback($exception);这个方法是 assign 方法，两种调用形式。

C::setMultiExceptionHandler(array $classes, $callback)

    为了方便多个异常统一调用， 添加了这种错误回调的形势，  $classes 为异常类名列表
C::setDefaultExceptionHandler($callback)

    没在异常类列表里的默认调用 $callback($exception);

### 异常处理的一般守则

Model 不抛出异常。

Business 抛出自己错误的时候，来个同名 exception 类。如 SessionBusiness => SessionException 。由调用者自行解决异常问题。

建议这些 Exception 类继承  BaseExeption 类，并实现一个 `display($ex)` 方法，以便于出错的时候显示

### 异常的高级处理

'handle_all_dev_error' => true, 'handle_all_exception' => true, 选项用于接管系统错误。

'skip_exception_check'=>false, 开启时候 运行阶段，跳过异常检查抛出给上层。如果你不打算自己管理错误的话。 技巧，管理错误的时候，把这个选项 打开 throw $ex ；则由上层管理错误
    
'default_exception_do_log' => true,
'default_exception_self_display' => true,

如果没调用 C::setDefaultExceptionHandler  则由 App::OnDefaultException 处理 exception 。你可以重写 App::\_OnDefaultException 来实现自己的异常管理，如加日志等等。

### ThrowOnTrait

DuckPhp 工程几乎不得不引用的类之一就是 ThrowOnTrait 这个 trait



ThrowOnTrait 提供了三个静态方法:

* `public static function ThrowOn($flag, $message, $code)`

这个方法用于如果 $flag 成立，则抛出当前异常类

PHP 有个函数 assert ， ThrowOn 和他逻辑相反。ThrowOn的方式会更直接些

* `public static function Handle($class)`

把本来 $class ThrowOn 到本类的异常 ， Throw 到当前异常类。

这个方法的作用是用于提供第三方异常类的时候。让人无缝处理异常类。

* `public static function Proxy($ex)`

throw new static($ex->getMessage, $ex->getCode());

用于把其他异常转成自己异常
