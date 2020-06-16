# DuckPhp\Core\HttpServer

## 简介
`组件` Http 服务器类，实现一个简单的 Http 服务
## 选项

## 公开方法
public function __construct()
public static function RunQuickly($options)
public function init(array $options, object $context = null)
public function run()

protected function getopt($options, $longopts, &$optind)
protected function parseCaptures($cli_options)

public function getPid()
public function close()

protected function showWelcome()

    显示欢迎信息，用于重写
protected function showHelp()

    显示帮助信息
protected function runHttpServer()

    开始运行

## 详解
虽然是个组件类，但是只是 起服务器而已