<?php 
namespace tests\DuckPhp\Core
{
use DuckPhp\Component\AppPluginTrait;
use DuckPhp\DuckPhp;

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
    public static function onPluginModeRun()
    {
        var_dump("onPluginModeRun");
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
            'plugin_path_namespace'=>$path_app.'secondapp/',
            
            'plugin_routehook_position'=>'append-outter',
            
            'plugin_path_conifg'=>'config',
            'plugin_path_view'=>'view',
            
            'plugin_search_config'=>true,
            //'plugin_files_conifg'=>[],
            'plugin_injected_helper_map' => '~\\Helper\\',
        ];

        $options['ext'][AppPluginTraitApp::class]=$plugin_options;
        $options['ext'][AppPluginTraitApp2::class]=$plugin_options;

        AppPluginTraitApp::G()->onPluginModePrepare=[static::class,"onPluginModePrepare"];// function(){ echo "onPrepare!";};
        AppPluginTraitApp::G()->onPluginModeInit=[static::class,"onPluginModeInit"];// function(){ echo "onPrepare!";};
        AppPluginTraitApp::G()->onPluginModeBeforeRun=[static::class,"onPluginModeBeforeRun"];// function(){ echo "onPrepare!";};
        //AppPluginTraitApp::G()->onPluginModeRun=;// function(){ echo "onPrepare!";};
        AppPluginTraitApp2::G()->onPluginModeRun=[static::class,"onPluginModeRun"];

        DuckPhp::G(new DuckPhp())->init($options);
        
        AppPluginTraitApp::G()->onPluginModeBeforeRun=function(){ echo "onBeforeRun!";};
        AppPluginTraitApp::G()->onPluginModeRun=function(){ echo "onPluginModeRun!";};
        
        
        
        $_SERVER['PATH_INFO']='/Test/second';
        DuckPhp::G()->run();
        AppPluginTraitApp2::G()->onPluginModeRun=null;
        DuckPhp::G()->run();
        
        $_SERVER['PATH_INFO']='/Test2/second';
        DuckPhp::G()->run();
        
        
        $plugin_options['plugin_path_namespace']=null;
        $plugin_options['plugin_search_config']=false;
        AppPluginTraitApp::G(new AppPluginTraitApp())->init($plugin_options,DuckPhp::G()->init($options));

        DuckPhp::G(new DuckPhp());
        
        
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
        $this->pluginModeGetOldRoute();
        $this->pluginModeGetOldView();
        $this->onPluginModeBeforeRun = function(){
                // ??? not hit ?
                var_dump("Before run!",get_class(AppPluginTraitApp::G()->pluginModeGetOldRoute()));
                $this->onPluginModeRun=function(){ echo "onRun!";};
                //var_dump($this->onPluginModeRun);
            };
            
        
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
        'plugin_url_prefix'=>'Test',
    ];
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
}
////]]]]
}