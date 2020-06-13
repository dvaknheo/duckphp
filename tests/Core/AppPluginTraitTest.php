<?php 
namespace tests\DuckPhp\Core
{
use DuckPhp\Core\AppPluginTrait;
use DuckPhp\DuckPhp;

class AppPluginTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(AppPluginTrait::class);
        $path_app=\MyCodeCoverage::GetClassTestPath(AppPluginTrait::class);
        $options=[
            'path' =>$path_app,
            'platform' => 'BJ',
            'is_debug' => true,
            'skip_setting_file' => true,
            'override_class'=>'',
        ];
        $plugin_options=[
            'plugin_path_namespace'=>$path_app.'secondapp/',
            
            'plugin_routehook_position'=>'append-outter',
            
            'plugin_path_conifg'=>'config',
            'plugin_path_view'=>'view',
            
            'plugin_search_config'=>true,
            //'plugin_files_conifg'=>[],
        ];

        $options['ext'][AppPluginTraitApp::class]=$plugin_options;
        $options['ext'][AppPluginTraitApp2::class]=$plugin_options;
        
        DuckPhp::G(new DuckPhp())->init($options);
        \DuckPhp\Core\Route::G()->setPathInfo('/Test/second');
        DuckPhp::G()->run();

        \DuckPhp\Core\Route::G()->setPathInfo('/Test2/second');
        DuckPhp::G()->run();
        
        
        $plugin_options['plugin_path_namespace']=null;
        $plugin_options['plugin_search_config']=false;
        AppPluginTraitApp::G(new AppPluginTraitApp())->init($plugin_options,DuckPhp::G()->init($options));
        DuckPhp::G(new DuckPhp());
        
        
        \MyCodeCoverage::G()->end();
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
        $this->pluginModeBeforeRun(function(){
            var_dump("Before run!",get_class(AppPluginTraitApp::G()->pluginModeGetOldRoute()));}
        );
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