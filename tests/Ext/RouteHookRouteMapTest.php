<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookRouteMap;
use DNMVCS\Core\Route;

class RouteHookRouteMapTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookRouteMap::class);
        
        $options=[
			'abc'=>function(){},
        ];
        RouteHookRouteMap::G()->init($options, $context=null);
        RouteHookRouteMap::G()->assignRoute('def',function(){});
        RouteHookRouteMap::G()->assignRoute(['hij',function(){}]);
        RouteHookRouteMap::G()->getRoutes();

        $options=[
        ];
        Route::G(new Route())->init($options);
        
        RouteHookRouteMap::Hook(Route::G());
        

        Route::G()->bindServerData([
            'PATH_INFO'=>'Missed',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        

\MyCodeCoverage::G()->end(RouteHookRouteMap::class);
        $this->assertTrue(true);
        /*
        RouteHookRouteMap::G()->init($options=[], $context=null);
        RouteHookRouteMap::G()->assignRoute($key, $value=null);
        RouteHookRouteMap::G()->getRoutes();
        RouteHookRouteMap::G()->matchRoute($pattern_url, $path_info, $route);
        RouteHookRouteMap::G()->getRouteHandelByMap($route, $routeMap);
        RouteHookRouteMap::G()->adjustCallback($callback);
        RouteHookRouteMap::G()->Hook($route);
        RouteHookRouteMap::G()->_Hook($route);
        //*/
    }
}
