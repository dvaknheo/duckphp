# DuckPhp\HttpServer\HttpServer
[toc]

## 简介
Http 独立组件，实现一个简单的 Http 服务

## 选项
全部选项

        'host' => '127.0.0.1',
HTTP服务器，Host

        'port' => '8080',
HTTP服务器，端口

        'path' => '',
工程路径

        'path_document' => 'public',
文档路径

## 方法
### 公开方法

    public static function RunQuickly($options)
快速运行

    public function run()
运行

    public function getPid()
获取运行时候的 PID

    public function close()
关闭

### 基本方法

    public function init(array $options, object $context = null)

初始化

    public static function G($object = null)
    public function __construct()
    public function isInited(): bool


### 内部方法

    protected function getopt($options, $longopts, &$optind)
处理选项

    protected function parseCaptures($cli_options)
处理命令行选项

    protected function showWelcome()
显示欢迎信息，用于重写

    protected function showHelp()
显示帮助信息，用于重写

    protected function runHttpServer()

开始运行 Http 服务器
## 详解
虽然是个组件类，但是在流程中没用到，在 start_server.php 的时候用

命令行的参数会合并进来
