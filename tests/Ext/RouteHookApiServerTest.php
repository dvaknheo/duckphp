<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Ext\RouteHookApiServer;

class RouteHookApiServerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookApiServer::class);
        $options = [
            'is_debug'=>true,
            'skip_setting_file'=>true,
            'override_class'=>'',
            'ext'=>[
                RouteHookApiServer::class => true,
            ],
            'api_class_base'=>'tests\DuckPhp\Ext\\'.'BaseApi', 
            'api_class_prefix'=>'tests\DuckPhp\Ext\\'.'Api_',
        ];
        DuckPhp::G()->init($options);
        Route::G()->bind('/test.foo2');
        DuckPhp::SuperGlobal()->_REQUEST=['a'=>'1','b'=>3];
        Route::G()->run();
        
        Route::G()->bind('/test.mustexception');
        DuckPhp::G()->run();
        
        Route::G()->bind('/testbad.foo');
        DuckPhp::G()->run();
        
        Route::G()->bind('/test.foo3');
        DuckPhp::SuperGlobal()->_REQUEST=['name'=>'a','id'=>[]];
        DuckPhp::G()->run();
        
        Route::G()->bind('/test.mustarg');
        DuckPhp::G()->run();
                Route::G()->bind('/test.mustarg2');
        DuckPhp::G()->run();
        
        DuckPhp::G()->options['is_debug']=false;
        Route::G()->bind('/test.foo2');
        DuckPhp::SuperGlobal()->_POST = ['a'=>'1','b'=>3];
        Route::G()->run();

////
        \MyCodeCoverage::G()->end();

    }
}
class BaseApi
{
    //use \DuckPhp\SingletonEx\SingletonEx;
}
class API_test extends BaseApi
{
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