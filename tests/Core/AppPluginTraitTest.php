<?php 
namespace tests\DuckPhp\Core
{
use DuckPhp\Core\AppPluginTrait;
use DuckPhp\App as DuckPhp;

class AppPluginTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(AppPluginTrait::class);
        $path_app=\GetClassTestPath(AppPluginTrait::class);

        $options=[
            'path' =>$path_app,
            'platform' => 'BJ',
            'is_debug' => true,
            'skip_setting_file' => true,
            
            'use_super_global' => true,
            'override_class'=>'\\'.AppTestApp::class,
        ];
        $plugin_options=[
            'plugin_path_namespace'=>$path_app.'secondapp/',
            
            'plugin_routehook_position'=>'append-outter',
            
            'plugin_path_conifg'=>'config',
            'plugin_path_view'=>'view',
            
            'plugin_search_config'=>true,
            //'plugin_files_conifg'=>[],
        ];
        DuckPhp::G(new DuckPhp());
        AppPluginTraitApp::G()->init($plugin_options,DuckPhp::G()->init($options));
        
        \DuckPhp\Core\Route::G()->bindServerData(\DuckPhp\Core\SuperGlobal::G()->_SERVER);
        \DuckPhp\Core\Route::G()->path_info='/second';
        DuckPhp::G()->run();
        
        $plugin_options['plugin_path_namespace']=null;
        $plugin_options['plugin_search_config']=false;
        AppPluginTraitApp::G(new AppPluginTraitApp())->init($plugin_options,DuckPhp::G()->init($options));
        var_dump(AppPluginTraitApp::G()->plugin_options['plugin_path_namespace']);
        \MyCodeCoverage::G()->end(AppPluginTrait::class);
        DuckPhp::G(new DuckPhp());
        $this->assertTrue(true);
    }
}
class AppPluginTraitApp extends DuckPhp
{
    use AppPluginTrait;
    
    public function __construct()
    {
        parent::__construct();
        $this->plugin_options['plugin_files_conifg']='config';
    }
}

}
namespace tests\DuckPhp\Core\Second\Controller
{
use DuckPhp\App as DuckPhp;
////[[[[
class Main
{
    public function second()
    {
        DuckPhp::Show(['date'=>DATE(DATE_ATOM)],'main');
    }
}
////]]]]
}