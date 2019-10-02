<?php
namespace tests\DNMVCS;

use DNMVCS\HttpServer;

class HttpServerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HttpServer::class);
        
        HttpServerParent::G()->test_checkSwoole();
        HttpServerParent::G()->test_runHttpServer();

        HttpServerParent::G()->test_runSwooleServer(__DIR__, '127.0.0.1', 9901);
        defined('DNMVCS_WARNING_IN_TEMPLATE');
        HttpServerParent::G()->test_runSwooleServer(__DIR__, '127.0.0.1', 9901);
        \MyCodeCoverage::G()->end(HttpServer::class);
        $this->assertTrue(true);
        /*
        HttpServer::G()->checkSwoole();
        HttpServer::G()->runHttpServer();
        HttpServer::G()->runSwooleServer($path, $host, $port);
        //*/
    }
}
class HttpServerParent extends HttpServer
{
    public function test_checkSwoole()
    {
        return $this->checkSwoole();
    }
    public function test_runHttpServer()
    {
        $this->options['path']=__DIR__;
        $this->options['dnmvcs']=[
            'skip_setting_file'=>true,
            'error_404'=>null,
            'ext'=>[
                'DNMVCS\SwooleHttpd\SwooleExt'=>false,
            ]
        ];
        
        $this->runHttpServer();
        $this->args['no-swoole']=true;
        $this->runHttpServer();
        unset($this->args['no-swoole']);
        $this->options['background']=true;
        $this->runHttpServer();
    }
    public function test_runSwooleServer($path, $host, $port)
    {
        //$_SERVER['argc']
        $this->options['dnmvcs']=[
            'skip_setting_file'=>true,
            'error_404'=>null,
            'ext'=>[
                'DNMVCS\SwooleHttpd\SwooleExt'=>false,
            ]
        ];
        if(!defined('DNMVCS_WARNING_IN_TEMPLATE')){
            define('DNMVCS_WARNING_IN_TEMPLATE',true);
        }
        return $this->runSwooleServer($path, $host, $port);

    }
}
