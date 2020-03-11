# Core\ExceptionManager

## 简介
异常管理类

组件类
## 选项
'handle_all_dev_error' => true,
'handle_all_exception' => true,
'system_exception_handler' => null,

'default_exception_handler' => null,
'dev_error_handler' => null,
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
## 详解

