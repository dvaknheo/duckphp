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

## 公开方法

    public function __construct()
    public function setDefaultExceptionHandler($default_exception_handler)
    public function assignExceptionHandler($class, $callback=null)
    public function setMultiExceptionHandler(array $classes, $callback)
    public function on_error_handler($errno, $errstr, $errfile, $errline)
    public function on_exception($ex)
    public function init($options=[], $context=null)
    public function run()
    public function clear()
    public static function CallException($ex)
    
    public function assignExceptionHandler($class, $callback = null)
    
    public function _CallException($ex)
    
    protected function initOptions(array $options)
    
    public function isInited():bool

以上为函数列表

## 详解

'system_exception_handler' ，'default_exception_handler' => null ，'dev_error_handler' => null 这三个选项内部使用一般不会去动

ExeptionManager 在 App 前初始化
