# 异常处理

## 相关类

ThrowOn


## 相关配置

    'handle_all_dev_error' => true,
    'handle_all_exception' => true,
    'error_404' => null,          //'_sys/error-404',
    'error_500' => null,          //'_sys/error-500',
    'error_debug' => null,        //'_sys/error-debug',

    'handle_all_dev_error' => $handle_all_dev_error,
    'handle_all_exception' => $handle_all_exception,

    'system_exception_handler' => $this->hanlder_for_exception_handler,
    'default_exception_handler' => $this->hanlder_for_exception,
    'dev_error_handler' => $this->hanlder_for_develop_exception,
## 说明

## 基本的异常处理

默认异常， 会调用 error_500 选项里设置的 view ，如果是个回调，则调用那个回调。

C::assignExceptionHandler($classes, $callback = null);
为特定 异常类设置回调， 发生异常的时候 $callback($exception);
assign 方法，两种调用形式，另一种调用形势是 assignExceptionHandler([$class=>$callback]);

C::setMultiExceptionHandler(array $classes, $callback)
为了方便多个异常统一调用， 添加了这种错误回调的形势，  $classes 为异常类名列表
C::setDefaultExceptionHandler setDefaultExceptionHandler($callback)
没在异常类列表里的默认调用哦个 $callback($exception);

C::ThrowOn($flag,$message,$code);
C::ThrowOn($flag,$message,$exception_class);
C::ThrowOn($flag,$message,$code,$exception_class);

如果 $flag 成立，则抛出异常。


## 异常处理的一般守则
一般 service 抛出自己错误的时候，来个同名 exception 类
如 SessionServiceException 。由调用者自行解决异常问题。

建议这些 Exception 类继承  BaseExeption 类，并实现一个  render 方法，以便于出错的时候显示
同时， DuckPhp 并没有记录异常。所以要自行处理异常。

## 异常的高级处理