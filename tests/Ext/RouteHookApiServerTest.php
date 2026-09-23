<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Ext\RouteHookApiServer;
use DuckPhp\Core\SingletonExTrait as SingletonExTrait;

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
            
            'apiserver_base_class' => '~BaseApi',
            'apiserver_namespace' => '\tests\DuckPhp\Ext',
            'apiserver_class_postfix' => 'API',
            //'apiserver_config_cache_file' => '',
            //'apiserver_on_missing' => '',
            'apiserver_use_singletonex' => false,
            'apiserver_404_as_exception' => false,
            'cli_enable'=>false,
        ];
        
        DuckPhp::_()->init($options);
        Route::_()->bind('/test.foo2');
        $_REQUEST=['a'=>'1','b'=>3];
        Route::_()->run();
        
        Route::_()->bind('/test.mustexception');
        DuckPhp::_()->run();
        
        Route::_()->bind('/testbad.foo');
        DuckPhp::_()->run();
        
        Route::_()->bind('/test.foo3');
        $_REQUEST=['name'=>'a','id'=>[]];
        DuckPhp::_()->run();
        
        Route::_()->bind('/test.mustarg');
        DuckPhp::_()->run();
                Route::_()->bind('/test.mustarg2');
        DuckPhp::_()->run();
        
        DuckPhp::_()->options['is_debug']=false;
        Route::_()->bind('/test.foo2');
        $_POST = ['a'=>'1','b'=>3];
        DuckPhp::_()->run();
        
        
        RouteHookApiServer::_()->options['apiserver_404_as_exception']=true;
        Route::_()->bind('/');
        DuckPhp::_()->run();
        
        RouteHookApiServer::_()->options['apiserver_use_singletonex']=true;
        Route::_()->bind('/test.G');
        DuckPhp::_()->run();
        Route::_()->bind('/test.foo');
        DuckPhp::_()->run();
        
        
        $options = [
            'is_debug'=>true,
            'override_class'=>'',
            'ext'=>[
                RouteHookApiServer::class => true,
            ],
            'namespace'=>'tests',
            'apiserver_base_class' => '~BaseApi',
            'apiserver_namespace' => 'DuckPhp\Ext',
            'apiserver_class_postfix' => 'API',
            'apiserver_use_singletonex' => false,
            'apiserver_404_as_exception' => false,
            'cli_enable'=>false,
        ];
        
        DuckPhp::_()->init($options);
        Route::_()->bind('/test.foo2');
        $_REQUEST=['a'=>'1','b'=>3];
        Route::_()->run();
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