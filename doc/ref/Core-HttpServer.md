# Core\HttpServer

## 简介
Http 服务器类，实现一个简单的 Http 服务
组件类

## 选项

## 公开方法
    public function __construct()
    public static function RunQuickly($options)
    public function init($options=[], $context=null)
    public function run()
    public function getPid()
    public function close()

## 详解

    public function __construct()
    public static function RunQuickly($options)
    public function init(array $options, object $context = null)
    protected function getopt($options, $longopts, &$optind)
    protected function parseCaptures($cli_options)
    public function run()
    public function getPid()
    public function close()
    protected function showWelcome()
    protected function showHelp()
    protected function runHttpServer()