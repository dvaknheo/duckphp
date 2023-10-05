<?php
namespace tests\DuckPhp\Core
{

use DuckPhp\Core\Route;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Ext\SuperGlobalContext;

class RouteTest extends \PHPUnit\Framework\TestCase
{
    public function testA()
    {
        \LibCoverage\LibCoverage::Begin(Route::class);
        
        $_SERVER = [
            'DOCUMENT_ROOT'=> __DIR__,
            'SCRIPT_FILENAME'=>__DIR__.'/aa/index.php',
        ];
        Route::G()->reset();
        Route::PathInfo('x/z');
        $t= Route::Url('aaa');
        $t= Route::Res('aaa');
        $z=Route::Route();
        Route::Domain(true);
        Route::Domain(false);

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
        Route::G()->setParameters([]);
        Route::Parameter('a','b');
        Route::Parameter();

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
            $_SERVER=[
                'SCRIPT_FILENAME'=> 'script_filename',
                'DOCUMENT_ROOT'=>'document_root',
                'REQUEST_METHOD'=>'POST',
                'PATH_INFO'=>'/',
            ];
            //Route::G()->reset();
            
            $callback=function () {
                var_dump(DATE(DATE_ATOM));
            };

        });
        
        Route::G()->bind('about/me');
        Route::G()->run();
        
        var_dump("zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz");
        
        Route::G()->bind('Main/index','POST');
        Route::G()->run();
        
        Route::G(MyRoute::G()->init(Route::G()->options));
        Route::G()->bind('Main/index','POST');
        //Route::G()->getCallback(null,'');
        Route::G()->getCallback('tests_Core_Route\\Main','__');
        Route::G()->getCallback('tests_Core_Route\\Main','post');
        Route::G()->getCallback('tests_Core_Route\\Main','post2');
        Route::G()->getCallback('tests_Core_Route\\Main','__missing');
        
        //Route::G()->goByPathInfo('tests_Core_Route\\Main','post');

        echo PHP_EOL;

        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_base_class'=>'~baseController',
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        Route::G(new Route())->init($options);
        Route::G()->bind('NoExists/Mazndex','POST');
        Route::G()->defaultGetRouteCallback('/');
        
        Route::G(new Route())->init($options);
        
        $callback=function () {
            echo "stttttttttttttttttttttttttttttttttttttttttttttttoped";
        };
        Route::G()->addRouteHook($callback, 'outter-inner', true);
        echo "3333333333333333333333333333333333333333333333";
        Route::G()->run();
        
        Route::G(new Route())->init($options);

        
        Route::G()->bind('Main/index','POST')->run();
        Route::G()->bind('main/index','POST')->run();
        
        Route::G(new Route())->init($options);
        Route::G()->bind("good")->run();

        Route::G()->bind('Missed','POST');
        Route::G()->run();
        Route::G()->bind("again",null)->run();
        ////////////
        $options2= $options;
        $options2['controller_method_prefix'] ='action_';
        Route::G(new Route())->init($options2);
        Route::G()->bind("post",'POST')->run();
        
        ////////////
        Route::G()->getControllerNamespacePrefix();
        
        $this->foo2();
        Route::G()->dumpAllRouteHooksAsString();
        
        Route::G(new Route())->init(['controller_enable_slash'=>true,'controller_path_ext'=>'.html']);
        Route::G()->defaultGetRouteCallback('/a.html');
        Route::G()->defaultGetRouteCallback('/a/b/');
        
        $this->doFixPathinfo();
        
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_use_singletonex' => true,
        ];
        Route::G(new Route())->init($options);
        Route::G()->defaultGetRouteCallback('/about/me');
        Route::G()->defaultGetRouteCallback('/about/Me');

        Route::G()->replaceController(\tests_Core_Route\about::class, \tests_Core_Route\about2::class);
        
        Route::G()->defaultGetRouteCallback('/about/me');
        Route::G()->defaultGetRouteCallback('/about/_start');
        Route::G()->defaultGetRouteCallback('/about/NoExists');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_base_class'=>'~baseController',
            'controller_class_postfix'=>'Controller',
        ];
        Route::G(new Route())->init($options);
        Route::G()->defaultGetRouteCallback('/noBase/me');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_base_class'=>'~baseController',
            'controller_class_postfix'=>'Controller',
            'controller_path_prefix'=>'/prefix/',
        ];
        Route::G(new Route())->init($options);        
        Route::G()->defaultGetRouteCallback('/prefix/about/me');
        Route::G()->defaultGetRouteCallback('/about/me');
        Route::G()->defaultGetRouteCallback('/about/_');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_stop_static_method' => true,
        ];
        Route::G(new Route())->init($options);
        Route::G()->defaultGetRouteCallback('/Main/G');
        Route::G()->defaultGetRouteCallback('/Main/MyStatic');

        SuperGlobalContext::DefineSuperGlobalContext();
        
        Route::G()->bind('Main/index','POST')->run();

        Route::G()->options['controller_runtime']=[MyRouteRuntime::class,'G'];
        Route::G()->options['controller_methtod_for_miss']='_ttt';
        Route::G()->options['controller_strict_mode']=false;
        Route::G()->options['controller_resource_prefix']='http://duckphp.github.com/';
        Route::G()->bind('Main/NO','POST')->run();
        echo Route::Res('x.jpg');
        echo Route::Res('http://dvaknheo.git/x.jpg');
        echo Route::Res('https://dvaknheo.git/x.jpg');
        echo Route::Res('//x.jpg');
        echo Route::Res('/x.jpg');
        
        Route::G()->options['controller_resource_prefix']='controller_resource_prefix/';
        Route::G()->bind('Main/NO','POST')->run();
        echo Route::Res('abc.jpg');
        
        $this->doFixedRouteEx();
        //////////////////////////////
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
        ];
        $options['controller_url_prefix'] = 'child/';
        Route::G(new Route())->init($options);
        Route::G()->bind('/date')->run();
        Route::G()->bind('/child/date')->run();
        \LibCoverage\LibCoverage::End();
        return;
    }
    protected function doFixedRouteEx()
    {
        echo "\nFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF\n";
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_hide_boot_class'=>false,
        ];

        Route::G(new MyRoute())->init($options);
        Route::G()->bind('/Main/MyStatic')->run();
        
        
        //echo Route::G()->getRouteError();

        Route::G()->bind('/Main/index')->run();
        Route::G()->route_error_flag=true;
        Route::G()->bind('/Main/index')->run();
        Route::G()->route_error_flag=false;
        
        Route::G()->bind('/main/index')->run();
        echo "\nfffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff\n";
        
    }
    protected function doFixPathinfo()
    {
        // 这里要扩展个 Route 类。
        MyRoute::G(new MyRoute())->init([]);
        $serverData=[
        ];
        $_SERVER=[];

        //MyRoute::G()->reset();
        
        $serverData=[
            'PATH_INFO'=>'abc',
        ];
        $_SERVER=$serverData;

        //MyRoute::G()->reset();
        $serverData=[
            'REQUEST_URI'=>'/',
            'SCRIPT_FILENAME'=>__DIR__ . '/index.php',
            'DOCUMENT_ROOT'=>__DIR__,
        ];
        $_SERVER=$serverData;
        //MyRoute::G()->reset();
        
        $serverData=[
            'REQUEST_URI'=>'/abc/d',
            'SCRIPT_FILENAME'=>__FILE__,
            'DOCUMENT_ROOT'=>__DIR__,
        ];
        
        $_SERVER=$serverData;
        //MyRoute::G()->reset();

        MyRoute::G(new MyRoute())->init(['skip_fix_path_info'=>true]);
        $_SERVER=$serverData;
        //MyRoute::G()->reset();

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
        $_SERVER=[
            'SCRIPT_FILENAME'=> 'x/z/index.php',
            'DOCUMENT_ROOT'=>'x',
        ];
        //Route::G()->reset();
        echo Route::URL("/aaaaaaaa");
        echo PHP_EOL;
        echo Route::URL("A");
        echo PHP_EOL;
        Route::G()->setURLHandler(function($str){ return "[$str]";});
        echo Route::URL("BB");
        echo PHP_EOL;
        
        //
        Route::G()->getURLHandler();
        Route::G(new Route());
        Route::G()->setURLHandler(null);
        $_SERVER = [
            'SCRIPT_FILENAME'=> 'x/index.php',
            'DOCUMENT_ROOT'=>'x',
        ];
        //Route::G()->reset();
        echo "--";
        $_SERVER['SCRIPT_FILENAME']='x/index.php';
        $_SERVER['DOCUMENT_ROOT']='x';
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

        Route::PathInfo();
        Route::PathInfo('xx');

    }
}
class MyRoute extends Route
{
    public $route_error_flag=false;
    public function getCallback($class,$method)
    {
        return null;
        //return $this->getMethodToCall(new $class,$method);
    }
    protected function createControllerObject($full_class)
    {
        $ret = parent::createControllerObject($full_class);
        if($this->route_error_flag){
            $this->route_error="By MyRoute";
        }
        return $ret;
    }
}
class MyRouteRuntime
{
    use \DuckPhp\SingletonEx\SingletonExTrait;
    
}

}
namespace tests_Core_Route
{
class baseController
{
    use \DuckPhp\SingletonEx\SingletonExTrait;
}
class noBaseController
{
    public function me()
    {
        //var_dump(DATE(DATE_ATOM));
    }
}
class about extends baseController
{
    public function me()
    {
        //var_dump(DATE(DATE_ATOM));
    }
}
class about2 extends baseController
{
    public function me()
    {
        echo "about2about2about2about2about2about2about2meeeeeeeeeeee";
        var_dump(DATE(DATE_ATOM));
    }
    public function __Missing()
    {
        var_dump("NOController");
    }
}
class Main  extends baseController
{
    public function index()
    {
        //var_dump(DATE(DATE_ATOM));
    }
    public function date()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function do_post()
    {
        //var_dump(DATE(DATE_ATOM));
    }
    public static function MyStatic()
    {
        echo "MyStatic";
    }
    public function action_do_post()
    {
        echo "action_do_post";
    }
}

}