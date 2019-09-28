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
        //*
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_base_class'=>\tests_Core_Route\baseController::class,
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        //First Run;
        $flag=Route::RunQuickly($options);
                Route::Parameters();

        //URL
        $this->doUrl();
        //Get,Set
        $this->doGetterSetter();
        //*/
        $options=[
            'namespace'=>'tests_Core_Route',
            'namespace_controller'=>'',
            'controller_hide_boot_class'=>true,
        ];
        Route::G(new Route());
        Route::RunQuickly($options,function(){
            Route::G()->bindServerData([
                'SCRIPT_FILENAME'=> 'script_filename',
                'DOCUMENT_ROOT'=>'document_root',
                'REQUEST_METHOD'=>'POST',
                'PATH_INFO'=>'/',
            ]);
            
            $callback=function ($obj) {
            };
            Route::G()->addRouteHook($callback, false, true);
            Route::G()->addRouteHook($callback, false, true);
            Route::G()->addRouteHook($callback, true, false);
        });
        
        Route::G()->bindServerData([
            'PATH_INFO'=>'about/me',
        ]);
        Route::G()->run();
        
        
        Route::G()->bindServerData([
            'PATH_INFO'=>'Main/index',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->defaultRouteHandler();
        
        Route::G()->getCallback(null,'');
        Route::G()->getCallback('tests_Core_Route\\Main','__');
        Route::G()->getCallback('tests_Core_Route\\Main','post');
        Route::G()->getCallback('tests_Core_Route\\Main','post2');
        Route::G()->getCallback('tests_Core_Route\\Main','_missing');
        
        echo Route::G()->error;
        echo PHP_EOL;
        //exit;
        //Route::RunQuickly($options,function(){});
        
        
        Route::G()->prepend(new RouteOjbect());
        Route::G()->append(new RouteOjbect());
        

        $this->assertTrue(true);
        
        \MyCodeCoverage::G()->end();
        return;
        //MyCodeCoverage::G()->report("code_2");
        //MyCodeCoverage::G()->reportHtml("report_html");
    }
    protected function doUrl()
    {
        echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzz";
        echo PHP_EOL;
        Route::G()->bindServerData([
            'SCRIPT_FILENAME'=> 'x/z/index.php',
            'DOCUMENT_ROOT'=>'x',
        ]);
        echo Route::URL("/");
        echo Route::URL("A");
        echo PHP_EOL;
        Route::G()->setURLHandler(function($str){ return "[$str]";});
        echo Route::URL("BB");
        echo PHP_EOL;
        
        //
        Route::G()->getURLHandler();
        
        Route::G()->setURLHandler(null);
        Route::G()->bindServerData([
            'SCRIPT_FILENAME'=> 'x/index.php',
            'DOCUMENT_ROOT'=>'x',
        ]);
        echo "--";
        echo Route::URL("");
        echo PHP_EOL;
        echo Route::URL("?11");
        echo PHP_EOL;
        echo Route::URL("#22");
        echo PHP_EOL;
        

    }
    protected function doGetterSetter()
    {
        Route::G()->getRouteCallingPath();
        Route::G()->getRouteCallingClass();
        Route::G()->getRouteCallingMethod();
        Route::G()->setRouteCallingMethod('_');
        Route::G()->stopRunDefaultHandler();
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
class baseController
{

}
class about extends baseController
{
    public function me()
    {
        //var_dump(DATE(DATE_ATOM));
    }
}
class Main  extends baseController
{
    public function index()
    {
        //var_dump(DATE(DATE_ATOM));
    }
    public function do_post()
    {
        //var_dump(DATE(DATE_ATOM));
    }
}

}