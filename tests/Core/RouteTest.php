<?php
namespace tests\DuckPhp\Core
{

use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;
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
        //Route::_()->reset();
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
            'controller_class_base'=>\tests_Core_Route\baseController::class,
            'controller_class_postfix' => '',
            'controller_method_prefix' => '',
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        //First Run;
        $flag=Route::RunQuickly($options);
        Route::_()->setParameters([]);
        Route::Parameter('a','b');
        Route::Parameter();

        //URL
        $this->doUrl();
        //Get,Set
        $this->doGetterSetter();
        $options=[
            'namespace'=>'tests_Core_Route',
            'namespace_controller'=>'',
            'controller_welcome_class_visible'=>false,
            'controller_class_postfix' => '',
            'controller_method_prefix' => '',
        ];
        Route::_(new Route());
        Route::RunQuickly($options,function(){
            $_SERVER=[
                'SCRIPT_FILENAME'=> 'script_filename',
                'DOCUMENT_ROOT'=>'document_root',
                'REQUEST_METHOD'=>'POST',
                'PATH_INFO'=>'/',
                    'controller_class_postfix' => '',
                    'controller_method_prefix' => '',
            ];
            //Route::_()->reset();
            
            $callback=function () {
                var_dump(DATE(DATE_ATOM));
            };

        });
        
        Route::_()->bind('about/me');
        Route::_()->run();
        Route::_()->bind('about/static');
        Route::_()->run();
        var_dump("zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz");
        
        Route::_()->bind('Main/index','POST');
        Route::_()->run();
        
        Route::_(MyRoute::_()->init(Route::_()->options));
        Route::_()->bind('Main/index','POST');
        //Route::_()->getCallback(null,'');
        Route::_()->getCallback('tests_Core_Route\\Main','__');
        Route::_()->getCallback('tests_Core_Route\\Main','post');
        Route::_()->getCallback('tests_Core_Route\\Main','post2');
        Route::_()->getCallback('tests_Core_Route\\Main','__missing');
        
        //Route::_()->goByPathInfo('tests_Core_Route\\Main','post');

        echo PHP_EOL;

        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_class_base'=>'~baseController',
            'controller_class_postfix' => '',
            'controller_method_prefix' => '',
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        Route::_(new Route())->init($options);
        Route::_()->bind('NoExists/Mazndex','POST');
        Route::_()->defaultGetRouteCallback('/');
        
        Route::_(new Route())->init($options);
        
        $callback=function () {
            echo "stttttttttttttttttttttttttttttttttttttttttttttttoped";
        };
        Route::_()->addRouteHook($callback, 'outter-inner', true);
        echo "3333333333333333333333333333333333333333333333";
        Route::_()->run();
        
        Route::_(new Route())->init($options);

        
        Route::_()->bind('Main/index','POST')->run();
        Route::_()->bind('main/index','POST')->run();
        
        Route::_(new Route())->init($options);
        Route::_()->bind("good")->run();

        Route::_()->bind('Missed','POST');
        Route::_()->run();
        Route::_()->bind("again",null)->run();
        ////////////
        $options2= $options;
        $options2['controller_method_prefix'] ='action_';
        Route::_(new Route())->init($options2);
        Route::_()->bind("post",'POST')->run();
        
        ////////////
        Route::_()->getControllerNamespacePrefix();
        
        $this->foo2();
        Route::_()->dumpAllRouteHooksAsString();
        
        Route::_(new Route())->init(['controller_enable_slash'=>true,'controller_path_ext'=>'.html']);
        Route::_()->defaultGetRouteCallback('/a.html');
        Route::_()->defaultGetRouteCallback('/a/b/');
        
        $this->doFixPathinfo();
        
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            
            'controller_class_postfix' => '',
            'controller_method_prefix' => '',
            
        ];
        Route::_(new Route())->init($options);
        Route::_()->defaultGetRouteCallback('/about/me');
        Route::_()->defaultGetRouteCallback('/about/Me');

        Route::_()->replaceController(\tests_Core_Route\about::class, \tests_Core_Route\about2::class);
        
        Route::_()->defaultGetRouteCallback('/about/me');
        Route::_()->defaultGetRouteCallback('/about/_start');
        Route::_()->defaultGetRouteCallback('/about/NoExists');
        Route::_()->defaultGetRouteCallback('/about/static');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_class_base'=>'~baseController',
            'controller_class_postfix'=>'Controller',
            'controller_method_prefix' => '',

        ];
        Route::_(new Route())->init($options);
        Route::_()->defaultGetRouteCallback('/noBase/me');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_class_base'=>'~baseController',
            'controller_class_postfix'=>'Controller',
            'controller_method_prefix' => '',
            'controller_path_prefix'=>'/prefix/',
        ];
        Route::_(new Route())->init($options);        
        Route::_()->defaultGetRouteCallback('/prefix/about/me');
        Route::_()->defaultGetRouteCallback('/about/me');
        Route::_()->defaultGetRouteCallback('/about/_');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            
            'controller_class_postfix' => '',
            'controller_method_prefix' => '',
            
        ];
        Route::_(new Route())->init($options);
        Route::_()->defaultGetRouteCallback('/Main/G');
        Route::_()->defaultGetRouteCallback('/Main/MyStatic');

        ///////////////////
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_class_adjust'=>'uc_method;uc_class;uc_full_class;'
            
        ];
        Route::_(new Route())->init($options);
        Route::_()->defaultGetRouteCallback('for_class_adjust/b/cd');
        //Route::_()->defaultGetRouteCallback('a/b/cd');
        ///////////////////


        \DuckPhp\Core\SuperGlobal::DefineSuperGlobalContext();
        
        Route::_()->bind('Main/index','POST')->run();

        Route::_()->options['controller_runtime']=[MyRouteRuntime::class,'G'];
        Route::_()->options['controller_methtod_for_miss']='_ttt';
        Route::_()->options['controller_strict_mode']=false;
        Route::_()->options['controller_resource_prefix']='http://duckphp.github.com/';
        Route::_()->bind('Main/NO','POST')->run();
        echo Route::Res('x.jpg');
        echo Route::Res('http://dvaknheo.git/x.jpg');
        echo Route::Res('https://dvaknheo.git/x.jpg');
        echo Route::Res('//x.jpg');
        echo Route::Res('/x.jpg');
        
        Route::_()->options['controller_resource_prefix']='controller_resource_prefix/';
        Route::_()->bind('Main/NO','POST')->run();
        echo Route::Res('abc.jpg');
        
        $this->doFixedRouteEx();
        //////////////////////////////
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            
            'controller_class_postfix' => '',
            'controller_method_prefix' => '',
        ];
        $options['controller_url_prefix'] = 'child/';
        Route::_(new Route())->init($options);
        Route::_()->bind('/date')->run();
        Route::_()->bind('/child/date')->run();
        
        \LibCoverage\LibCoverage::End();
        return;
    }
    protected function doFixedRouteEx()
    {
        echo "\nFFFFFFFFFFFFFFFFFFFFFFFFFFFF\n";
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_welcome_class_visible'=>true,
        ];

        Route::_(new MyRoute())->init($options);
        Route::_()->bind('/Main/MyStatic')->run();
        
        
        //echo Route::_()->getRouteError();

        Route::_()->bind('/Main/index')->run();
        Route::_()->route_error_flag=true;
        Route::_()->bind('/Main/index')->run();
        Route::_()->route_error_flag=false;
        
        Route::_()->bind('/main/index')->run();
        echo "\nffffffffffffffffffffffffffffffffff\n";
        
    }
    protected function doFixPathinfo()
    {
        // 这里要扩展个 Route 类。
        MyRoute::_(new MyRoute())->init([]);
        $serverData=[
        ];
        $_SERVER=[];

        //MyRoute::_()->reset();
        
        $serverData=[
            'PATH_INFO'=>'abc',
        ];
        $_SERVER=$serverData;

        //MyRoute::_()->reset();
        $serverData=[
            'REQUEST_URI'=>'/',
            'SCRIPT_FILENAME'=>__DIR__ . '/index.php',
            'DOCUMENT_ROOT'=>__DIR__,
        ];
        $_SERVER=$serverData;
        //MyRoute::_()->reset();
        
        $serverData=[
            'REQUEST_URI'=>'/abc/d',
            'SCRIPT_FILENAME'=>__FILE__,
            'DOCUMENT_ROOT'=>__DIR__,
        ];
        
        $_SERVER=$serverData;
        //MyRoute::_()->reset();

        MyRoute::_(new MyRoute())->init(['skip_fix_path_info'=>true]);
        $_SERVER=$serverData;
        //MyRoute::_()->reset();

    }
    protected function foo2()
    {
       $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_class_base'=>\tests_Core_Route\baseController::class,
        ];
        Route::_(new Route());
        $flag=Route::RunQuickly([],function(){
            $my404=function(){ return false;};
            $appended=function () {
                Route::_()->forceFail();
                return true;
            };
            Route::_()->addRouteHook($appended, 'append-outter', true);
        });

        var_dump("zzzzzzzzzzzzzzzzzzzzzzzzzzz",$flag);
    }
    protected function hooks()
    {
        //First Main();
        Route::RunQuickly([]);
        
        //Prepend, true
        Route::_(new Route());
        Route::RunQuickly([],function(){
            $prepended=function () {
                var_dump(DATE(DATE_ATOM));
                return true;
            };
            Route::_()->addRouteHook($prepended, 'prepend-outter', true);
            Route::_()->addRouteHook($prepended, 'prepend-outter', true);
        });
        //prepend,false
        Route::_(new Route());
        Route::RunQuickly([],function(){
            $prepended=function () {
                var_dump(DATE(DATE_ATOM));
                Route::_()->defaulToggleRouteCallback(false);
                return false;
            };
            $prepended2=function () { var_dump('prepended2!');};
            Route::_()->addRouteHook($prepended, 'prepend-inner', true);
            Route::_()->addRouteHook($prepended, 'prepend-outter', false);
        });
        // append true.
        
        Route::_(new Route());
        Route::RunQuickly([],function(){
            $my404=function(){ return false;};
            $appended=function () {
                var_dump(DATE(DATE_ATOM));
                return true;
            };
            Route::_()->addRouteHook($appended, 'append-inner', true);
            Route::_()->addRouteHook($appended, 'append-outter', true);
        });
    }
    protected function doUrl()
    {
    
        // remark: realpath!
        echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzz";
        echo PHP_EOL;
        $_SERVER=[
            'SCRIPT_FILENAME'=> 'x/z/index.php',
            'DOCUMENT_ROOT'=>'x',
        ];
        //Route::_()->reset();
        echo Route::URL("/aaaaaaaa");
        echo PHP_EOL;
        echo Route::URL("A");
        echo PHP_EOL;
        Route::_()->setURLHandler(function($str){ return "[$str]";});
        echo Route::URL("BB");
        echo PHP_EOL;
        
        //
        Route::_()->getURLHandler();
        Route::_(new Route());
        Route::_()->setURLHandler(null);
        $_SERVER = [
            'SCRIPT_FILENAME'=> 'x/index.php',
            'DOCUMENT_ROOT'=>'x',
        ];
        //Route::_()->reset();
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
        Route::_()->getRouteError();
        Route::_()->getRouteCallingPath();
        Route::_()->getRouteCallingClass();
        Route::_()->getRouteCallingMethod();
        Route::_()->setRouteCallingMethod('_');

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
use  SingletonExTrait;
    
}

}
namespace tests_Core_Route
{
use DuckPhp\Core\SingletonTrait as SingletonExTrait;

class baseController
{
    use SingletonExTrait;
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
    public static function static()
    {
        //
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
class Main extends baseController
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