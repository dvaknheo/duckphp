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
        Route::G()->bind('/test.foo2',);
        DuckPhp::SuperGlobal()->_REQUEST=['a'=>'1','b'=>3];
        Route::G()->run();
////
        \MyCodeCoverage::G()->end();

    }
}
class BaseApi
{
    //use \DuckPhp\Core\SingletonEx;
}
class API_test extends BaseApi
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
    public function foo2($a,$b)
    {
        return [$a+$b, DATE(DATE_ATOM)];
    }
}