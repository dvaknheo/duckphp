<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\RouteHookRouteMap;
use DuckPhp\Core\Route;
use DuckPhp\Core\APP;

class RouteHookRouteMapTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookRouteMap::class);
        
        $route_options=[
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookRouteMapTestMain',

        ];
        Route::G(new Route())->init($route_options);
        
        $options=[
            'route_map'=>[
                '/first'=>function(){echo "first1111 \n";},
            ],
        ];
        
        $options['route_map_important']['@posts/{post}/comments/{comment:\d+}'] = RouteHookRouteMapTest_FakeObject::class.'@foo';
        //$this->options['route_map_important']['~abc/d(/?|)\w*'] = [$this,'foo'];
        
        RouteHookRouteMap::G()->init($options, Route::G());
        
        RouteHookRouteMap::G()->assignRoute('^second(/(?<id>\d+))?',RouteHookRouteMapTest_FakeObject::class.'@'.'second');
        RouteHookRouteMap::G()->assignRoute(['/third*'=>RouteHookRouteMapTest_FakeObject::class.'->'.'adjustCallbackArrow']);
        RouteHookRouteMap::G()->assignImportantRoute(['@posts/{post}/comments/{comment:\d+}'=>RouteHookRouteMapTest_FakeObject::class.'@foo']);
        RouteHookRouteMap::G()->assignImportantRoute('@posts/{post}/comments/{comment:\d+}',RouteHookRouteMapTest_FakeObject::class.'@foo');
        RouteHookRouteMap::G()->getRoutes();
        
        Route::G()->bind('/')->run();
        Route::G()->bind('/first')->run();
        Route::G()->bind('/second/1')->run();
        Route::G()->bind('/third/abc/d/e')->run();
        Route::G()->bind('/thirdabc/d/e')->run();
        Route::G()->bind('/posts/aa/comments/33')->run();
        
        var_dump("-------------------------");
        RouteHookRouteMap::G(new RouteHookRouteMap())->init($options, App::G());
        RouteHookRouteMap::G()->options['route_map_important']=[];
        Route::G()->bind('/posts/aa/comments/33')->run();
        \MyCodeCoverage::G()->end(RouteHookRouteMap::class);
        $this->assertTrue(true);
    }
}
class RouteHookRouteMapTestMain{
    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }


}
class RouteHookRouteMapTest_FakeObject
{
    public function __construct()
    {
        echo "Main Class Start...";
    }
    function second()
    {
        var_dump(Route::G()->getParameters());
    }
    function foo()
    {
        var_dump(Route::G()->getParameters());
    }
    function adjustCallbackArrow()
    {
        //var_dump(Route::Parameters());
        //var_dump(Route::G()->path_info);
        echo __METHOD__;echo PHP_EOL;
    }
}