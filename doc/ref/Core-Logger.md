# Core\Logger

## 简介
`组件类` 日志类，满足 psr 标准的 日志类

## 使用于
[DuckPhp\Core\App](Core-Kernel.md)
## 选项

## 方法




## 详解

App::Logger() 函数得到的就是这个类



    public function __construct()
    public function reset()
    public function init(array $options, object $context = null)
    public function log($level, $message, array $context = array())
    public function emergency($message, array $context = array())
    public function alert($message, array $context = array())
    public function critical($message, array $context = array())
    public function error($message, array $context = array())
    public function warning($message, array $context = array())
    public function notice($message, array $context = array())
    public function info($message, array $context = array())
    public function debug($message, array $context = array())