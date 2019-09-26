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

        \MyCodeCoverage::G()->end(HttpServer::class);
        $this->assertTrue(true);
        /*
        HttpServer::G()->RunQuickly($options);
        HttpServer::G()->init($options=[], $context=null);
        HttpServer::G()->parseCaptures($cli_options);
        HttpServer::G()->run();
        HttpServer::G()->showWelcome();
        HttpServer::G()->showHelp();
        HttpServer::G()->runHttpServer();
        //*/
    }
}
class HttpServerParent extends HttpServer
{
    public function test_showHelp()
    {
        $this->showHelp();
    }
    protected function runHttpServer()
    {
        $this->args['dry']=true;
        return parent::runHttpServer();
    }
}