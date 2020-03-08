<?php
namespace tests\DuckPhp\Core{

use DuckPhp\Core\App;
use DuckPhp\Core\Kernel;
use DuckPhp\App as DuckPhp;
use DuckPhp\Core\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonEx;
use DuckPhp\Ext\Pager;

class KernelTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Kernel::class);
    
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
            'reload_for_flags' => false,
            'error_exception' => NULL,
            'error_500' => NULL,
            'error_404' => NULL,
            'error_debug' => NULL,
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

        $this->do404();
        

        
        $xfunc=function () {
            var_dump("changed");
            return true;
        };
        App::G()->replaceDefaultRunHandler($xfunc);
        App::G()->run();
        
        $this->doFixPathinfo();

        
    \MyCodeCoverage::G()->end(Kernel::class);
    $this->assertTrue(true);
    return;

    }
    protected function doFixPathinfo()
    {
        KernelTestApp::G()->init([]);
        $serverData=[
        ];
        KernelTestApp::G()->fixPathInfo($serverData);
        
        $serverData=[
            'PATH_INFO'=>'abc',
        ];
        KernelTestApp::G()->fixPathInfo($serverData);
        $serverData=[
            'REQUEST_URI'=>'/',
            'SCRIPT_FILENAME'=>__DIR__ . '/index.php',
            'DOCUMENT_ROOT'=>__DIR__,
        ];
        
        KernelTestApp::G()->fixPathInfo($serverData);
        
        $serverData=[
            'REQUEST_URI'=>'/abc/d',
            'SCRIPT_FILENAME'=>__FILE__,
            'DOCUMENT_ROOT'=>__DIR__,
        ];
        KernelTestApp::G()->fixPathInfo($serverData);
        
        
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
            'reload_for_flags' => true,
            
            'skip_view_notice_error' => true,
            'use_super_global' => true,
            'override_class'=>'\\'.KernelTestApp::class,
        ];
        DuckPhp::G(new DuckPhp())->init($options);

        
        $options=[
            'path' => $path_app,
            'skip_setting_file' => true,
            'is_debug'=>false,
        ];
        KernelTestApp::RunQuickly($options);
        
        KernelTestApp::G()->options['error_404']='_sys/error-404';
        KernelTestApp::On404();        
        KernelTestApp2::RunQuickly([]);
    }
    
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
    use SingletonEx;

    public static function Foo()
    {
        return "OK";
    }
}
class KernelTestObjectA
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
class KernelTestObjectB
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
