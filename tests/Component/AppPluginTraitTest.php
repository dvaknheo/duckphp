<?php 
namespace tests\DuckPhp\Core
{
use DuckPhp\Component\AppPluginTrait;
use DuckPhp\DuckPhp;
use DuckPhp\Core\View;


class AppPluginTraitTest extends \PHPUnit\Framework\TestCase
{
    public static function onPluginModePrepare()
    {
        var_dump("onPluginModePrepare");
    }
    public static function onPluginModeInit()
    {
        var_dump("onPluginModeInit");
    }
    public static function onPluginModeBeforeRun()
    {
        var_dump("onPluginModeBeforeRun");
    }
    public static function onPluginModeAfterRun()
    {
        var_dump("onPluginModeAfterRun");
    }
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AppPluginTrait::class);
        $path_app=\LibCoverage\LibCoverage::G()->getClassTestPath(AppPluginTrait::class);
        $options=[
            'path' =>$path_app,
            'platform' => 'BJ',
            'is_debug' => true,
            'override_class'=>'',
            'cli_enable'=>false,
        ];
        $plugin_options=[
            'plugin_path'=>$path_app.'secondapp/',
            
            'plugin_routehook_position'=>'append-outter',
            
            'plugin_path_conifg'=>'config',
            'plugin_path_view'=>'view',
            
            'plugin_search_config'=>true,
            //'plugin_files_conifg'=>[],
            'plugin_injected_helper_map' => '~\\Helper\\',
        ];

        $options['ext'][AppPluginTraitApp::class]=$plugin_options;
        $options['ext'][AppPluginTraitApp2::class]=$plugin_options;

        AppPluginTraitApp::G()->onPluginModePrepare=[static::class,"onPluginModePrepare"];
        AppPluginTraitApp::G()->onPluginModeInit=[static::class,"onPluginModeInit"];
        AppPluginTraitApp::G()->onPluginModeBeforeRun=[static::class,"onPluginModeBeforeRun"];
        AppPluginTraitApp::G()->onPluginModeAfterRun= function(){ echo "onPluginModeAfterRun!";};
        AppPluginTraitApp::G()->onPluginModeException= function(){ echo "onPluginModeException!";};
        
        DuckPhp::G(new DuckPhp())->init($options);
        
        AppPluginTraitApp::G()->onPluginModeBeforeRun=function(){ echo "onBeforeRun!";};
        AppPluginTraitApp::G()->onPluginModeAfterRun=function(){ echo "onPluginModeAfterRun!";};
        
        
        $_SERVER['PATH_INFO']='/Test/second';
        DuckPhp::G()->run();
        

        AppPluginTraitApp2::G()->onPluginModeAfterRun=function(){ echo "onPluginModeAfterRun!";};
        DuckPhp::G()->run();
        
        $_SERVER['PATH_INFO']='/Test2/second';
        DuckPhp::G()->run();
        
        
        
            $_SERVER['PATH_INFO']='/Test/exception';
            DuckPhp::G()->run();
        
            $_SERVER['PATH_INFO']='/Test/exception';
            AppPluginTraitApp2::G()->onPluginModeException= function(){ echo "onPluginModeException!";};

            DuckPhp::G()->run();
      
        
        
        ////[[[[
        AppPluginTraitApp2::G()->plugin_options['plugin_enable_readfile']=true;
        AppPluginTraitApp2::G()->plugin_options['plugin_path_document']='../public';
        
        $_SERVER['PATH_INFO']='/Test/../x.html';
        DuckPhp::G()->run();
        $_SERVER['PATH_INFO']='/Test/x.php';
        DuckPhp::G()->run();
        $_SERVER['PATH_INFO']='/Test/z.html';
        DuckPhp::G()->run();
        $_SERVER['PATH_INFO']='/Test/x.html';
        DuckPhp::G()->run();
        
        AppPluginTraitApp2::G()->plugin_options['plugin_readfile_prefix']='/res';
        $_SERVER['PATH_INFO']='/Test/res/x.html';
        DuckPhp::G()->run();
        AppPluginTraitApp2::G()->plugin_options['plugin_readfile_prefix']='/rez';
            $_SERVER['PATH_INFO']='/Test/res/x.html';
        DuckPhp::G()->run();
        ////]]]]
        ////
        $plugin_options['plugin_path_namespace']=null;
        $plugin_options['plugin_search_config']=false;
        AppPluginTraitApp::G(new AppPluginTraitApp())->init($plugin_options,DuckPhp::G()->init($options));
        AppPluginTraitApp::G()->testIt();
        AppPluginTraitApp::G()->testIt2();
        AppPluginTraitApp::G()->testIt3();

        /*
        $plugin_options=[
            'plugin_path'=>'~/',
        ];
        $options['ext']=[];
        $options['ext'][AppPluginTraitApp3::class]=$plugin_options;
        $_SERVER['PATH_INFO']='/second';
        DuckPhp::G(new DuckPhp())->init($options)->run();
        */
        \LibCoverage\LibCoverage::End();
    }
}
class AppPluginTraitApp extends DuckPhp
{
    use AppPluginTrait;
    public $componentClassMap = [
        'A' => 'AppHelper',
    ];
    public function __construct()
    {
        parent::__construct();
        $this->plugin_options['plugin_files_conifg']='config';
        $this->pluginModeGetOldComponent(View::class);
        $this->onPluginModeBeforeRun = function(){
            // ??? not hit ?
            //*
            var_dump("Before run!",get_class(AppPluginTraitApp::G()->pluginModeGetOldComponent(View::class)));
            $this->onPluginModeAfterRun=function(){ echo "onRun!";};
            //var_dump($this->onPluginModeAfterRun);
            //*/
        };
            
        
    }
    public function testIt()
    {
        $this->plugin_options['plugin_path_document']='/test';
        $this->pluginModeGetPath('plugin_path_document');
    }
    public function testIt2()
    {
        $this->plugin_options['plugin_path']='~';
        $this->pluginModeInit([],$this);
    }
    public function testIt3()
    {
        $this->plugin_options['plugin_path']='';
        $this->pluginModeInit([],$this);
    }
        
}
class AppHelper
{
    public static function Foo()
    {
        var_dump("AppHelper OK");
    }

}
class AppPluginTraitApp2 extends DuckPhp
{
    use AppPluginTrait;
    public $plugin_options=[
        'plugin_url_prefix'=>'/Test',
    ];
    public function __construct()
    {
        parent::__construct();
    }
}
class AppPluginTraitApp3 extends DuckPhp
{
    use AppPluginTrait;

    public function __construct()
    {
        parent::__construct();
    }
}


}
namespace tests\DuckPhp\Controller
{
use DuckPhp\DuckPhp;
////[[[[
class Main
{
    public function second()
    {
        //DuckPhp::Foo
        //\DuckPhp\Helper\AppHelper::Foo();
        $x=__url("z");
        DuckPhp::Show(['date'=>DATE(DATE_ATOM)],'main');
    }
    public function exception()
    {
        throw new \Exception("zzzzzzzzzzzzz");
    }
}
////]]]]
}