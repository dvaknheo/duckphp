<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\Route;

class RouteTest extends \PHPUnit\Framework\TestCase
{
    public function testA()
    {
        \MyCodeCoverage::G()->begin(Route::class);
        
        //Main
        $options=[
            'namespace_controller'=>'\\',
        ];
        $_SERVER['argv']=[ __FILE__ ,'/' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        Route::RunQuickly($options);

        $flag=Route::RunQuickly($options, function () {
            //Route::G()->prepend(function(){return false;});
            //Route::G()->append(function(){return false;});
        });
        
        //Get,Set
        Route::URL("A");
        Route::Parameters();
        $callback=function ($obj) {
        };
        Route::G()->addRouteHook($callback, false, true);
        Route::G()->addRouteHook($callback, false, true);
        
        Route::G()->getRouteCallingPath();
        Route::G()->getRouteCallingClass();
        Route::G()->getRouteCallingMethod();
        Route::G()->setRouteCallingMethod('_');
        Route::G()->stopRunDefaultHandler();
        Route::G()->setURLHandler(function () {
        });
        Route::G()->getURLHandler();

        
        $this->assertTrue(true);
        
        \MyCodeCoverage::G()->end();
        return;
        //MyCodeCoverage::G()->report("code_2");
        //MyCodeCoverage::G()->reportHtml("report_html");
    }
}
