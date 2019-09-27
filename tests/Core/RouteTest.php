<?php
namespace tests\DNMVCS\Core
{

use DNMVCS\Core\Route;

class RouteTest extends \PHPUnit\Framework\TestCase
{
    public function testA()
    {
        \MyCodeCoverage::G()->begin(Route::class);
        
        //Main
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        Route::RunQuickly($options);

        $flag=Route::RunQuickly($options, function () {
            //Route::G()->prepend(function(){return false;});
            //Route::G()->append(function(){return false;});
        });
        
        //Get,Set
        Route::URL("A");
        Route::G()->setURLHandler(function($str){ return $str;});
        Route::URL("BB");
        
        Route::G()->defaultURLHandler('/');


        Route::Parameters();
        $callback=function ($obj) {
        };
        Route::G()->addRouteHook($callback, false, true);
        Route::G()->addRouteHook($callback, false, true);
        Route::G()->addRouteHook($callback, true, false);
        
        Route::G()->prepend(new RouteOjbect());
        Route::G()->append(new RouteOjbect());
        
        Route::G()->getRouteCallingPath();
        Route::G()->getRouteCallingClass();
        Route::G()->getRouteCallingMethod();
        Route::G()->setRouteCallingMethod('_');
        Route::G()->stopRunDefaultHandler();

        Route::G()->getURLHandler();

    Route::G()->bindServerData([
        'SCRIPT_FILENAME'=> 'script_filename',
        'DOCUMENT_ROOT'=>'document_root',
    ],false);
            Route::G()->setURLHandler(null);
        $x=Route::URL("");
        Route::URL("?");
        Route::URL("#");
        var_dump($x);


        
        $this->assertTrue(true);
        
        \MyCodeCoverage::G()->end();
        return;
        //MyCodeCoverage::G()->report("code_2");
        //MyCodeCoverage::G()->reportHtml("report_html");
    }
}
class RouteOjbect
{
    public function run()
    {
        var_dump(DATE(DATE_ATOM));
        return false;
    }
}
}
namespace tests_Core_Route
{
class about
{
    public function me()
    {
        //var_dump(DATE(DATE_ATOM));
    }
}
}