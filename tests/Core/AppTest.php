<?php
namespace tests\DuckPhp\Core{

use DuckPhp\Core\App;
use DuckPhp\App as DuckPhp;
use DuckPhp\Core\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonEx;
use DuckPhp\Ext\Pager;

class AppTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(App::class);
    
        $path_app=\GetClassTestPath(App::class);
        $path_config=\GetClassTestPath(Configer::class);
        
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'path_view' => $path_app.'view/',
            'namespace' => __NAMESPACE__,
            'platform' => 'ForTests',
            'is_debug' => true,
            'skip_setting_file' => true,
            'use_flag_by_setting' => false,
            'error_exception' => NULL,
            'error_500' => NULL,
            'error_404' => NULL,
            'error_debug' => NULL,
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>'\\'.AppTestApp::class,
            'controller_welcome_class'=>'AppMain',

        ];

        $options['ext']=[
            'noclass'=>true,
            AppTestObject::class=>false,
            AppTestObjectA::class=>true,
            AppTestObjectB::class=>['aa'=>'22'],
        ];
        App::RunQuickly($options,function(){
            App::G()->addBeforeShowHandler(function(){ echo "beforeShowHandlers";});
            
            $value = $cache[$key]; // trigger notice
            App::G()->options['error_debug']='_sys/error-debug';
            $value = $cache[$key]; 
            
            App::G()->options['error_debug']=function($data){var_dump($data);return;};
            $value = $cache[$key]; 
            
            App::G()->is_debug=false;
            $value = $cache[$key]; 
            App::G()->is_debug=true;

        });
        
        //App::SG()->_SERVER['PATH_INFO']='/NOOOOOOOOOOOOOOO';
        Route::G()->bind('/NOOOOOOOOOOOOOOO');  // 这两句居然有区别 ,TODO ，分析之
        
        App::G()->options['error_404']=function(){
            echo "noooo 404  ooooooooo\n";
            
        };
        
        App::G()->run();
echo "-------------------------------------\n";
        Route::G()->bind('/exception');
        App::G()->run();

        try{
            App::G()->options['skip_exception_check']=true;
            Route::G()->bind('/exception');
            
            App::G()->run();
        }catch(\Throwable $ex){
            echo $ex->getMessage();
        }
        App::G()->options['skip_exception_check']=false;
//\MyCodeCoverage::G()->end(App::class);
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
        
        
        App::G()->clear();
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
        
        $xfunc=function () {
            var_dump("changed");
            return true;
        };
        App::G()->replaceDefaultRunHandler($xfunc);
        App::G()->run();
        
        $this->do_Core_Component();
        
        $this->doFixPathinfo();
        
App::Pager(Pager::G());
App::PageNo();
App::PageSize();
App::PageHtml(123);

        
    \MyCodeCoverage::G()->end(App::class);
    $this->assertTrue(true);
    return;

    }
    protected function doFixPathinfo()
    {
        AppTestApp::G()->init([]);
        $serverData=[
        ];
        AppTestApp::G()->fixPathInfo($serverData);
        
        $serverData=[
            'PATH_INFO'=>'abc',
        ];
        AppTestApp::G()->fixPathInfo($serverData);
        $serverData=[
            'REQUEST_URI'=>'/',
            'SCRIPT_FILENAME'=>__DIR__ . '/index.php',
            'DOCUMENT_ROOT'=>__DIR__,
        ];
        
        AppTestApp::G()->fixPathInfo($serverData);
        
        $serverData=[
            'REQUEST_URI'=>'/abc/d',
            'SCRIPT_FILENAME'=>__FILE__,
            'DOCUMENT_ROOT'=>__DIR__,
        ];
        AppTestApp::G()->fixPathInfo($serverData);
        
        
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
        
        App::G()->system_wrapper_replace([
            'header' =>function(){ echo "change!\n";},
            'setcookie' =>function(){ echo "change!\n";},
            'exit' =>function(){ echo "change!\n";},
            'set_exception_handler' =>function(){ echo "change!\n";},
            'register_shutdown_function' =>function(){ echo "change!\n";},
        ]);
        
        App::header($output,$replace = true, $http_response_code=0);
        App::setcookie( $key="123",  $value = '', $expire = 0,  $path = '/',  $domain  = '', $secure = false,  $httponly = false);
        App::exit($code=0);
        App::set_exception_handler(function($handler){
            return set_exception_handler($handler);
        });
        App::register_shutdown_function(function(){echo "shutdowning";});
        
    }
    public function doException()
    {
        App::G()->is_debug=true;
        App::G()->options['error_500']="_sys/error-exception";
        App::OnException(new \Exception("333333",-1));
        App::G()->options['error_500']=null;
        App::OnException(new \Exception("EXxxxxxxxxxxxxxx",-1));
        
        App::G()->options['error_500']=function($ex){ echo $ex;};
        App::OnException(new \Exception("22222222222222",-1));
        
        App::assignExceptionHandler(\Exception::class,function($ex){
            App::OnDefaultException($ex);
            echo "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
        });
        App::OnException(new \Exception("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA",-1));
        App::OnException(new E("EXxxxxxxxxxxxxxx",-1));
        App::OnException(new E2("EXxxxxxxxxxxxxxx",-1));
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
        echo App::HL("&<{b}>",['b'=>'123']);
        echo App::Domain();
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
        $flag=App::G()->is_debug;
        
        App::G()->is_debug=true;
        App::trace_dump();
        App::var_dump("OK");
        App::G()->is_debug=false;
        App::trace_dump();
        App::var_dump("OK");
        App::G()->is_debug=$flag;
        
        $sql="Select * from users";
        App::SqlForPager($sql,1,5);
        App::SqlForCountSimply($sql);        
    }
    public function doGlue()
    {
        $path_base=realpath(__DIR__.'/../');
        $path_config=$path_base.'/data_for_tests/Helper/ControllerHelper/';
        $options=[
            'skip_setting_file'=>true,
            'path_config'=>$path_config,
        ];
        Configer::G()->init($options);
        $key='key';
        $file_basename='config';
        
        App::Setting($key);
        App::Config($key, $file_basename);
        App::LoadConfig($file_basename);
        
        
        $url="";
        $method="method";
        App::URL($url=null);
        
        App::getParameters();
        App::Parameter('x','y');
        App::getRouteCallingMethod();
        App::setRouteCallingMethod($method);
        App::setURLHandler(function($url){return $url;});
        App::URL('abc');
        //*/
        //*
        $path_view=\GetClassTestPath(App::class).'view/';

        $options=[
            'path_view'=>$path_view,
        ];
        View::G()->init($options);
        
        App::G()->addBeforeShowHandler(function(){ echo "addBeforeShowHandler";});
        App::G()->options['skip_view_notice_error']=true;
        App::Show(['A'=>'b'],"view");
        App::Display("view",['A'=>'b']);
        
        
        $key="key";
        App::setViewWrapper($head_file=null, $foot_file=null);
        App::assignViewData($key, $value=null);
        
        //*/
        $url="/abc";
        $path_info="aa/bb";
        $ret=["ret"=>'OK'];
        
        $output="";


        //*/
        

        
        
        $classes=[];
        $callback=function($code){
            var_dump(DATE(DATE_ATOM));
        };
        App::assignExceptionHandler($classes, $callback);
        App::setMultiExceptionHandler($classes, $callback);
        App::setDefaultExceptionHandler($callback);
        
        $k="k";$v="v";
        $class_name=AppTestObject::class;
        $var_name="x";
        App::SG();
        App::GLOBALS($k, $v=null);
        App::STATICS($k, $v=null);
        App::CLASS_STATICS($class_name, $var_name);        
        
        App::session_start($options=[]);
        App::session_id(null);
        App::session_destroy();
        $handler=new FakeSessionHandler();
        App::session_set_save_handler( $handler);
        
        App::assignPathNamespace("NoPath","NoName");
        App::addRouteHook(function(){},'append-outter',true);
        
        
        App::isInException();
        App::getPathInfo();
        
        
        App::OnException(new \Exception("something"));
        
    }
    protected function do404()
    {
        
        
        echo "-----------------------\n";
        $path_app=\GetClassTestPath(App::class);
        $path_config=\GetClassTestPath(Configer::class);
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'platform' => 'BJ',
            'is_debug' => true,
            'skip_setting_file' => true,
            'use_flag_by_setting' => true,
            
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>'\\'.AppTestApp::class,
        ];
        DuckPhp::G(new DuckPhp())->init($options);

        
        $options=[
            'path' => $path_app,
            'skip_setting_file' => true,
            'is_debug'=>false,
        ];
        AppTestApp::RunQuickly($options);
        
        AppTestApp::G()->options['error_404']='_sys/error-404';
        AppTestApp::On404();        
        AppTestApp2::RunQuickly([]);
    }
    protected function do_Core_Redirect()
    {
        App::G()->system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);
        
        $url="/test";
        
        App::ExitRedirect($url);
        App::ExitRedirect('http://www.github.com');

        App::ExitRedirectOutside("http://www.github.com",true);
        App::ExitRouteTo($url);
        App::Exit404();
        App::G()->is_debug=true;
        App::ExitJson($ret);
    }
    protected function do_Core_Component()
    {

        App::G()->getStaticComponentClasses();
        App::G()->getDynamicComponentClasses();
        $class="NoExits";
        App::G()->addDynamicComponentClass($class);
        App::G()->removeDynamicComponentClass($class);
        
        
        $new_namespace=__NAMESPACE__;
        $new_namespace.='\\';
    
        $options=[
            //'path' => $path_app,
            'is_debug' => true,
            'skip_setting_file' => true,
            'namespace'=> __NAMESPACE__,
            'override_class'=>'\\'.AppTestApp::class,
        ];
        App::G()->init($options);

        App::G()->extendComponents(['Foo'=>[AppTest::class,'Foo']],['V',"ZZZ"]);
        App::G()->cloneHelpers($new_namespace);
        App::G()->cloneHelpers($new_namespace, ['M'=>'no_exits_class']);
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
    use SingletonEx;

    public static function Foo()
    {
        return "OK";
    }
}
class AppTestObjectA
{
    static $x;
    use SingletonEx;
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
    use SingletonEx;
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
class ControllerHelper
{
    use \DuckPhp\Helper\HelperTrait;
}
class ViewHelper
{
    use \DuckPhp\Helper\HelperTrait;
}
}
namespace tests\DuckPhp\Core\Controller{
class AppMain
{
    public function index()
    {
        var_dump("OK");
    }
    public function exception()
    {
        throw new \Exception("HAHA");
    }
}


}