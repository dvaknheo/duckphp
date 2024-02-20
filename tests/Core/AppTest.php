<?php
namespace tests\DuckPhp\Core{

use DuckPhp\Core\App;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\DuckPhp;
use DuckPhp\Component\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;
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

        App::_(new App());
        App::RunQuickly($options,function(){
            $value = $cache[$key]; // trigger notice
            App::_()->options['error_debug']='_sys/error-debug';
            $value = $cache[$key]; 
            
            App::_()->options['error_debug']=function($data){var_dump($data);return;};
            $value = $cache[$key]; 
            
            App::_()->options['is_debug']=false;
            $value = $cache[$key]; 
            App::_()->options['is_debug']=true;

        });
        App::_()->getProjectPath();
        App::_()->getRuntimePath();
        \DuckPhp\Core\Route::_()->bind('/NOOOOOOOOOOOOOOO'); 
        
        App::_()->options['error_404']=function(){
            echo "noooo 404  ooooooooo\n";
        };
        
        App::_()->run();
        \DuckPhp\Core\Route::_()->bind('/exception');
        App::_()->run();
        
        App::_()->isInstalled();
        
        App::_()->options['error_404']=function(){
            echo "zzzzzo 404  zzzzzzzzzzzz\n";
        };
         \DuckPhp\Core\Route::_()->bind('/Base/index');
        try{
        App::_()->system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);

        App::_()->run();
        }catch(\Throwable $ex){
            echo "failed".$ex;
        }
        try{
            App::_()->options['skip_exception_check']=true;
            Route::_()->bind('/exception');
            
            App::_()->run();
        }catch(\Throwable $ex){
            echo $ex->getMessage();
        }
        App::_()->options['skip_exception_check']=false;
        //////////////////////////////////////////////////
        
        $app=new App();
        $options=['plugin_mode'=>true];
        try{
            $app->init($options,$app);
        }catch(\Exception $ex){
            echo $ex->getMessage();
        }
        
        
        //App::_()->clear();
        ///////////////////////////
        $options=[
            // 'no this path' => $path_app,
            'path_config' => $path_app,
            'override_class'=>'\\'.App::class,
            'path_view' => $path_app.'view/',
            'is_debug' => true,
        ];
        View::_(new View());
        Configer::_(new Configer());
        App::_(new App())->init($options);
        

        $this->doException();

        $this->do404();
        $this->doHelper();
        $this->doGlue();
        $this->do_Core_Redirect();
        $this->doSystemWrapper();
        
        
        $this->do_Core_Component();
        

        $old_class = AppTestObjectA::class;
        $new_class = AppTestObjectB::class;
        App::_()->version();
        
        $path_app=$this->LibCoverage->getClassTestPath(App::class);
        $options=[
            'path' => $path_app,
            'is_debug'=>false,
        ];
        AppTestApp::RunQuickly($options);
        
        AppTestApp::_()->options['error_404']='_sys/error-404';
        AppTestApp::On404();
        
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
        AppTestApp::_(new AppTestApp());
        App::_(new App())->init($options);
        \DuckPhp\Core\View::Show(['A'=>'b'],"view");
        
        ////
        ////[[[[
        AppTestApp::_()->getOverrideableFile('view', $path_view."view.php");
        AppTestApp::_()->getOverrideableFile('view', 'view.php');
        ////]]]]
        
        ////[[[[
        $options=[
            'is_debug'=>true,
            'cli_enable'=>false,
            'path' => $path_app,
            'path_view'=>$path_view,
            'ext' => [AppTestApp::class => [
                'is_debug'=>true,
                'cli_enable'=>false,
                'namespace' => 'tests\\DuckPhp\\Core',
                'controller_url_prefix'=>'abc/',
                'controller_class_postfix'=>'',
                'controller_method_prefix'=>'',
                'path_view'=>$path_view,
                'name'=>'MyAppTestApp',
                'error_404'=> function(){ echo "inner404\n";},
                'error_500'=> function(){ echo "inner500\n";},
            ]],
            'error_404'=> function(){ echo "out404\n";},
            'error_500'=> function(){ echo "out500\n";},
        ];
        echo "---------------------------------------\n";
        PhaseContainer::GetContainerInstanceEx(new PhaseContainer());
        AppTestApp::_(new AppTestApp());
        App::_(new App())->init($options);
        var_dump(md5(spl_object_hash(App::_())));
        SystemWrapper::system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);
        //Route::_()->bind('/abc/Base/date');
        //App::_()->run();
        Route::_()->bind('/abc/Base/do404');
        App::_()->run();
        Route::_()->bind('/abc/Base/do500');
        App::_()->run();
        
        Route::_()->bind('/abc/Base/doexit');
        App::_()->run();
        ////]]]]
        echo "-------111111111111-----------\n";
        
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

    }
    public function doException()
    {
        App::_()->options['is_debug']=true;
        App::_()->options['error_500']="_sys/error-exception";
        ExceptionManager::CallException(new \Exception("333333",-1));
        App::_()->options['error_500']=null;
        ExceptionManager::CallException(new \Exception("EXxxxxxxxxxxxxxx",-1));
        
        App::_()->options['error_500']=null;
        App::_()->options['is_debug']=false;
        ExceptionManager::CallException(new \Exception("EXxxxxxxxxxxxxxx",-1));
        App::_()->options['is_debug']=true;
        
        App::_()->options['error_500']=function($ex){ echo $ex;};
        ExceptionManager::CallException(new \Exception("22222222222222",-1));
        
        ExceptionManager::_()->assignExceptionHandler(\Exception::class,function($ex){
            App::OnDefaultException($ex);
           
        });
        ExceptionManager::CallException(new \Exception("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA",-1));
        ExceptionManager::CallException(new E("EXxxxxxxxxxxxxxx",-1));
        ExceptionManager::CallException(new E2("EXxxxxxxxxxxxxxx",-1));
        
        
        $options=[
            'path' => $path_app,
            'is_debug'=>true,
            'on_init'=> function(){ 
            
            AppTestApp::_()->_OnDevErrorHandler(0, '', '', 0);
            AppTestApp::_()->_OnDefaultException(new \Exception('--'));
            },
        ];
        AppTestApp::_(new AppTestApp);
        PhaseContainer::GetContainerInstanceEx(new PhaseContainer());
        AppTestApp::RunQuickly($options);
    }
    public function doHelper()
    {
        App::IsDebug();
        App::IsRealDebug();
        App::Platform();

   
    }
    public function doGlue()
    {
        $path_base=realpath(__DIR__.'/../');
        $path_config=$path_base.'/data_for_tests/Helper/ControllerHelper/';
        $options=[
            'path_config'=>$path_config,
        ];
        Configer::_()->init($options);
        $key='key';
        $file_basename='config';
        
        App::Setting($key);
        
        
        $url="";
        $method="method";

        //*/
        //*
        $path_view=$this->LibCoverage->getClassTestPath(App::class).'view/';

        $options=[
            'path_view'=>$path_view,
        ];
        View::_()->init($options);
        
        View::_()->options['skip_view_notice_error']=true;
        //App::getViewData();

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
        DuckPhp::_(new DuckPhp())->init($options);

        
        $options=[
            'path' => $path_app,
            'is_debug'=>false,
        ];
        AppTestApp::On404();
        AppTestApp::RunQuickly($options);

        AppTestApp::_()->options['error_404']='_sys/error-404';
        AppTestApp::On404();
        AppTestApp::_()->options['error_404']=function(){};
        AppTestApp::On404();                
        AppTestApp2::RunQuickly([]);
    }
    protected function do_Core_Redirect()
    {

    }
    protected function do_Core_Component()
    {
        App::_()->options['ext']['Xclass']=true;
        //App::_()->getStaticComponentClasses();
//        App::_()->getDynamicComponentClasses();
        $class="NoExits";
//        App::_()->addDynamicComponentClass($class);
        App::_()->skip404Handler();
        

    
        
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
    public function do404()
    {
        \DuckPhp\Core\CoreHelper::Show404(false);
    }
    public function do500()
    {
        throw new \Exception('500000');
    }
    public function doexit()
    {
        throw new \DuckPhp\Core\ExitException('500000');
    }
    public function date()
    {
        var_dump(DATE(DATE_ATOM));
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