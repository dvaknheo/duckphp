<?php
namespace tests\DuckPhp\Core{

use DuckPhp\Core\App;
use DuckPhp\Core\KernelTrait;
use DuckPhp\Core\RuntimeState;
use DuckPhp\DuckPhp;
use DuckPhp\Core\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Ext\Pager;

class KernelTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(KernelTrait::class);
    
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(App::class);
        $path_config=\LibCoverage\LibCoverage::G()->getClassTestPath(Configer::class);
        
        $options=[
            'path' => $path_app,
            'path_config' => $path_config,
            'path_view' => $path_app.'view/',
            'namespace' => __NAMESPACE__,
            'platform' => 'ForTests',
            'is_debug' => true,
            'use_setting_file' => true,
            'use_flag_by_setting' => true,
            'error_exception' => NULL,
            'error_500' => NULL,
            'error_404' => NULL,
            'error_debug' => [APP::class,'Blank'],
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>'\\'.KernelTestApp::class,
            'skip_fix_path_info'=>true,
        ];
        $options['ext']=[
            'noclass'=>true,
            KernelTestObject::class=>false,
            KernelTestObjectA::class=>true,
            KernelTestObjectB::class=>['aa'=>'22'],
        ];
        App::RunQuickly($options,function(){
            App::G()->addBeforeShowHandler(function(){ echo "beforeShowHandlers";});
            
            $value = $cache[$key]; // trigger notice
            App::G()->options['error_debug']='_sys/error-debug';
            $value = $cache[$key]; 
            
            App::G()->options['error_debug']=function($data){var_dump($data);return;};
            $value = $cache[$key]; 
            
            App::G()->options['is_debug']=false;
            $value = $cache[$key]; 
            App::G()->options['is_debug']=true;
            App::G()->onPrepare=function(){ echo "onPrepare!";};
            App::G()->onInit=function(){ echo "onInit!";};
            App::G()->onBeforeRun=function(){ echo "onRun!";};
            App::G()->onAfterRun=function(){ echo "onAfterRun!";};

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
            'use_short_functions' => true,
            'use_setting_file' => true,

        ];
        View::G(new View());
        Configer::G(new Configer());
        App::G(new App())->init($options);



        $this->do404();
        

        
        $xfunc=function () {
            var_dump("changed");
            return true;
        };
        App::G()->replaceDefaultRunHandler($xfunc);
        App::G()->run();
        

        ////
        RuntimeState::G()->toggleOutputed(false);
        //App::OnOutputBuffering('abc');
        RuntimeState::G()->toggleOutputed(true);
        //App::OnOutputBuffering('def');
        ////
        
            App::G()->isInited();
//////////////////
        App::G(new App());
        App::G()->init([
            'handle_all_dev_error' => false,
            'handle_all_exception' => false,
            'override_class' => 'no_Exits',
        ]);
        App::G()->init([
            'handle_all_dev_error' => false,
            'handle_all_exception' => false,
            'override_class' => App::class,
        ]);
        App::G()->init([
            'handle_all_dev_error' => false,
            'handle_all_exception' => false,
            'use_autoloader' => true,
        ]);
////////////////////////
MyKernelTrait::OnDefaultException(new \Exception("error"));
MyKernelTrait::OnDevErrorHandler("", "", "", "");
MyKernelTrait::On404();

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
            'use_setting_file' => true,
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>'\\'.KernelTestApp::class,
        ];
        DuckPhp::G(new DuckPhp())->init($options);

        
        $options=[
            'path' => $path_app,
            'is_debug'=>false,
        ];
        KernelTestApp::RunQuickly($options);
        
        KernelTestApp::G()->options['error_404']='_sys/error-404';
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



}


namespace tests\DuckPhp\Core\Controller{
class Main
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
