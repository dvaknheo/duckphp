# DuckPhp\Core\Logger
[toc]

## 简介
日志组件 ，满足 psr 标准的日志组件

## 使用于

[DuckPhp\Core\App](Core-App.md)

## 选项

        'path' => '',
基础路径

        'path_log' => 'logs',
日志目录路径

        'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
日志文件名模板 

        'log_prefix' => 'DuckPhpLog',
日志前缀
## 方法
### 主流程方法
```php
    public function __construct()
    public function reset()
    public function init(array $options, object $context = null)
    protected function initOptions(array $options)
```
### psr-16 标准方法
```php
    public function log($level, $message, array $context = array())
    public function emergency($message, array $context = array())
    public function alert($message, array $context = array())
    public function critical($message, array $context = array())
    public function error($message, array $context = array())
    public function warning($message, array $context = array())
    public function notice($message, array $context = array())
    public function info($message, array $context = array())
    public function debug($message, array $context = array())
```

## 说明

App::Logger() 函数得到的就是这个类

Logger 类初始化的时候就直接调用 init() ，你可调用 reset() 重置

其他方法都遵循 PSR 标准 **但是这个类没实现 PSR 接口。**

放在 Core 下是因为 处理默认异常会记录
