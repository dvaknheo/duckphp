<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Core\Route;
use DuckPhp\DuckPhp as App;

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
        Route::_(new Route())->init($route_options);
        App::_()->init([]);
        
        $options=[
            'route_map'=>[
                //'/first'=>function(){echo "first1111 \n";},
            ],
        ];
        
        $options['route_map_important']['@posts/{post}/comments/{comment:\d+}'] = RouteHookRouteMapTest_FakeObject::class.'@foo';
        
        RouteHookRouteMap::_()->init($options, App::_());
        RouteHookRouteMap::_()->assignRoute('/first',RouteHookRouteMapTest_FakeObject::class.'::'.'fifth');
//        RouteHookRouteMap::_()->assignRoute('/sixth','~RouteHookRouteMapTest_FakeObject::sixth');
        RouteHookRouteMap::_()->assignRoute('/seventh',[RouteHookRouteMapTest_FakeObject::class,'seventh']);
        RouteHookRouteMap::_()->assignRoute('^second(/(?<id>\d+))?',RouteHookRouteMapTest_FakeObject::class.'@'.'second');
        RouteHookRouteMap::_()->assignRoute(['/third*'=>RouteHookRouteMapTest_FakeObject::class.'->'.'adjustCallbackArrow']);
        RouteHookRouteMap::_()->assignImportantRoute(['@posts/{post}/comments/{comment:\d+}'=>RouteHookRouteMapTest_FakeObject::class.'@foo']);
        RouteHookRouteMap::_()->assignImportantRoute('@posts/{post}/comments/{comment:\d+}',RouteHookRouteMapTest_FakeObject::class.'@foo');
        


        RouteHookRouteMap::_()->getRoutes();
        
        Route::_()->bind('/')->run();
        Route::_()->bind('/first')->run();
        Route::_()->bind('/second/1')->run();
        Route::_()->bind('/third/abc/d/e')->run();
        Route::_()->bind('/thirdabc/d/e')->run();
        Route::_()->bind('/posts/aa/comments/33')->run();
        Route::_()->bind('/fifth')->run();
        Route::_()->bind('/sixth')->run();
        Route::_()->bind('/seventh')->run();

        var_dump("-------------------------");
        $path=\LibCoverage\LibCoverage::G()->getClassTestPath(RouteHookRouteMap::class);
        App::_()->init([
            'path'=>$path,
            'path_config'=>'',
            
        ]);
        $options['route_map_by_config_name']='routes';
        RouteHookRouteMap::_(new RouteHookRouteMap())->init($options, App::_());
        RouteHookRouteMap::_()->options['route_map_important']=[];
        Route::_()->bind('/posts/aa/comments/33')->run();
        //Route::_()->bind('/eighth')->run();
        
        
        App::_(new App())->init([
            'path'=>$path,
            'path_config'=>'',            
        ]);
        $options['route_map_by_config_name']=null;

        $options['controller_url_prefix']='admin/';
        RouteHookRouteMap::_(new RouteHookRouteMap())->init($options, App::_());
        RouteHookRouteMap::_()->assignRoute('/night','~RouteHookRouteMapTest_FakeObject::night');
        Route::_()->bind('/admin/night')->run();
        Route::_()->bind('/night')->run();
        

        RouteHookRouteMap::_()->isInited();
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
    public static function _()
    {
        return new static();
    }
    public function __construct()
    {
        echo "Main Class Start...";
    }
    function second()
    {
        var_dump(Route::_()->_Parameter());
    }
    public static function fifth()
    {
        var_dump(Route::_()->_Parameter());
    }
    public static function sixth()
    {
        var_dump(Route::_()->_Parameter());
    }
    public static function seventh()
    {
        var_dump("seventh!");
        var_dump(Route::_()->_Parameter());
    }
    function eighth()
    {
        var_dump("eight!");
        var_dump(Route::_()->_Parameter());
    }
    function night()
    {
        var_dump("night!");
        var_dump(Route::_()->_Parameter());
    }
    function foo()
    {
        var_dump(Route::_()->_Parameter());
    }
    function adjustCallbackArrow()
    {
        echo __METHOD__;echo PHP_EOL;
    }
}