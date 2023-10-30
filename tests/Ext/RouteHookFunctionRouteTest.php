<?php
namespace tests\DuckPhp\Ext
{

use DuckPhp\Ext\RouteHookFunctionRoute;
use DuckPhp\Core\App;
use DuckPhp\Core\Route;
use DuckPhp\Ext\SuperGlobalContext;

class RouteHookFunctionRouteTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookFunctionRoute::class);

        $this->doFuncMode();
        
        \LibCoverage\LibCoverage::End();

    }
    protected function doFuncMode()
    {
        $route_options=[
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookFunctionRouteTestMain',
            'controller_prefix_post'=>'do_',

        ];
        Route::_(new Route());
        App::_(new App())->init([$route_options]);
        $options=[
            'path_info_compact_enable' => true,
            
            'function_route'=>true,
            'function_route_method_prefix' => 'myaction_',
            'function_route_404_to_index' => false,

        ];
        RouteHookFunctionRoute::_(new RouteHookFunctionRoute())->init($options, App::_());
        
        $_POST['PATH_INFO'] = "path_info";

        echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n";
        Route::_()->bind('/normal');
        Route::_()->run();
        Route::_()->bind('/');
        Route::_()->run();
        Route::_()->bind('/post');
        Route::_()->run();
        Route::_()->bind('/post','POST');
        Route::_()->run();
        Route::_()->bind('/post2','POST');
        Route::_()->run();
        echo "===------\n";
        Route::_()->bind('/404')->run();
        RouteHookFunctionRoute::_()->options['function_route_404_to_index'] = true;
        Route::_()->bind('/404')->run();
        RouteHookFunctionRoute::_()->options['function_route_method_prefix'] = 'null_';
        Route::_()->bind('/404')->run();
        
    }
}
class RouteHookFunctionRouteTestMain
{    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }
}

}
namespace 
{
    function myaction_index()
    {
        var_dump(__FUNCTION__);
    }
    function myaction_normal()
    {
        var_dump(__FUNCTION__);

    }
    function myaction_post()
    {
        var_dump(__FUNCTION__);
    }

    function myaction_do_post()
    {
        var_dump(__FUNCTION__);
    }
    function myaction_post2()
    {
        var_dump(__FUNCTION__);
    }
    function myaction_do_index()
    {
        var_dump(__FUNCTION__);
    }
}