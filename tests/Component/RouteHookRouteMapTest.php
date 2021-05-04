<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Core\Route;
use DuckPhp\Core\App;

class RouteHookRouteMapTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookRouteMap::class);
        
        $route_options=[
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookRouteMapTestMain',

        ];
        Route::G(new Route())->init($route_options);
        App::G()->init([]);
        
        $options=[
            'route_map'=>[
                //'/first'=>function(){echo "first1111 \n";},
            ],
        ];
        
        $options['route_map_important']['@posts/{post}/comments/{comment:\d+}'] = RouteHookRouteMapTest_FakeObject::class.'@foo';
        
        RouteHookRouteMap::G()->init($options, App::G());
        RouteHookRouteMap::G()->assignRoute('/first',RouteHookRouteMapTest_FakeObject::class.'::'.'fifth');
        RouteHookRouteMap::G()->assignRoute('/sixth','~RouteHookRouteMapTest_FakeObject::sixth');
        RouteHookRouteMap::G()->assignRoute('/seventh',[RouteHookRouteMapTest_FakeObject::class,'seventh']);
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
        Route::G()->bind('/fifth')->run();
        Route::G()->bind('/sixth')->run();
        Route::G()->bind('/seventh')->run();

        var_dump("-------------------------");
        $path=\LibCoverage\LibCoverage::G()->getClassTestPath(RouteHookRouteMap::class);
        App::G()->init([
            'path'=>$path,
            'path_config'=>'',
            
        ]);
        $options['route_map_by_config_name']='routes';
        RouteHookRouteMap::G(new RouteHookRouteMap())->init($options, App::G());
        RouteHookRouteMap::G()->options['route_map_important']=[];
        Route::G()->bind('/posts/aa/comments/33')->run();
        //Route::G()->bind('/eighth')->run();

        RouteHookRouteMap::G()->isInited();
        \LibCoverage\LibCoverage::End();
    }
}
class RouteHookRouteMapTestMain{
    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }


}
class RouteHookRouteMapTest_FakeObject
{
    public static function G()
    {
        return new static();
    }
    public function __construct()
    {
        echo "Main Class Start...";
    }
    function second()
    {
        var_dump(Route::G()->_Parameter());
    }
    public static function fifth()
    {
        var_dump(Route::G()->_Parameter());
    }
    public static function sixth()
    {
        var_dump(Route::G()->_Parameter());
    }
    public static function seventh()
    {
        var_dump("seventh!");
        var_dump(Route::G()->_Parameter());
    }
    function eighth()
    {
        var_dump("eight!");
        var_dump(Route::G()->_Parameter());
    }
    function foo()
    {
        var_dump(Route::G()->_Parameter());
    }
    function adjustCallbackArrow()
    {
        echo __METHOD__;echo PHP_EOL;
    }
}