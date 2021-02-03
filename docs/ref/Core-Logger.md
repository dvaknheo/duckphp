# DuckPhp\Core\Logger
[toc]

## 简介
日志组件 ，满足 psr 标准的 日志类

## 使用于

[DuckPhp\Core\Kernel](Core-Kernel.md)

## 选项

'path' => '',
    
    基础路径
'path_log' => 'logs',

    日志文件名称
'log_prefix' => 'DuckPhpLog',

    日志前缀
'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',

   日志文件模板 
## 方法
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
## 详解

App::Logger() 函数得到的就是这个类

Logger 类初始化的时候就直接调用 init() ，你可调用 reset() 重置

其他方法都遵循 PSR 标准 **但是这个类没实现 PSR 接口。**

自带 reset() 方法 ，可以重置


