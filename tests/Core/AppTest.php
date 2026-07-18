<?php
namespace tests\DuckPhp\Core{

use DuckPhp\Core\App;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\DuckPhp;
use DuckPhp\Component\Configer;
use DuckPhp\Core\ExitException;
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
        
        //TODO move function test to CoreHelperTest
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
            'is_debug' => false,
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
            //'noclass'=>true,
            AppTestObject::class=>false,
            AppTestObjectA::class=>true,
            AppTestObjectB::class=>['aa'=>'22'],
        ];

        MyApp::_(new MyApp());
        MyApp::RunQuickly($options,function(){
			$e_old = error_reporting();
			error_reporting($e_old |E_USER_NOTICE |E_NOTICE |E_STRICT |E_DEPRECATED |E_USER_DEPRECATED);
            $value = $cache[$key]; // trigger notice
            MyApp::_()->options['error_debug']='_sys/error-debug';
            $value = $cache[$key]; 
            
            MyApp::_()->options['error_debug']=function($data){var_dump($data);return;};
            $value = $cache[$key]; 
            
            MyApp::_()->options['is_debug']=false;
            $value = $cache[$key]; 
            MyApp::_()->options['is_debug']=true;
			error_reporting($e_old);
        });

        \DuckPhp\Core\Route::_()->bind('/NOOOOOOOOOOOOOOO'); 
        
        MyApp::_()->options['error_404']=function(){
            echo "noooo 404  ooooooooo\n";
        };
        
        MyApp::_()->run();
        \DuckPhp\Core\Route::_()->bind('/exception');
        MyApp::_()->run();
        
        MyApp::_()->isInstalled();
        
        MyApp::_()->options['error_404']=function(){
            echo "zzzzzo 404  zzzzzzzzzzzz\n";
        };
         \DuckPhp\Core\Route::_()->bind('/Base/index');
        try{
        MyApp::_()->system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);

        MyApp::_()->run();
        }catch(\Throwable $ex){
            echo "failed".$ex;
        }
        try{
            MyApp::_()->options['skip_exception_check']=true;
            Route::_()->bind('/exception');
            
            MyApp::_()->run();
        }catch(\Throwable $ex){
            echo $ex->getMessage();
        }
        MyApp::_()->options['skip_exception_check']=false;
        //////////////////////////////////////////////////
        /*
        $MyApp=new MyApp();
        $options=['plugin_mode'=>true];
        try{
            $app->init($options,$MyApp);
        }catch(\Exception $ex){
            echo $ex->getMessage();
        }
        
        
        //MyApp::_()->clear();
        */
        ///////////////////////////
        $options=[
            // 'no this path' => $path_app,
            'path_config' => $path_app,
            'override_class'=>'\\'.MyApp::class,
            'path_view' => $path_app.'view/',
            'is_debug' => true,
        ];
        View::_(new View());
        Configer::_(new Configer());
        MyApp::_(new MyApp())->init($options);
        

        $this->doException();

        $this->do404();
        $this->doHelper();
        $this->doGlue();
        $this->do_Core_Redirect();
        $this->doSystemWrapper();
        
        
        $this->do_Core_Component();
        

        $old_class = AppTestObjectA::class;
        $new_class = AppTestObjectB::class;
        MyApp::_()->version();
        
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
            'MyApp' => [AppTestApp::class => [
                'path_view'=>$path_view,
                'name'=>'MyAppTestApp',
            ]],
        ];
        AppTestApp::_(new AppTestApp());
        MyApp::_(new MyApp());//->overriding_class = MyApp::class; //要这么清理状态可不好，最好不要裸用App 类以防处意外
        MyApp::_()->init($options);
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
PhaseContainer::RestAllContainerForTesting();
        AppTestApp::_(new AppTestApp());
        MyApp::_(new MyApp())->init($options);
        var_dump(md5(spl_object_hash(MyApp::_())));
        SystemWrapper::system_wrapper_replace(['exit'=>function($code){
            var_dump(DATE(DATE_ATOM));
        }]);
        //Route::_()->bind('/abc/Base/date');
        //MyApp::_()->run();
        Route::_()->bind('/abc/Base/do404');
        MyApp::_()->run();
        Route::_()->bind('/abc/Base/do500');
        MyApp::_()->run();

        ExitException::Init();
        Route::_()->bind('/abc/Base/doexit');
        MyApp::_()->run();
        ////]]]]
		MyApp::_()->options['lang_handler']=[static::class,'lang_handler'];
		MyApp::_()->lang("test",[]);
		MyApp::_()->options['lang_handler']=null;
		MyApp::_()->lang("test",[]);
		MyApp::_()->lang("test{hello}",['hello'=>'world']);

PhaseContainer::RestAllContainerForTesting();
        $options =[
            'path' => $path_app,
            'is_debug'=>true,
            'error_debug'=>function($data){
                return;
            },
        ];
        MyApp::_(new MyApp())->init($options);

        MyApp::_()->_OnDevErrorHandler(0, '', '', 0);
        MyApp::_()->options['error_debug'] = '_sys/error-debug';
        MyApp::_()->_OnDevErrorHandler(0, '', '', 0);
        ///////////////////////////////////////////
PhaseContainer::RestAllContainerForTesting();
        $options =[
            'path' => $path_app,
            'name' =>'zz',
            'app'=>[
                AppTestApp2::class =>[
                    'name'=>'abc',
                ],
            ],
        ];
        
        MyApp::_(new MyApp())->init($options);
        //$x = AppTestApp2::FromCurrentParent()->getOverrideableFile("view",'abc');
PhaseContainer::RestAllContainerForTesting();
        try{
            \DuckPhp\Core\App::_(new \DuckPhp\Core\App())->init([]);
        }catch(\Exception $ex){}
        
        $this->doLoadSettingCoverage();
        $this->doAppCoverageGapTest();
        
        \LibCoverage\LibCoverage::End();
        return;

    }
	public static function lang_handler($str,$arg=[])
	{
		return $str;
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
    public function doLoadSettingCoverage()
    {
        // 覆盖 App::loadSetting / dealWithEnvFile / dealWithSettingFile 分支
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(App::class);
        
        // 覆盖 use_env_file 分支
        $envFile = $path_app . '.env';
        file_put_contents($envFile, "ENV_TEST_FOO=bar\nENV_TEST_BAZ=42\n");
PhaseContainer::RestAllContainerForTesting();
        AppTestApp::_(new AppTestApp());
        AppTestApp::RunQuickly([
            'path' => $path_app,
            'use_env_file' => true,
            'setting_file_enable' => false,
            'cli_enable' => false,
        ]);
        unlink($envFile);
        
        // 覆盖 setting_file 绝对路径分支
        $settingDir = $path_app . 'config/';
        if (!is_dir($settingDir)) {
            mkdir($settingDir, 0777, true);
        }
        $absSettingFile = $settingDir . 'AbsSetting.config.php';
        file_put_contents($absSettingFile, "<?php\nreturn ['abs_key' => 'abs_value'];\n");
PhaseContainer::RestAllContainerForTesting();
        AppTestApp::_(new AppTestApp());
        AppTestApp::RunQuickly([
            'path' => $path_app,
            'setting_file' => $absSettingFile,
            'setting_file_ignore_exists' => false,
            'cli_enable' => false,
        ]);
        unlink($absSettingFile);
        
        // 覆盖 setting_file 不存在且 ignore_exists=false 分支
PhaseContainer::RestAllContainerForTesting();
        AppTestApp::_(new AppTestApp());
        try {
            AppTestApp::RunQuickly([
                'path' => $path_app,
                'setting_file' => $path_app . 'config/NotExist.config.php',
                'setting_file_ignore_exists' => false,
                'cli_enable' => false,
            ]);
        } catch (\ErrorException $ex) {
            // expected
        }
    }
    public function doException()
    {
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(App::class);
        MyApp::_()->options['path']=$path_app;
        MyApp::_()->options['is_debug']=true;
        MyApp::_()->options['error_500']="_sys/error-exception";
        ExceptionManager::CallException(new \Exception("333333",-1));
        MyApp::_()->options['error_500']=null;
        ExceptionManager::CallException(new \Exception("EXxxxxxxxxxxxxxx",-1));
        
        MyApp::_()->options['error_500']=null;
        MyApp::_()->options['is_debug']=false;
        ExceptionManager::CallException(new \Exception("EXxxxxxxxxxxxxxx",-1));
        MyApp::_()->options['is_debug']=true;
        
        MyApp::_()->options['error_500']=function($ex){ echo $ex;};
        ExceptionManager::CallException(new \Exception("22222222222222",-1));
        
        ExceptionManager::_()->assignExceptionHandler(\Exception::class,function($ex){
            MyApp::OnDefaultException($ex);
           
        });
        ExceptionManager::CallException(new \Exception("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA",-1));
        ExceptionManager::CallException(new E("EXxxxxxxxxxxxxxx",-1));
        ExceptionManager::CallException(new E2("EXxxxxxxxxxxxxxx",-1));
        
        
        $options=[
            'path' => $path_app,
            'is_debug'=>true,
            'on_init'=> function(){ 
            ExitException::Init();
            AppTestApp::_()->_OnDevErrorHandler(0, '', '', 0);
            AppTestApp::_()->_OnDefaultException(new \Exception('--'));
            AppTestApp::_()->_OnDefaultException(new ExitException('--'));
            },
        ];
        AppTestApp::_(new AppTestApp);
PhaseContainer::RestAllContainerForTesting();
        AppTestApp::RunQuickly($options);
    }
    public function doHelper()
    {
        MyApp::IsDebug();
        MyApp::IsRealDebug();
        MyApp::Platform();

   
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
        
        MyApp::Setting($key);
        
        
        $url="";
        $method="method";

        //*/
        //*
        $path_view=$this->LibCoverage->getClassTestPath(MyApp::class).'view/';

        $options=[
            'path_view'=>$path_view,
        ];
        View::_()->init($options);
        
        View::_()->options['skip_view_notice_error']=true;
        //MyApp::getViewData();

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
        MyApp::_()->options['ext']['Xclass']=true;
        //MyApp::_()->getStaticComponentClasses();
//        MyApp::_()->getDynamicComponentClasses();
        $class="NoExits";
//        MyApp::_()->addDynamicComponentClass($class);
        MyApp::_()->skip404Handler();
        

    
        
    }
    public static function Foo()
    {
    }
    // ======== 新增：覆盖缺口测试 ========
    protected function doAppCoverageGapTest()
    {
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(App::class);
        
        // 1. prepareServe maintain: error_maintain=null → 默认 Maintaining 消息
        PhaseContainer::RestAllContainerForTesting();
        ob_start();
        $a1 = new MyApp();
        MyApp::_($a1)->init([
            'path' => $path_app,
            'is_maintain' => true,
            'cli_enable' => false,
            'namespace' => __NAMESPACE__,
            'is_debug' => false,
        ]);
        $a1->serve();
        $out1 = ob_get_clean();
        $this->assertStringContainsString('Maintaining', $out1);
        
        // 2. prepareServe maintain: error_maintain=callable
        PhaseContainer::RestAllContainerForTesting();
        ob_start();
        $a2 = new MyApp();
        MyApp::_($a2)->init([
            'path' => $path_app,
            'is_maintain' => true,
            'error_maintain' => function () { echo 'cb_maintain'; },
            'cli_enable' => false,
            'namespace' => __NAMESPACE__,
        ]);
        $a2->serve();
        $out2 = ob_get_clean();
        $this->assertStringContainsString('cb_maintain', $out2);
        
        // 3. _OnDefaultException 在 is_inited=false (line 236)
        $a3 = new MyApp();
        ob_start();
        $a3->_OnDefaultException(new \Exception('before_init'));
        $out3 = ob_get_clean();
        $this->assertStringContainsString('error trigger before inited', $out3);
        
        // 4. _OnDevErrorHandler 在 error_debug=null 输出默认 HTML (lines 288-301)
        PhaseContainer::RestAllContainerForTesting();
        ob_start();
        $a4 = new MyApp();
        MyApp::_($a4)->init([
            'path' => $path_app,
            'is_debug' => true,
            'cli_enable' => false,
            'namespace' => __NAMESPACE__,
            // error_debug 未设置 → null
        ]);
        $a4->_OnDevErrorHandler(E_USER_NOTICE, 'test_err', '/fake/file', 42);
        $out4 = ob_get_clean();
        $this->assertStringContainsString('DuckPhp_DEBUG', $out4);
        $this->assertStringContainsString('test_err', $out4);
        $this->assertStringContainsString('/fake/file', $out4);
        
        // 5. prepareServe maintain: error_maintain='view' → View::Show (line 124)
        PhaseContainer::RestAllContainerForTesting();
        ob_start();
        $a5 = new MyApp();
        MyApp::_($a5)->init([
            'path' => $path_app,
            'path_view' => $path_app . 'view/',
            'is_maintain' => true,
            'error_maintain' => 'view',
            'cli_enable' => false,
            'namespace' => __NAMESPACE__,
            'view_skip_notice_error' => true,
        ]);
        $a5->serve();
        $out5 = ob_get_clean();
        $this->assertStringContainsString('Hello DuckPhp', $out5);
    }
}
class MyApp extends App
{
    protected function onPrepare(): void
    {
        //just for skip self::_()->Init;
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
class AppTestApp extends MyApp
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
class AppTestApp2 extends MyApp
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