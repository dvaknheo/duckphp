<?php
namespace tests\DuckPhp\Core
{

use DuckPhp\Core\Route;

class RouteTest extends \PHPUnit\Framework\TestCase
{
    public function testA()
    {
        \MyCodeCoverage::G()->begin(Route::class);
        
        Route::G()->document_root=__DIR__;
        Route::G()->setPathInfo('x/z');
        Route::G()->script_filename=__DIR__.'/aa/index.php';
        $t= Route::URL('aaa');

        
        $this->hooks();
        //Main
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_base_class'=>\tests_Core_Route\baseController::class,
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        //First Run;
        $flag=Route::RunQuickly($options);
        Route::G()->getParameters();
        Route::G()->setParameters([]);
        Route::Parameter('a','b');

        //URL
        $this->doUrl();
        //Get,Set
        $this->doGetterSetter();
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
            
            $callback=function () {
                var_dump(DATE(DATE_ATOM));
            };

        });
        
        Route::G()->bindServerData([
            'PATH_INFO'=>'about/me',
        ]);
        Route::G()->run();
        
        var_dump("zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz");
        
        Route::G()->bindServerData([
            'PATH_INFO'=>'Main/index',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        
        Route::G(MyRoute::G()->init(Route::G()->options));
        Route::G()->bindServerData([
            'PATH_INFO'=>'Main/index',
            'REQUEST_METHOD'=>'POST',
        ]);
        //Route::G()->getCallback(null,'');
        Route::G()->getCallback('tests_Core_Route\\Main','__');
        Route::G()->getCallback('tests_Core_Route\\Main','post');
        Route::G()->getCallback('tests_Core_Route\\Main','post2');
        Route::G()->getCallback('tests_Core_Route\\Main','_missing');
        
        //Route::G()->goByPathInfo('tests_Core_Route\\Main','post');

        echo PHP_EOL;

        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_base_class'=>'~baseController',
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        Route::G(new Route())->init($options);
        Route::G()->bindServerData([
            'PATH_INFO'=>'NoExists/Mazndex',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->defaultGetRouteCallback('/');
        
        Route::G(new Route())->init($options);
        
        $callback=function () {
            echo "stttttttttttttttttttttttttttttttttttttttttttttttoped";
        };
        Route::G()->addRouteHook($callback, 'outter-inner', true);
        echo "3333333333333333333333333333333333333333333333";
        Route::G()->run();
        
        Route::G(new Route())->init($options);

        
        Route::G()->bindServerData([
            'PATH_INFO'=>'Main/index',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        
        Route::G(new Route())->init($options);
        Route::G()->bind("good")->run();

        Route::G()->bindServerData([
            'PATH_INFO'=>'Missed',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        Route::G()->bind("again",null)->run();
        Route::G()->getNamespacePrefix();
        
        $this->foo2();
        Route::G()->dumpAllRouteHooksAsString();
        
        Route::G(new Route())->init(['controller_enable_slash'=>true,'controller_path_ext'=>'.html']);
        Route::G()->defaultGetRouteCallback('/a.html');
        Route::G()->defaultGetRouteCallback('/a/b/');
        \MyCodeCoverage::G()->end();
        return;
    }
    protected function foo2()
    {
       $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_base_class'=>\tests_Core_Route\baseController::class,
        ];
        Route::G(new Route());
        $flag=Route::RunQuickly([],function(){
            $my404=function(){ return false;};
            $appended=function () {
                Route::G()->forceFail();
                return true;
            };
            Route::G()->addRouteHook($appended, 'append-outter', true);
        });
        var_dump("zzzzzzzzzzzzzzzzzzzzzzzzzzz",$flag);
    }
    protected function hooks()
    {
        //First Main();
        Route::RunQuickly([]);
        
        //Prepend, true
        Route::G(new Route());
        Route::RunQuickly([],function(){
            $prepended=function () {
                var_dump(DATE(DATE_ATOM));
                return true;
            };
            Route::G()->addRouteHook($prepended, 'prepend-outter', true);
            Route::G()->addRouteHook($prepended, 'prepend-outter', true);
        });
        //prepend,false
        Route::G(new Route());
        Route::RunQuickly([],function(){
            $prepended=function () {
                var_dump(DATE(DATE_ATOM));
                Route::G()->defaulToggleRouteCallback(false);
                return false;
            };
            $prepended2=function () { var_dump('prepended2!');};
            Route::G()->addRouteHook($prepended, 'prepend-inner', true);
            Route::G()->addRouteHook($prepended, 'prepend-outter', false);
        });
        // append true.
        
        Route::G(new Route());
        Route::RunQuickly([],function(){
            $my404=function(){ return false;};
            $appended=function () {
                var_dump(DATE(DATE_ATOM));
                return true;
            };
            Route::G()->add404RouteHook($my404);
            Route::G()->addRouteHook($appended, 'append-inner', true);
            Route::G()->addRouteHook($appended, 'append-outter', true);
        });
    }
    protected function doUrl()
    {
        echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzz";
        echo PHP_EOL;
        Route::G()->bindServerData([
            'SCRIPT_FILENAME'=> 'x/z/index.php',
            'DOCUMENT_ROOT'=>'x',
        ]);
        echo Route::URL("/aaaaaaaa");
        echo PHP_EOL;
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
        Route::G()->getRouteError();
        Route::G()->getRouteCallingPath();
        Route::G()->getRouteCallingClass();
        Route::G()->getRouteCallingMethod();
        Route::G()->setRouteCallingMethod('_');

        Route::G()->getPathInfo();
        Route::G()->setPathInfo('xx');

    }
}
class MyRoute extends Route
{
    public function getCallback($class,$method)
    {
        return $this->getMethodToCall(new $class,$method);
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