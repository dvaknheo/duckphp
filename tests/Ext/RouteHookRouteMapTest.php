<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookRouteMap;
use DNMVCS\Core\Route;

class RouteHookRouteMapTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookRouteMap::class);
        
        $route_options=[
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
        ];
        Route::G(new Route())->init($route_options);
        
        $options=[
            'route_map'=>[
                '/first'=>function(){echo "first1111 \n";},
            ],
        ];
        RouteHookRouteMap::G()->init($options, Route::G());
        
        RouteHookRouteMap::G()->assignRoute('~second(/(?<id>\d+))?',FakeObject::class.'@'.'second');
        RouteHookRouteMap::G()->assignRoute(['/third*'=>FakeObject::class.'->'.'adjustCallbackArrow']);
        RouteHookRouteMap::G()->getRoutes();
        
        Route::G()->bind('/')->run();
        Route::G()->bind('/first')->run();
        Route::G()->bind('/second/1')->run();
        Route::G()->bind('/third/abc/d/e')->run();
        Route::G()->bind('/thirdabc/d/e')->run();

        \MyCodeCoverage::G()->end(RouteHookRouteMap::class);
        $this->assertTrue(true);
    }
}
class Main{
    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }

}
class FakeObject
{
    public function __construct()
    {
        echo "Main Class Start...";
    }
    function second()
    {
        var_dump(Route::Parameters());
    }
    function adjustCallbackArrow()
    {
        //var_dump(Route::Parameters());
        //var_dump(Route::G()->path_info);
        echo __METHOD__;echo PHP_EOL;
    }
}