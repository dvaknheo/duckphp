<?php
namespace tests\DuckPhp\Ext
{
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Ext\SuperGlobalContext;
use DuckPhp\Ext\MiniRoute;

class MiniRouteTest extends \PHPUnit\Framework\TestCase
{
    public function testA()
    {
        \LibCoverage\LibCoverage::Begin(MiniRoute::class);
        
        $_SERVER = [
            'DOCUMENT_ROOT'=> __DIR__,
            'SCRIPT_FILENAME'=>__DIR__.'/aa/index.php',
        ];
        //MiniRoute::G()->reset();
        MiniRoute::PathInfo('x/z');
        $t= MiniRoute::Url('aaa');
        $t= MiniRoute::Res('aaa');
        $z=MiniRoute::Route();
        MiniRoute::Domain(true);
        MiniRoute::Domain(false);

        //Main
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_class_base'=>\tests_Core_Route\baseController::class,
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        //First Run;
        //$flag=MiniRoute::RunQuickly($options);
        //MiniRoute::G()->setParameters([]);
        //MiniRoute::Parameter('a','b');
        //MiniRoute::Parameter();

        //URL
        $this->doUrl();
        //Get,Set
        $this->doGetterSetter();
        $options=[
            'namespace'=>'tests_Core_Route',
            'namespace_controller'=>'',
            'controller_welcome_class_visible'=>false,
        ];
        MiniRoute::G(new MiniRoute());
        
        $_SERVER=[
                'SCRIPT_FILENAME'=> 'script_filename',
                'DOCUMENT_ROOT'=>'document_root',
                'REQUEST_METHOD'=>'POST',
                'PATH_INFO'=>'/',
            ];
        MiniRoute::G()->init($options)->run();
        
        MiniRoute::G()->bind('about/me');
        MiniRoute::G()->run();
        MiniRoute::G()->bind('about/static');
        MiniRoute::G()->run();
        var_dump("zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz");
        
        MiniRoute::G()->bind('Main/index','POST');
        MiniRoute::G()->run();
        
        MiniRoute::G(MyRoute::G()->init(MiniRoute::G()->options));
        MiniRoute::G()->bind('Main/index','POST');
        //MiniRoute::G()->getCallback(null,'');
        MiniRoute::G()->getCallback('tests_Core_Route\\Main','__');
        MiniRoute::G()->getCallback('tests_Core_Route\\Main','post');
        MiniRoute::G()->getCallback('tests_Core_Route\\Main','post2');
        MiniRoute::G()->getCallback('tests_Core_Route\\Main','__missing');
        
        //MiniRoute::G()->goByPathInfo('tests_Core_Route\\Main','post');

        echo PHP_EOL;

        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_class_base'=>'~baseController',
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        MiniRoute::G(new MiniRoute())->init($options);
        MiniRoute::G()->bind('NoExists/Mazndex','POST');
        MiniRoute::G()->defaultGetRouteCallback('/');
        
        MiniRoute::G(new MiniRoute())->init($options);
        
        $callback=function () {
            echo "stttttttttttttttttttttttttttttttttttttttttttttttoped";
        };
        MiniRoute::G()->addRouteHook($callback, 'outter-inner', true);
        echo "3333333333333333333333333333333333333333333333";
        MiniRoute::G()->run();
        
        MiniRoute::G(new MiniRoute())->init($options);

        
        MiniRoute::G()->bind('Main/index','POST')->run();
        MiniRoute::G()->bind('main/index','POST')->run();
        
        MiniRoute::G(new MiniRoute())->init($options);
        MiniRoute::G()->bind("good")->run();

        MiniRoute::G()->bind('Missed','POST');
        MiniRoute::G()->run();
        MiniRoute::G()->bind("again",null)->run();
        ////////////
        $options2= $options;
        $options2['controller_method_prefix'] ='action_';
        MiniRoute::G(new MiniRoute())->init($options2);
        MiniRoute::G()->bind("post",'POST')->run();
        
        ////////////
        MiniRoute::G()->getControllerNamespacePrefix();
        
        $this->foo2();
        MiniRoute::G()->dumpAllRouteHooksAsString();
        
        MiniRoute::G(new MiniRoute())->init(['controller_enable_slash'=>true,'controller_path_ext'=>'.html']);
        MiniRoute::G()->defaultGetRouteCallback('/a.html');
        MiniRoute::G()->defaultGetRouteCallback('/a/b/');
        
        $this->doFixPathinfo();
        
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_use_singletonex' => true,
        ];
        MiniRoute::G(new MiniRoute())->init($options);
        MiniRoute::G()->defaultGetRouteCallback('/about/me');
        MiniRoute::G()->defaultGetRouteCallback('/about/Me');

        MiniRoute::G()->replaceController(\tests_Core_Route\about::class, \tests_Core_Route\about2::class);
        
        MiniRoute::G()->defaultGetRouteCallback('/about/me');
        MiniRoute::G()->defaultGetRouteCallback('/about/_start');
        MiniRoute::G()->defaultGetRouteCallback('/about/NoExists');
        MiniRoute::G()->defaultGetRouteCallback('/about/static');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_class_base'=>'~baseController',
            'controller_class_postfix'=>'Controller',
        ];
        MiniRoute::G(new MiniRoute())->init($options);
        MiniRoute::G()->defaultGetRouteCallback('/noBase/me');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_class_base'=>'~baseController',
            'controller_class_postfix'=>'Controller',
            'controller_path_prefix'=>'/prefix/',
        ];
        MiniRoute::G(new MiniRoute())->init($options);        
        MiniRoute::G()->defaultGetRouteCallback('/prefix/about/me');
        MiniRoute::G()->defaultGetRouteCallback('/about/me');
        MiniRoute::G()->defaultGetRouteCallback('/about/_');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_stop_static_method' => true,
        ];
        MiniRoute::G(new MiniRoute())->init($options);
        MiniRoute::G()->defaultGetRouteCallback('/Main/G');
        MiniRoute::G()->defaultGetRouteCallback('/Main/MyStatic');

        SuperGlobalContext::DefineSuperGlobalContext();
        
        MiniRoute::G()->bind('Main/index','POST')->run();

        MiniRoute::G()->options['controller_runtime']=[MyRouteRuntime::class,'G'];
        MiniRoute::G()->options['controller_methtod_for_miss']='_ttt';
        MiniRoute::G()->options['controller_strict_mode']=false;
        MiniRoute::G()->options['controller_resource_prefix']='http://duckphp.github.com/';
        MiniRoute::G()->bind('Main/NO','POST')->run();
        echo MiniRoute::Res('x.jpg');
        echo MiniRoute::Res('http://dvaknheo.git/x.jpg');
        echo MiniRoute::Res('https://dvaknheo.git/x.jpg');
        echo MiniRoute::Res('//x.jpg');
        echo MiniRoute::Res('/x.jpg');
        
        MiniRoute::G()->options['controller_resource_prefix']='controller_resource_prefix/';
        MiniRoute::G()->bind('Main/NO','POST')->run();
        echo MiniRoute::Res('abc.jpg');
        
        $this->doFixedRouteEx();
        //////////////////////////////
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
        ];
        $options['controller_url_prefix'] = 'child/';
        MiniRoute::G(new MiniRoute())->init($options);
        MiniRoute::G()->bind('/date')->run();
        MiniRoute::G()->bind('/child/date')->run();
        \LibCoverage\LibCoverage::End();
        return;
    }
    protected function doFixedRouteEx()
    {
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route',
            'controller_welcome_class_visible'=>true,
        ];

        MiniRoute::G(new MyRoute())->init($options);
        MiniRoute::G()->bind('/Main/MyStatic')->run();
        
        
        //echo MiniRoute::G()->getRouteError();

        MiniRoute::G()->bind('/Main/index')->run();
        MiniRoute::G()->route_error_flag=true;
        MiniRoute::G()->bind('/Main/index')->run();
        MiniRoute::G()->route_error_flag=false;
        
        MiniRoute::G()->bind('/main/index')->run();
        
    }
    protected function doFixPathinfo()
    {
        // 这里要扩展个 MiniRoute 类。
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
            'controller_class_base'=>\tests_Core_Route\baseController::class,
        ];
        MiniRoute::G(new MiniRoute());
        $flag=MiniRoute::RunQuickly([],function(){
            $my404=function(){ return false;};
            $appended=function () {
                MiniRoute::G()->forceFail();
                return true;
            };
            MiniRoute::G()->addRouteHook($appended, 'append-outter', true);
        });

    }
    protected function hooks()
    {

    }
    protected function doUrl()
    {
        echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzz";
        echo PHP_EOL;
        $_SERVER=[
            'SCRIPT_FILENAME'=> 'x/z/index.php',
            'DOCUMENT_ROOT'=>'x',
        ];
        //MiniRoute::G()->reset();
        echo MiniRoute::URL("/aaaaaaaa");
        echo PHP_EOL;
        echo MiniRoute::URL("A");
        echo PHP_EOL;

        echo PHP_EOL;
        
        //
        MiniRoute::G(new MiniRoute());
        $_SERVER = [
            'SCRIPT_FILENAME'=> 'x/index.php',
            'DOCUMENT_ROOT'=>'x',
        ];
        //MiniRoute::G()->reset();
        echo "--";
        $_SERVER['SCRIPT_FILENAME']='x/index.php';
        $_SERVER['DOCUMENT_ROOT']='x';
        echo MiniRoute::URL("");
        echo PHP_EOL;
        echo MiniRoute::URL("?11");
        echo PHP_EOL;
        echo MiniRoute::URL("#22");
        echo PHP_EOL;
    }
    protected function doGetterSetter()
    {
        MiniRoute::G()->getRouteError();
        MiniRoute::G()->getRouteCallingPath();
        MiniRoute::G()->getRouteCallingClass();
        MiniRoute::G()->getRouteCallingMethod();
        MiniRoute::G()->setRouteCallingMethod('_');

        MiniRoute::PathInfo();
        MiniRoute::PathInfo('xx');

    }
}
class MyRoute extends MiniRoute
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