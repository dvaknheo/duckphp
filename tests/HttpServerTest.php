<?php
namespace tests\DuckPhp;

use DuckPhp\HttpServer;

class HttpServerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HttpServer::class);
        
        
            
        $path=\MyCodeCoverage::GetClassTestPath(HttpServer::class);
        
        
            
        HttpServerParent::G()->test_checkSwoole();
        HttpServerParent::G()->test_runHttpServer();

        HttpServerParent::G()->test_runSwooleServer(__DIR__, '127.0.0.1', 9901);
        defined('DuckPhp_WARNING_IN_TEMPLATE');
        HttpServerParent::G()->test_runSwooleServer(__DIR__, '127.0.0.1', 9901);
        
        if (!function_exists('swoole_version')) {
            include $path."fake_swoole.php";
            var_dump("ffffffffffffffffffffff");
            HttpServerParent::G(new HttpServerParent())->is_fake=true;
            HttpServerParent::G()->test_runSwooleServer2(__DIR__, '127.0.0.1', 9901);
        }
        
        
            if (!function_exists('swoole_version')) {
                include $path."fake_swoole.php";
                var_dump("ffffffffffffffffffffff");
                HttpServerParent::G()->is_fake=true;
                HttpServerParent::G()->test_runSwooleServer2(__DIR__, '127.0.0.1', 9901);
            }
            
            
        \MyCodeCoverage::G()->end();
        
        return;
        
        /*
        HttpServer::G()->checkSwoole();
        HttpServer::G()->runHttpServer();
        HttpServer::G()->runSwooleServer($path, $host, $port);
        //*/
    }
}
class HttpServerParent extends HttpServer
{
    public $is_fake=false;
    
    public function test_checkSwoole()
    {
        return $this->checkSwoole();
    }
    public function test_runHttpServer()
    {
        $this->options['path']=__DIR__;
        $this->options['duckphp']=[
            'skip_setting_file'=>true,
            'error_404'=>null,
            'ext'=>[
                'DuckPhp\PluginForSwoole'=>false,
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
                'DuckPhp\SwooleHttpd\SwooleExt'=>false,
            ]
        ];
        if(!defined('DuckPhp_WARNING_IN_TEMPLATE')){
            define('DuckPhp_WARNING_IN_TEMPLATE',true);
        }
        return $this->runSwooleServer($path, $host, $port);

    }
    public function test_runSwooleServer2($path, $host, $port)
    {
        return $this->runHttpServer($path, $host, $port);

    }
    protected function runSwooleServer($path, $host, $port)
    {
        if($this->is_fake){
            var_dump("fffffffffffffffffff");
            return false;
        }
        return parent::runSwooleServer($path, $host, $port);
    }
}
