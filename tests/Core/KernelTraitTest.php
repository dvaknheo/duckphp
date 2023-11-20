<?php
namespace tests\DuckPhp\Core{

use DuckPhp\Core\App;
use DuckPhp\Core\KernelTrait;
use DuckPhp\Core\Runtime;
use DuckPhp\DuckPhp;
use DuckPhp\Component\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;
use DuckPhp\Ext\Pager;

class KernelTraitTest extends \PHPUnit\Framework\TestCase
{
    public static function Blank()
    {
    }
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(KernelTrait::class);
        $LibCoverage = \LibCoverage\LibCoverage::G();
        
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(App::class);
        $path_config=\LibCoverage\LibCoverage::G()->getClassTestPath(Configer::class); //???
        
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'path_view' => $path_app.'view/',
            'namespace' => __NAMESPACE__,
            'platform' => 'ForTests',
            'is_debug' => true,
            'setting_file_enable' => true,
            'use_flag_by_setting' => true,
            'error_exception' => NULL,
            'error_500' => NULL,
            'error_404' => NULL,
            'error_debug' => [static::class,'Blank'],
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>'\\'.KernelTestApp::class,
            'skip_fix_path_info'=>true,
            'on_init' =>function (){ echo 'Inited!';},
            'cli_enable' => true,
            
            'controller_class_postfix' => 'Controller',
            'controller_method_prefix' => 'action_',
        ];
        $options['ext']=[
            'noclass'=>true,
            KernelTestObject::class=>false,
            KernelTestObjectA::class=>true,
            KernelTestObjectB::class=>['aa'=>'22'],
        ];
        App::RunQuickly($options,function(){});
        App::_()->options['cli_enable'] =false;
        
        //App::SG()->_SERVER['PATH_INFO']='/NOOOOOOOOOOOOOOO';
        Route::_()->bind('/NOOOOOOOOOOOOOOO');  // 这两句居然有区别 ,TODO ，分析之
        
        App::_()->options['error_404']=function(){
            echo "noooo 404  ooooooooo\n";
            
        };
        
        App::_()->options['controller_class_postfix']='';
            App::_()->options['controller_method_prefix']='';
        App::_()->run();
echo "-------------------------------------\n";
            
        Route::_()->bind('/exception');
        App::_()->run();

        try{
            App::_()->options['skip_exception_check']=true;
            App::_()->options['controller_class_postfix']='';
            App::_()->options['controller_method_prefix']='';
            
            Route::_()->bind('/exception');
            App::_()->run();
        }catch(\Throwable $ex){
            echo $ex->getMessage();
        }
        App::_()->options['skip_exception_check']=false;
        
        Route::_()->bind('/exception2');
        App::_()->run();
        //////////////////////////////////////////////////
        
        $app=new App();

        
        //App::_()->clear();
        ///////////////////////////
        $options=[
            // 'no this path' => $path_app,
            'path_config' => $path_app,
            'override_class'=>'\\'.App::class,
            'path_view' => $path_app.'view/',
            'is_debug' => true,
            'use_short_functions' => true,
            'setting_file_enable' => true,

        ];
        View::_(new View());
        Configer::_(new Configer());
        App::_(new App())->init($options);
        App::_()->getProjectPathFromClass(App::class,true);
        $this->do404();
        

        ////
        //Runtime::_()->toggleOutputed(false);
        //App::OnOutputBuffering('abc');
        //Runtime::_()->toggleOutputed(true);
        //App::OnOutputBuffering('def');
        ////
        
            App::_()->isInited();
//////////////////
        App::_(new App());
        App::_()->init([
            'handle_all_dev_error' => false,
            'handle_all_exception' => false,
//          'override_class' => 'no_Exits',
        ]);
        App::_()->init([
            'handle_all_dev_error' => false,
            'handle_all_exception' => false,
            'override_class' => App::class,
        ]);
        App::_()->init([
            'handle_all_dev_error' => false,
            'handle_all_exception' => false,
            'use_autoloader' => true,
        ]);
        ////////////////////////
        MyKernelTrait::OnDefaultException(new \Exception("error"));
        MyKernelTrait::OnDevErrorHandler("", "", "", "");
        MyKernelTrait::On404();

        $options['cli_enable']=false;
        $options['ext'][KernelTestApp2::class]=[
            'path'=>null,
            'namespace' => __NAMESPACE__,
            'controller_url_prefix'=>'/child/',
        ];
        App::Root();
        App::Current();
        
        App::_(new App())->init($options)->run();
        
        App::Root();
        App::Current();
        
        $_SERVER['PATH_INFO'] = '/child/date';
        //Route::_()->bind();
        App::_()->run();
        $phase=App::Phase();
        App::Phase($phase);
        
        /////////////////////
        $options['ext'][KernelTestApp2::class]=[
            'path'=>null,
            'namespace' => __NAMESPACE__,
            'ext' =>[ KernelTestObjectError::class=>true,]
        ];
        try{
        App::_(new App())->init($options)->run();
        }catch(\Exception $ex){}
        /////////////////////
        $options =[
            'path' => $path_app,
            'use_flag_by_setting' => true,
            'use_env_file' => true,
        ];
        App::_(new App())->init($options);
        
        try{
        $options =[
            'path' => $path_app,
            'use_flag_by_setting' => true,
            'use_env_file' => true,
            'setting_file' =>$path_app.'no_exists.php',
            'setting_file_ignore_exists' =>false,
        ];
        App::_(new App())->init($options);
        }catch(\Exception $ex){}
        $options =[
            'path' => $path_app,
            'use_flag_by_setting' => true,
            'use_env_file' => true,
            'setting_file' =>$path_app.'DuckPhpSettings.config.php',
            'setting_file_ignore_exists' =>false,
        ];
        App::_(new App())->init($options);
        //setting.php
        
        MyKernelTrait::_()->init($options);
        MyKernelTrait::_()->isRoot();
        
        ////[[[[
        
        $options =[
            'path' => $path_app,
            'use_flag_by_setting' => true,
            'use_env_file' => true,
            'setting_file' =>$path_app.'DuckPhpSettings.config.php',
            'setting_file_ignore_exists' =>false,
        ];
        App::_(new App())->init($options);
        
        ////]]]]
        
        
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    return;

    }
    protected function do404()
    {
        
        
        echo "-----------------------\n";
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(App::class);
        $path_config=\LibCoverage\LibCoverage::G()->getClassTestPath(Configer::class);
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'platform' => 'BJ',
            'is_debug' => true,
            'use_flag_by_setting' => false,
            'setting_file_enable' => true,
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>'\\'.KernelTestApp::class,
        ];
        DuckPhp::_(new DuckPhp())->init($options);

        
        $options=[
            'path' => $path_app,
            'is_debug'=>false,
        ];
        KernelTestApp::RunQuickly($options);
        
        KernelTestApp::_()->options['error_404']='_sys/error-404';
        KernelTestApp::On404();        
        KernelTestApp2::RunQuickly([]);
    }
    
}
class MyKernelTrait
{
    use SingletonExTrait;
    use KernelTrait;
}

class KernelTestApp extends App
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
class KernelTestApp2 extends App
{
    protected function onInit()
    {
        return null;
        //throw new \Exception("zzzzzzzzzzzz");
    }
}
class KernelTestObject
{
    static $x;
    use SingletonExTrait;

    public static function Foo()
    {
        return "OK";
    }
}
class KernelTestObjectA
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
class KernelTestObjectB
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
class KernelTestObjectError
{
    use SingletonExTrait;
    public function init($options,$context)
    {
        throw new \Exception("test");
    }

}

}


namespace tests\DuckPhp\Core\Controller{
class MainController
{
    public function action_index()
    {
        var_dump("OK");
    }
    public function action_date()
    {
        var_dump(DATE(DATE_ATOM));
    }
    public function action_exception()
    {
        throw new \Exception("HAHA");
    }
    public function action_exception2()
    {
        \DuckPhp\Core\App::Phase("MyPhase");
        throw new \Exception("HAHA");
    }
}
}
