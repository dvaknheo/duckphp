<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Ext\RouteHookApiServer;
use DuckPhp\SingletonEx\SingletonExTrait;

class RouteHookApiServerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookApiServer::class);
        $options = [
            'is_debug'=>true,
            'override_class'=>'',
            'ext'=>[
                RouteHookApiServer::class => true,
            ],
            
            'api_server_base_class' => '~BaseApi',
            'api_server_namespace' => '\tests\DuckPhp\Ext',
            'api_server_class_postfix' => 'API',
            //'api_server_config_cache_file' => '',
            //'api_server_on_missing' => '',
            'api_server_use_singletonex' => false,
            'api_server_404_as_exception' => false,
            'cli_enable'=>false,
        ];
        
        DuckPhp::G()->init($options);
        Route::G()->bind('/test.foo2');
        $_REQUEST=['a'=>'1','b'=>3];
        Route::G()->run();
        
        Route::G()->bind('/test.mustexception');
        DuckPhp::G()->run();
        
        Route::G()->bind('/testbad.foo');
        DuckPhp::G()->run();
        
        Route::G()->bind('/test.foo3');
        $_REQUEST=['name'=>'a','id'=>[]];
        DuckPhp::G()->run();
        
        Route::G()->bind('/test.mustarg');
        DuckPhp::G()->run();
                Route::G()->bind('/test.mustarg2');
        DuckPhp::G()->run();
        
        DuckPhp::G()->options['is_debug']=false;
        Route::G()->bind('/test.foo2');
        $_POST = ['a'=>'1','b'=>3];
        DuckPhp::G()->run();
        
        
        RouteHookApiServer::G()->options['api_server_404_as_exception']=true;
        Route::G()->bind('/');
        DuckPhp::G()->run();
        
        RouteHookApiServer::G()->options['api_server_use_singletonex']=true;
        Route::G()->bind('/test.G');
        DuckPhp::G()->run();
        Route::G()->bind('/test.foo');
        DuckPhp::G()->run();
        
        
        $options = [
            'is_debug'=>true,
            'override_class'=>'',
            'ext'=>[
                RouteHookApiServer::class => true,
            ],
            'namespace'=>'tests',
            'api_server_base_class' => '~BaseApi',
            'api_server_namespace' => 'DuckPhp\Ext',
            'api_server_class_postfix' => 'API',
            'api_server_use_singletonex' => false,
            'api_server_404_as_exception' => false,
            'cli_enable'=>false,
        ];
        
        DuckPhp::G()->init($options);
        Route::G()->bind('/test.foo2');
        $_REQUEST=['a'=>'1','b'=>3];
        Route::G()->run();
////
        \LibCoverage\LibCoverage::End();

    }
}
class BaseApi
{
    //use \DuckPhp\SingletonEx\SingletonExTrait;
}
class testAPI extends BaseApi
{
    use SingletonExTrait;
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
    public function mustexception()
    {
        throw new \Exception("aaa",1111);
    }
    public function foo2($a,$b)
    {
        return [$a+$b, DATE(DATE_ATOM)];
    }
    public function foo3(string $name,int $id)
    {
        return DATE(DATE_ATOM);
    }

    public function mustarg($aaaaaa)
    {
        return DATE(DATE_ATOM);
    }
    public function mustarg2($ixxxxxxxxxxxd="123")
    {
        return DATE(DATE_ATOM);
    }
}
class API_testbad
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}