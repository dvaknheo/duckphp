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
        
        //Main
        $options=[
            'namespace_controller'=>'\\',
        ];
        $_SERVER['argv']=[ __FILE__ ,'/' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        Route::RunQuickly($options);

        $flag=Route::RunQuickly($options,function(){
            //Route::G()->prepend(function(){return false;});
            //Route::G()->append(function(){return false;});
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

        
        $this->assertTrue($flag);
        
        MyCodeCoverage::G()->end();
        return;
        //MyCodeCoverage::G()->report("code_2");
        //MyCodeCoverage::G()->reportHtml("report_html");
    }
    
    public function testMain2()
    {
        MyCodeCoverage::G()->begin(\DNMVCS\Core\App::class);
        /////
        $options=[];
        $options['skip_setting_file']=true;
        $options['error_exception']=null;
        $options['error_500']=null;
        $options['error_404']=null;
        \DNMVCS\Core\App::RunQuickly($options);//("ZZZZ");
         $this->assertTrue(true);
        MyCodeCoverage::G()->end();
    }
}
