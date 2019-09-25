<?php 
namespace tests\DNMVCS;
use DNMVCS\HttpServer;

class HttpServerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HttpServer::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(HttpServer::class);
        $this->assertTrue(true);
        /*
        HttpServer::G()->checkSwoole();
        HttpServer::G()->runHttpServer();
        HttpServer::G()->runSwooleServer($path, $host, $port);
        //*/
    }
}
