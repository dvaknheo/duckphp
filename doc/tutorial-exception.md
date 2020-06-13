# 异常处理
[toc]
## 相关类

ThrowOn , ExceptionManager


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

### 基本的异常处理

默认异常， 会调用 error_500 选项里设置的 view ，如果值是回调闭包，则调用那个回调。

### 控制器处理异常

C::assignExceptionHandler($classes, $callback = null);
C::assignExceptionHandler([$class=>$callback]);

    为特定 异常类设置回调， 发生异常的时候 $callback($exception);这个方法是 assign 方法，两种调用形式，另一种调用形势是 

C::setMultiExceptionHandler(array $classes, $callback)

    为了方便多个异常统一调用， 添加了这种错误回调的形势，  $classes 为异常类名列表
C::setDefaultExceptionHandler setDefaultExceptionHandler($callback)

    没在异常类列表里的默认调用 $callback($exception);

C::ThrowOn($flag,$message,$code);
C::ThrowOn($flag,$message,$exception_class);
C::ThrowOn($flag,$message,$code,$exception_class);

    如果 $flag 成立，则抛出异常。ThrowOn 使用于所有助手类，以及 use DuckPhp\Core\ThrowOn 的类。如果该类是 异常类，则默认的 $exception_class 为 该类，否则为 \Exception

### 异常处理的一般守则

Model 不抛出异常。Service 抛出自己错误的时候，来个同名 exception 类。如 SessionService => SessionServiceException 。由调用者自行解决异常问题。

建议这些 Exception 类继承  BaseExeption 类，并实现一个  display($ex) 方法，以便于出错的时候显示
同时， DuckPhp 并没有自动记录异常。所以要自行 Log。

### 异常的高级处理
'error_debug' => null, 选项，用于

'handle_all_dev_error' => true, 'handle_all_exception' => true, 选项用于接管系统错误。
该选项用于 DuckPhp\DuckPhp 类，而不是 MY\Base\App 这样后者接管类

'skip_exception_check'=>false, 开启时候 运行阶段，跳过异常检查抛出给上层。如果你不打算自己管理错误的话。 技巧，管理错误的时候，把这个选项 打开 throw $ex ；则由上层管理错误
	
如果没调用 C::setDefaultExceptionHandler  则由 APP::OnDefaultException 处理 exception 。你可以重写 APP::\_OnDefaultException 来实现自己的异常管理，如加日志等等。
