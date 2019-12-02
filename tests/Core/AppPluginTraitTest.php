<?php 
namespace tests\DNMVCS\Core
{

    use DNMVCS\Core\AppPluginTrait;
    use DNMVCS\Core\App;
    use DNMVCS\DNMVCS;

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
        
        AppPluginTraitApp::G()->init($plugin_options,DNMVCS::G()->init($options));
        
        \DNMVCS\Core\Route::G()->bindServerData(\DNMVCS\Core\SuperGlobal::G()->_SERVER);
        \DNMVCS\Core\Route::G()->path_info='/second';
        DNMVCS::G()->run();
        
        $plugin_options['plugin_path_namespace']=null;
        $plugin_options['plugin_search_config']=false;
        AppPluginTraitApp::G(new AppPluginTraitApp())->init($plugin_options,DNMVCS::G()->init($options));
        var_dump(AppPluginTraitApp::G()->plugin_options['plugin_path_namespace']);
        \MyCodeCoverage::G()->end(AppPluginTrait::class);
        $this->assertTrue(true);
    }
}
class AppPluginTraitApp extends DNMVCS
{
    use AppPluginTrait;
    
    public function __construct()
    {
        parent::__construct();
        $this->plugin_options['plugin_files_conifg']='config';
    }
}

}
namespace tests\DNMVCS\Core\Second\Controller
{
    use DNMVCS\DNMVCS;
////[[[[
class Main
{
    public function second()
    {
        DNMVCS::Show(['date'=>DATE(DATE_ATOM)],'main');
    }
}
////]]]]
}