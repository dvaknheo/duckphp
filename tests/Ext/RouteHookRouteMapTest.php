<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookRouteMap;

class RouteHookRouteMapTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookRouteMap::class);
        
        //code here
        
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
