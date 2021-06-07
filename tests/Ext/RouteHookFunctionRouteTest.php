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
        Route::G(new Route());
        App::G(new App())->init([$route_options]);
        $options=[
            'path_info_compact_enable' => true,
            
            'function_route'=>true,
            'function_route_method_prefix' => 'myaction_',
            'function_route_404_to_index' => false,

        ];
        RouteHookFunctionRoute::G(new RouteHookFunctionRoute())->init($options, App::G());
        
        $_POST['PATH_INFO'] = "path_info";

        echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n";
        App::Route()->reset()->bind('/normal');
        Route::G()->run();
        App::Route()->reset()->bind('/');
        Route::G()->run();
        App::Route()->bind('/post');
        Route::G()->run();
        App::Route()->bind('/post','POST');
        Route::G()->run();
        App::Route()->bind('/post2','POST');
        Route::G()->run();
        echo "===------\n";
        App::Route()->bind('/404')->run();
        RouteHookFunctionRoute::G()->options['function_route_404_to_index'] = true;
        App::Route()->bind('/404')->run();
        RouteHookFunctionRoute::G()->options['function_route_method_prefix'] = 'null_';
        App::Route()->bind('/404')->run();
        
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