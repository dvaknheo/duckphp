<?php
namespace tests\DuckPhp\Ext
{
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Ext\SuperGlobalContext;
use DuckPhp\Ext\MiniRoute;

class MiniRouteTest extends \PHPUnit\Framework\TestCase
{
    public function bind($path_info, $request_method = 'GET')
    {
        $path_info = parse_url($path_info, PHP_URL_PATH);
        $this->setPathInfo($path_info);
        if (isset($request_method)) {
            $_SERVER['REQUEST_METHOD'] = $request_method;
            if (defined('__SUPERGLOBAL_CONTEXT')) {
                (__SUPERGLOBAL_CONTEXT)()->_SERVER = $_SERVER;
            }
        }
        return $this;
    }
    protected function setPathInfo($path_info)
    {
        // TODO protected
        $_SERVER['PATH_INFO'] = $path_info;
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            (__SUPERGLOBAL_CONTEXT)()->_SERVER = $_SERVER;
        }
    }
    
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(MiniRoute::class);
        
        $_SERVER = [
            'DOCUMENT_ROOT'=> __DIR__,
            'SCRIPT_FILENAME'=>__DIR__.'/aa/index.php',
        ];
        //MiniRoute::_()->reset();
        MiniRoute::PathInfo('x/z');
        $t= MiniRoute::Url('aaa');
        $t= MiniRoute::Res('aaa');
        $z=MiniRoute::Route();
        MiniRoute::Domain(true);
        MiniRoute::Domain(false);

        //Main
        $options=[
            'namespace_controller'=>'\\tests_Core_Route2',
            'controller_class_base'=>\tests_Core_Route2\baseController::class,
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        //First Run;
        //$flag=MiniRoute::RunQuickly($options);
        //MiniRoute::_()->setParameters([]);
        //MiniRoute::Parameter('a','b');
        //MiniRoute::Parameter();

        //URL
        $this->doUrl();
        //Get,Set
        $this->doGetterSetter();
        $options=[
            'namespace'=>'tests_Core_Route2',
            'namespace_controller'=>'',
            'controller_welcome_class_visible'=>false,
        ];
        MiniRoute::_(new MiniRoute());
        
        $_SERVER=[
                'SCRIPT_FILENAME'=> 'script_filename',
                'DOCUMENT_ROOT'=>'document_root',
                'REQUEST_METHOD'=>'POST',
                'PATH_INFO'=>'/',
            ];
        MiniRoute::_()->init($options)->run();
        
        $this->bind('about/me');
        MiniRoute::_()->run();
        $this->bind('about/static');
        MiniRoute::_()->run();
        var_dump("zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz");
        
        $this->bind('Main/index','POST');
        MiniRoute::_()->run();
        
        MiniRoute::_(MyRoute::_()->init(MiniRoute::_()->options));
        $this->bind('Main/index','POST');
        //MiniRoute::_()->getCallback(null,'');
        MiniRoute::_()->getCallback('tests_Core_Route2\\Main','__');
        MiniRoute::_()->getCallback('tests_Core_Route2\\Main','post');
        MiniRoute::_()->getCallback('tests_Core_Route2\\Main','post2');
        MiniRoute::_()->getCallback('tests_Core_Route2\\Main','__missing');
        
        //MiniRoute::_()->goByPathInfo('tests_Core_Route\\Main','post');

        echo PHP_EOL;

        $options=[
            'namespace_controller'=>'\\tests_Core_Route2',
            'controller_class_base'=>'~baseController',
        ];
        $_SERVER['argv']=[ __FILE__ ,'about/me' ];
        $_SERVER['argc']=count($_SERVER['argv']);
        
        MiniRoute::_(new MiniRoute())->init($options);
        $this->bind('NoExists/Mazndex','POST');
        MiniRoute::_()->defaultGetRouteCallback('/');
        

        
        MiniRoute::_(new MiniRoute())->init($options);

        $this->bind('Main/index','POST');
        MiniRoute::_()->run();
        $this->bind('main/index','POST');
        MiniRoute::_()->run();
        MiniRoute::_(new MiniRoute())->init($options);
        $this->bind("good");
        MiniRoute::_()->run();
        
        $this->bind('Missed','POST');
        MiniRoute::_()->run();
        $this->bind("again",null);
        MiniRoute::_()->run();

        ////////////
        $options2= $options;
        $options2['controller_method_prefix'] ='action_';
        MiniRoute::_(new MiniRoute())->init($options2);
        $this->bind("post",'POST');
        MiniRoute::_()->run();

        ////////////
        MiniRoute::_()->getControllerNamespacePrefix();
        
        
        MiniRoute::_(new MiniRoute())->init(['controller_enable_slash'=>true,'controller_path_ext'=>'.html']);
        MiniRoute::_()->defaultGetRouteCallback('/a.html');
        MiniRoute::_()->defaultGetRouteCallback('/a/b/');
        
        $this->doFixPathinfo();
        
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route2',
            'controller_use_singletonex' => true,
        ];
        MiniRoute::_(new MiniRoute())->init($options);
        MiniRoute::_()->defaultGetRouteCallback('/about/me');
        MiniRoute::_()->defaultGetRouteCallback('/about/Me');

        MiniRoute::_()->replaceController(\tests_Core_Route2\about::class, \tests_Core_Route2\about2::class);
        
        MiniRoute::_()->defaultGetRouteCallback('/about/me');
        MiniRoute::_()->defaultGetRouteCallback('/about/_start');
        MiniRoute::_()->defaultGetRouteCallback('/about/NoExists');
        MiniRoute::_()->defaultGetRouteCallback('/about/static');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route2',
            'controller_class_base'=>'~baseController',
            'controller_class_postfix'=>'Controller',
        ];
        MiniRoute::_(new MiniRoute())->init($options);
        MiniRoute::_()->defaultGetRouteCallback('/noBase/me');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route2',
            'controller_class_base'=>'~baseController',
            'controller_class_postfix'=>'Controller',
            'controller_path_prefix'=>'/prefix/',
        ];
        MiniRoute::_(new MiniRoute())->init($options);        
        MiniRoute::_()->defaultGetRouteCallback('/prefix/about/me');
        MiniRoute::_()->defaultGetRouteCallback('/about/me');
        MiniRoute::_()->defaultGetRouteCallback('/about/_');
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route2',
            'controller_stop_static_method' => true,
        ];
        MiniRoute::_(new MiniRoute())->init($options);
        MiniRoute::_()->defaultGetRouteCallback('/Main/G');
        MiniRoute::_()->defaultGetRouteCallback('/Main/MyStatic');

        \DuckPhp\Core\SuperGlobal::DefineSuperGlobalContext();
        
        $this->bind('Main/index','POST');
        MiniRoute::_()->run();

        MiniRoute::_()->options['controller_runtime']=[MyRouteRuntime::class,'G'];
        MiniRoute::_()->options['controller_methtod_for_miss']='_ttt';
        MiniRoute::_()->options['controller_strict_mode']=false;
        MiniRoute::_()->options['controller_resource_prefix']='http://duckphp.github.com/';
        $this->bind('Main/NO','POST');
        MiniRoute::_()->run();
        echo MiniRoute::Res('x.jpg');
        echo MiniRoute::Res('http://dvaknheo.git/x.jpg');
        echo MiniRoute::Res('https://dvaknheo.git/x.jpg');
        echo MiniRoute::Res('//x.jpg');
        echo MiniRoute::Res('/x.jpg');
        
        MiniRoute::_()->options['controller_resource_prefix']='controller_resource_prefix/';
        $this->bind('Main/NO','POST');
        MiniRoute::_()->run();
        echo MiniRoute::Res('abc.jpg');
        
        $this->doFixedRouteEx();
        //////////////////////////////
        $options=[
            'namespace_controller'=>'\\tests_Core_Route2',
        ];
        $options['controller_url_prefix'] = 'child/';
        MiniRoute::_(new MiniRoute())->init($options);
        $this->bind('/date');
        MiniRoute::_()->run();
        $this->bind('/child/date');
        MiniRoute::_()->run();
        MiniRoute::PathInfo('/z');
        \LibCoverage\LibCoverage::End();
        return;
    }
    protected function doFixedRouteEx()
    {
        
        $options=[
            'namespace_controller'=>'\\tests_Core_Route2',
            'controller_welcome_class_visible'=>true,
        ];

        MiniRoute::_(new MyRoute())->init($options);
        $this->bind('/Main/MyStatic');
        MiniRoute::_()->run();
        
        
        //echo MiniRoute::_()->getRouteError();

        $this->bind('/Main/index');
        MiniRoute::_()->run();
        MiniRoute::_()->route_error_flag=true;
        $this->bind('/Main/index');
        MiniRoute::_()->run();
        MiniRoute::_()->route_error_flag=false;
        
        $this->bind('/main/index');
        MiniRoute::_()->run();
        
    }
    protected function doFixPathinfo()
    {
        // 这里要扩展个 MiniRoute 类。
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
        //MiniRoute::_()->reset();
        echo MiniRoute::URL("/aaaaaaaa");
        echo PHP_EOL;
        echo MiniRoute::URL("A");
        echo PHP_EOL;

        echo PHP_EOL;
        
        //
        MiniRoute::_(new MiniRoute());
        $_SERVER = [
            'SCRIPT_FILENAME'=> 'x/index.php',
            'DOCUMENT_ROOT'=>'x',
        ];
        //MiniRoute::_()->reset();
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
        MiniRoute::_()->getRouteError();
        MiniRoute::_()->getRouteCallingPath();
        MiniRoute::_()->getRouteCallingClass();
        MiniRoute::_()->getRouteCallingMethod();
        MiniRoute::_()->setRouteCallingMethod('_');

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
namespace tests_Core_Route2
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