# DucPhp\HttpServer

## 简介

Http 服务器，DuckPhpCore\HttpServer 扩展了一些东西

扩充自 DuckPhp\Core\HttpServer
## 选项
    public function __construct()
    protected function checkSwoole()
    protected function runHttpServer()
    protected function runSwooleServer($path, $host, $port)
    
## 详解

未来可扩充到使用 Swoole Http 。但目前和 DuckPhp\Core\HttpServer 无区别