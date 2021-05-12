# DuckPhp\Core\ExceptionManager
[toc]

## 简介
异常管理组件

## 选项
全部选项

        'handle_all_dev_error' => true,
抓取调试错误

        'handle_all_exception' => true,
抓取全部异常

        'system_exception_handler' => null,
系统的异常调试回调

        'default_exception_handler' => null,
默认的异常处理回调

        'dev_error_handler' => null,
调试错误的回调

'system_exception_handler' ，'default_exception_handler' => null ，'dev_error_handler' => null 这三个选项内部使用一般不会去动

## 公开方法

    public function __construct()
    public function init($options=[], $context=null)
    public function run()
    protected function initOptions(array $options)
    public function isInited():bool
基本流程函数

    public function clear()
清理

    public function reset()
和 clear 不同的是没重设 handler,AppPluginTrait 用到

    public function assignExceptionHandler($class, $callback = null)
    public function setMultiExceptionHandler(array $classes, $callback)
    public function setDefaultExceptionHandler($default_exception_handler)
相关业务函数

    public static function CallException($ex)
    public function _CallException($ex)
给特定的异常，调用处理程序。

    public function on_error_handler($errno, $errstr, $errfile, $errline)
默认回调用

    public function on_exception($ex)
默认回调用


## 说明

