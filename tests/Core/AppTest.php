<?php
namespace tests\DuckPhp\Core{

use DuckPhp\Core\App;
use DuckPhp\DuckPhp;
use DuckPhp\Component\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Component\Pager;
use DuckPhp\Ext\SuperGlobalContext;

class AppRoute extends Route
{
    protected $welcome_class='AppMain';
}
class AppTest extends \PHPUnit\Framework\TestCase
{
    protected $LibCoverage;
    public function testAll()
    {
        $ref = new \ReflectionClass(App::class);
        $path = $ref->getFileName();
        
        $extFile=dirname($path).'/Functions.php';
        \LibCoverage\LibCoverage::G()->addExtFile($extFile);
        
        \LibCoverage\LibCoverage::Begin(App::class);
        $this->LibCoverage = \LibCoverage\LibCoverage::G();
        
/*
        $path_app=$this->LibCoverage->getClassTestPath(App::class);
        $options=[
            'path' => $path_app,
            'is_debug'=>false,
        ];
        AppTestApp::RunQuickly($options);
        
        AppTestApp::G()->options['error_404']='_sys/error-404';
        AppTestApp::On404();
//\LibCoverage\LibCoverage::G($this->LibCoverage);     \LibCoverage\LibCoverage::End();       return;    
//*/
        Route::G(AppRoute::G());
        $_SESSION=[];
        
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(App::class);
        $path_config=\LibCoverage\LibCoverage::G()->getClassTestPath(Configer::class);
        
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'path_view' => $path_app.'view/',
            'namespace' => __NAMESPACE__,
            'platform' => 'ForTests',
            'is_debug' => true,
            'use_flag_by_setting' => false,
            'error_exception' => NULL,
            'error_500' => NULL,
            'error_404' => NULL,
            'error_debug' => NULL,
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>AppTestApp::class,

        ];
       

        $options['ext']=[
            'noclass'=>true,
            AppTestObject::class=>false,
            AppTestObjectA::class=>true,
            AppTestObjectB::class=>['aa'=>'22'],
        ];

        App::G(new App());
        App::RunQuickly($options,function(){
        App::SessionSet('zz','abc');
        App::SessionGet('zz');
        
        \DuckPhp\Core\SuperGlobal::DefineSuperGlobalContext();
        App::SessionUnset('zz');

            App::G()->addBeforeShowHandler(function(){ echo "beforeShowHandlers";});
            App::G()->addBeforeShowHandler("testsssssssssss");
            App::G()->removeBeforeShowHandler("testsssssssssss");

            $value = $cache[$key]; // trigger notice
            App::G()->options['error_debug']='_sys/error-debug';
            $value = $cache[$key]; 
            
            App::G()->options['error_debug']=function($data){var_dump($data);return;};
            $value = $cache[$key]; 
            
            App::G()->options['is_debug']=false;
            $value = $cache[$key]; 
            App::G()->options['is_debug']=true;

        });
        App::G()->getProjectPath();
        App::G()->getRuntimePath();
        App::Route()->bind('/NOOOOOOOOOOOOOOO');  // 这两句居然有区别 ,TODO ，分析之
        
        App::G()->options['error_404']=function(){
            echo "noooo 404  ooooooooo\n";
        };
        
        App::G()->run();
echo "-------------------------------------\n";
        App::Route()->bind('/exception');
        App::G()->run();
        
        App::G()->options['error_404']=function(){
            echo "zzzzzo 404  zzzzzzzzzzzz\n";
        };
        App::Route()->bind('/Base/index');
        try{
                App::G()->system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);
        
        App::G()->run();
        }catch(\Throwable $ex){
            echo "failed".$ex;
        }
        try{
            App::G()->options['skip_exception_check']=true;
            Route::G()->bind('/exception');
            
            App::G()->run();
        }catch(\Throwable $ex){
            echo $ex->getMessage();
        }
        App::G()->options['skip_exception_check']=false;
//\LibCoverage\LibCoverage::End(App::class);
//$this->assertTrue(true);
//return;
        //Route::G()->bind('')
        //////////////////////////////////////////////////
        
        $app=new App();
        $options=['plugin_mode'=>true];
        try{
            $app->init($options,$app);
        }catch(\Exception $ex){
            echo $ex->getMessage();
        }
        
        
        //App::G()->clear();
        ///////////////////////////
        $options=[
            // 'no this path' => $path_app,
            'path_config' => $path_app,
            'override_class'=>'\\'.App::class,
            'path_view' => $path_app.'view/',
            'is_debug' => true,
        ];
        View::G(new View());
        Configer::G(new Configer());
        App::G(new App())->init($options);
        

        $this->doException();

        $this->do404();
        $this->doHelper();
        $this->doGlue();
        $this->do_Core_Redirect();
        $this->doSystemWrapper();
        
        
        $this->do_Core_Component();
        
try{
App::Pager(Pager::G());
}catch(\Exception $ex){}


        try{
            App::Event();
        }catch(\Throwable $ex){
        }
//*

        
        App::SESSION();
        App::FILES();
        
        App::Route();
        
        App::SessionSet('x',DATE('Y,M,d'));
        SuperGlobal::DefineSuperGlobalContext();
        App::SessionSet('x',DATE('Y,M,d'));

        App::CookieSet('x',DATE('Y,M,d'));
        App::CookieGet('x');
//*/


        
        $old_class = AppTestObjectA::class;
        $new_class = AppTestObjectB::class;
        App::replaceController($old_class, $new_class);
        App::G()->version();
        
        App::IsAjax();
        $path_app=$this->LibCoverage->getClassTestPath(App::class);
        $options=[
            'path' => $path_app,
            'is_debug'=>false,
        ];
        AppTestApp::RunQuickly($options);
        
        AppTestApp::G()->options['error_404']='_sys/error-404';
        AppTestApp::On404();
        
        AppTestApp::PhaseCall('z',[AppTestApp::class,'CallIt'],123);
        AppTestApp::PhaseCall('',[AppTestApp::class,'CallIt'],123);
        
        /*
        AppTestApp::ThrowOn(false,'ee',0, null, null);
        try{
            AppTestApp::ThrowOn(true,'ee',0, null, null);
        }catch(\Exception $ex){}
        try{
            AppTestApp::ThrowOn(true,'ee', 0,null, 'exception_project');
        }catch(\Exception $ex){}
        try{
            AppTestApp::ThrowOn(true,'ee',0,null, 'exception_controller');
        }catch(\Exception $ex){}
        try{
            AppTestApp::ThrowOn(true,'ee',0,null, 'exception_business');
        }catch(\Exception $ex){}
        try{
            AppTestApp::ThrowOn(true,'ee',0,null, 'bad');
        }catch(\Exception $ex){}
        //*/
        


$this->doFunctions();

        ////
        $path_view=$this->LibCoverage->getClassTestPath(App::class).'view/';

        $options=[
            'path' => $path_app,
            'path_view'=>$path_view,
            'ext' => [AppTestApp::class => [
                'path_view'=>$path_view,
                'name'=>'MyAppTestApp',
            ]],
        ];
        AppTestApp::G(new AppTestApp());
        App::G(new App())->init($options);
        App::G()->addBeforeShowHandler(function(){ echo "addBeforeShowHandler";});
        \DuckPhp\Core\View::Show(['A'=>'b'],"view");
        
        ////
        ////[[[[
        AppTestApp::G()->getOverrideableFile('view', $path_view."view.php");
        AppTestApp::G()->getOverrideableFile('view', 'view.php');
        ////]]]]
        
        \LibCoverage\LibCoverage::G($this->LibCoverage);
        \LibCoverage\LibCoverage::End();
        return;

    }
    protected function doFunctions()
    {
        \__h("test");
        \__l("test");
        \__hl("test");
        \__url("test");
        \__res("test");
        \__json("test");
        \__domain();
        \__display("_sys/error-404",[]);
        \__trace_dump();
        \__var_dump("abc");
        \__var_log($this);
        \__debug_log("OK");
        
        \__is_debug();
        \__is_real_debug();
        \__platform();
        \__logger();
    }
    public function doSystemWrapper()
    {
        App::system_wrapper_get_providers();
        $output="";

        App::header($output,$replace = true, $http_response_code=0);
        App::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
       
        App::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        App::register_shutdown_function(function(){echo "shutdowning";});
        
        App::session_start([]);
        try{
        App::session_id(md5('123456'));
        }catch(\ErrorException $ex){
        }
        App::session_id(null);
        App::session_destroy();
        $handler=new FakeSessionHandler();
        App::session_set_save_handler( $handler);
        
        
        SystemWrapper::G()->system_wrapper_replace([
            'mime_content_type' =>function(){ echo "change!\n";},
            'header' =>function(){ echo "change!\n";},
            'setcookie' =>function(){ echo "change!\n";},
            'exit' =>function(){ echo "change!\n";},
            'set_exception_handler' =>function(){ echo "change!\n";},
            'register_shutdown_function' =>function(){ echo "change!\n";},
            'session_start' => function(){ echo "change!\n";},
            'session_id' =>  function(){ echo "change!\n";},
            'session_destroy' => function(){ echo "change!\n";},
            'session_set_save_handler' => function(){ echo "change!\n";},
        ]);
        App::mime_content_type('test');
        App::header($output,$replace = true, $http_response_code=0);
        App::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        App::exit($code=0);
        App::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        App::register_shutdown_function(function(){echo "shutdowning";});
        
        
        App::session_start([]);
        App::session_id(null);
        App::session_id(md5('123456'));
        App::session_destroy();
        $handler=new FakeSessionHandler();
        App::session_set_save_handler( $handler);
        
        
        
    }
    public function doException()
    {
        App::G()->options['is_debug']=true;
        App::G()->options['error_500']="_sys/error-exception";
        App::CallException(new \Exception("333333",-1));
        App::G()->options['error_500']=null;
        App::CallException(new \Exception("EXxxxxxxxxxxxxxx",-1));
        
                App::G()->options['error_500']=null;
                App::G()->options['is_debug']=false;
        App::CallException(new \Exception("EXxxxxxxxxxxxxxx",-1));
        App::G()->options['is_debug']=true;
        
        App::G()->options['error_500']=function($ex){ echo $ex;};
        App::CallException(new \Exception("22222222222222",-1));
        

        
        
        $options=[
            'path' => $path_app,
            'is_debug'=>true,
            'on_init'=> function(){ 
            
            AppTestApp::G()->_OnDevErrorHandler(0, '', '', 0);
            AppTestApp::G()->_OnDefaultException(new \Exception('--'));
            },
        ];
        AppTestApp::RunQuickly($options);
    }
    public function doHelper()
    {
        ////
        
        ////
        $str='<>';
        echo  App::H($str);
        echo App::H(['a'=>'b']);
        echo App::H(123);
        echo App::L("a{b}c",[]);
        echo App::L("a{b}c",['b'=>'123']);
        echo App::Hl("&<{b}>",['b'=>'123']);
        echo App::Json("&<{b}>",['b'=>'123']);
        App::IsRunning();
        App::IsDebug();
        App::IsRealDebug();
        App::Platform();
        try{
            App::Pager();
        }catch(\Throwable $ex){
            //
        }
        App::Logger();
        $flag=App::G()->options['is_debug'];
        
        App::G()->options['is_debug']=true;
        App::var_dump("OK");
        App::TraceDump();
        App::DebugLog("OK");
        App::G()->options['is_debug']=false;
        App::TraceDump();
        App::var_dump("OK");
        App::DebugLog("OK");

        App::G()->options['is_debug']=$flag;
        
   
    }
    public function doGlue()
    {
        $path_base=realpath(__DIR__.'/../');
        $path_config=$path_base.'/data_for_tests/Helper/ControllerHelper/';
        $options=[
            'path_config'=>$path_config,
        ];
        Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        App::Setting($key);
        
        
        $url="";
        $method="method";
        App::Url($url=null);
        
        App::Url('abc');
        App::Res('abc');
        //*/
        //*
        $path_view=$this->LibCoverage->getClassTestPath(App::class).'view/';

        $options=[
            'path_view'=>$path_view,
        ];
        View::G()->init($options);
        
        App::G()->addBeforeShowHandler(function(){ echo "addBeforeShowHandler";});
        App::G()->options['skip_view_notice_error']=true;
        App::getViewData();
        
        
        
        //*/
        $url="/abc";
        $path_info="aa/bb";
        $ret=["ret"=>'OK'];
        
        $output="";


        //*/
        

        
        
        $classes=[];
        $k="k";$v="v";
        $class_name=AppTestObject::class;
        $var_name="x";

        

        
        App::addRouteHook(function(){},'append-outter',true);
        
        
        App::isInException();
        
        
        App::CallException(new \Exception("something"));
        
    }
    protected function do404()
    {
        
        
        echo "-----------------------\n";
        $path_app=$this->LibCoverage->getClassTestPath(App::class);
        $path_config=$this->LibCoverage->getClassTestPath(Configer::class);
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'platform' => 'BJ',
            'is_debug' => true,
            'skip_setting_file' => true,
            'use_flag_by_setting' => true,
            
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>AppTestApp::class,
        ];
        DuckPhp::G(new DuckPhp())->init($options);

        
        $options=[
            'path' => $path_app,
            'is_debug'=>false,
        ];
        AppTestApp::On404();
        AppTestApp::RunQuickly($options);

        AppTestApp::G()->options['error_404']='_sys/error-404';
        AppTestApp::On404();
        AppTestApp::G()->options['error_404']=function(){};
        AppTestApp::On404();                
        AppTestApp2::RunQuickly([]);
    }
    protected function do_Core_Redirect()
    {
        // 这里不能直接用 DuckPhp\Core\App ;奇怪；
        AppTestApp::G()->init(App::G()->options);
        AppTestApp::G()->system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);

        $url="/test";
        
        AppTestApp::ExitRedirect($url);
        AppTestApp::ExitRedirect('http://www.github.com');

        AppTestApp::ExitRedirectOutside("http://www.github.com",true);
        AppTestApp::ExitRouteTo($url);
        AppTestApp::Exit404();
        AppTestApp::G()->options['is_debug']=true;
        AppTestApp::ExitJson($ret);
    }
    protected function do_Core_Component()
    {
        App::G()->options['ext']['Xclass']=true;
        //App::G()->getStaticComponentClasses();
//        App::G()->getDynamicComponentClasses();
        $class="NoExits";
//        App::G()->addDynamicComponentClass($class);
        App::G()->skip404Handler();
        

    
        
    }
    public static function Foo()
    {
    }
}
class E extends \Exception
{
    public function handle($ex)
    {
        var_dump("Hit");
    }
}
class E2 extends \Exception
{
    public function display($ex)
    {
        var_dump("Hit2");
    }
}
class AppTestApp extends App
{
    public static function Blank()
    {
    }
    public static function CallIt($arg)
    {
        var_dump($arg);
    }
    public function __construcct()
    {
        parent::__construct();
        return;
    }
    protected function onInit()
    {
        return parent::onInit();
    }
    public function fixPathInfo(&$serverData)
    {
var_dump("zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n");
var_dump($serverData);
        return parent::fixPathInfo($serverData);
    }
}
class AppTestApp2 extends App
{
    protected function onInit()
    {
        return null;
        //throw new \Exception("zzzzzzzzzzzz");
    }
}
class AppTestObject
{
    static $x;
    use SingletonExTrait;

    public static function Foo()
    {
        return "OK";
    }
}
class AppTestObjectA
{
    static $x;
    use SingletonExTrait;
    public static function Foo()
    {
        return "OK";
    }
    public function init($options,$context)
    {
    }
}
class AppTestObjectB
{
    static $x;
    use SingletonExTrait;
    public static function Foo()
    {
        return "OK";
    }
    public function init($options,$context)
    {
    }
}

class FakeSessionHandler implements \SessionHandlerInterface
{
    public function open($savePath, $sessionName)
    {
    }
    public function close()
    {
    }
    public function read($id)
    {
    }
    public function write($id, $data)
    {
    }
    public function destroy($id)
    {
        return true;
    }
    public function gc($maxlifetime)
    {
        return true;
    }
}

}
namespace tests\DuckPhp\Core\Helper{
    use \DuckPhp\Ext\ExtendableStaticCallTrait as a;

class ControllerHelper
{
    use a;
}
class ViewHelper
{
    use a;
}
}
namespace tests\DuckPhp\Core\Controller{
class Base
{
    public function __construct()
    {
    }
    public function index()
    {
        echo "OK";
    }
}
class AppMain extends Base
{
    public function index()
    {
        new Base();
        var_dump("OK");
    }
    public function exception()
    {
        throw new \Exception("HAHA");
    }
}


}