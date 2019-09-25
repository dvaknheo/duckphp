<?php
use DNMVCS\Core\Route;
class Main
{
    public function index()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function i()
    {
        phpinfo();
    }
}


class coreApp extends \PHPUnit\Framework\TestCase
{

    public function testMain()
    {
        MyCodeCoverage::G()->begin(Route::class);
        //var_dump($_SERVER);
        
        //Main
        $options=[
            'namespace_controller'=>'\\',
        ];
        $_SERVER['argv']=[ __FILE__ ,'/' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        Route::RunQuickly($options);

        $flag=Route::RunQuickly($options,function(){
            Route::G()->prepend(function(){return false;});
            Route::G()->append(function(){return false;});
        });
        //Get,Set
        Route::URL("A");
        Route::Parameters();
        $callback=function($obj){};
        Route::G()->addRouteHook($callback,false,true);
        Route::G()->addRouteHook($callback,false,true);
        
        Route::G()->getRouteCallingPath();
        Route::G()->getRouteCallingClass();
        Route::G()->getRouteCallingMethod();
        Route::G()->setRouteCallingMethod('_');
        Route::G()->stopRunDefaultHandler();
        Route::G()->setURLHandler(function(){});
        Route::G()->getURLHandler();
        // again

        
        $this->assertTrue($flag);
        
        MyCodeCoverage::G()->end();
        MyCodeCoverage::G()->report("code_2");
        MyCodeCoverage::G()->reportHtml("report_html");
    }
}
