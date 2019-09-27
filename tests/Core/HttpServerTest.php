<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\HttpServer;

class HttpServerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HttpServer::class);
        
        $options=[
            'path_document'=>__DIR__,
        ];
        HttpServerParent::G()->RunQuickly($options);
        HttpServerParent::G()->test_showHelp();
        HttpServerParent::G()->test_run2();

        \MyCodeCoverage::G()->end(HttpServer::class);
        $this->assertTrue(true);
    }
}
class HttpServerParent extends HttpServer
{
    protected $cli_options_ex=[
            'just-test'=>[
                'short'=>'z',
                'desc'=>'do not use swoole httpserver',
                'optional'=>true,
            ],
    ];
    public function test_showHelp()
    {
        $this->showHelp();
    }
    public function test_run2()
    {
        $this->args['help']=true;
        $this->run();
    }
    protected function runHttpServer()
    {
        $this->args['dry']=true;
        return parent::runHttpServer();
    }
}