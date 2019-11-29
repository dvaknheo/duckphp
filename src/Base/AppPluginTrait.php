<?php
namespace DNMVCS\Base;

use DNMVCS\Core\App;
use DNMVCS\Core\Configer;
use DNMVCS\Core\View;
use DNMVCS\Core\Route;
use DNMVCS\Core\SuperGlobal;

trait AppPluginTrait
{
    public $plugin_options=[
        'plugin_mode'=>false,
        'plugin_path_namespace'=>null,
        'plugin_namespace'=>null,
        
        'plugin_routehook_position'=>'append-outter',
        
        'plugin_path_conifg'=>'config',
        'plugin_path_view'=>'view',
        
        'plugin_search_config'=>false,
        'plugin_files_conifg'=>[],

    ];
    protected $context_path=null;
    protected $path_view_override='';
    protected $path_config_override='';
    
    public function initAsPlugin(array $options=[],?object $context=null)
    {
        //
        $this->plugin_options=array_intersect_key(array_replace_recursive($this->plugin_options, $options)??[], $this->plugin_options);
        $this->context_path=$context->options['path'];
        $this->path_view_override  =rtrim($this->context_path .$this->plugin_options['plugin_path_namespace'].'/'.$this->plugin_options['plugin_path_view'],'/').'/';
        $this->path_config_override=rtrim($this->context_path .$this->plugin_options['plugin_path_namespace'].'/'.$this->plugin_options['plugin_path_conifg'],'/').'/';

        if($this->plugin_options['plugin_search_config']){
            $this->plugin_options['plugin_files_conifg']=$this->searchAllPluginFile($this->path_config_override);
        }
        foreach ($this->plugin_options['plugin_files_conifg'] as $name) {
            $config_data=$this->includeFileForPluginConfig($this->path_config_override.$name.'.php');
            Configer::G()->prependConfig($name,$config_data);
        }
        Route::G()->addRouteHook([static::class,'Hook'],$this->plugin_options['plugin_routehook_position']);
        //
    }
    protected function includeFileForPluginConfig($file)
    {
        return include $file;
    }
    protected function searchAllPluginFile($path,$setting_file='setting')
    {
        $flags = \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS ;
        $directory =new \RecursiveDirectoryIterator($path, $flags);
        $it = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::MATCH);
        foreach( $regex as $k =>$v){
            if($k===$setting_file){
                continue;
            }
            if(substr($k,0,-strlen('.sample.php'))==='.sample.php'){
                continue;
            }
            $k=substr($regex->getSubPathName(),0,-4);
            $ret[]=$k;
        }
        return $k;
    }

    public static function Hook($path_info)
    {
        return static::G()->_Hook($path_info);
    }
    public function _Hook($path_info)
    {
        View::G()->setOverridePath($this->path_view_override);
        
        $route=new Route();
        $options=$this->options;
        $options['namespace']=$options['namespace']??$this->plugin_options['plugin_namespace'];
        $route->init($options)->bindServerData(SuperGlobal::G()->_SERVER);       
        $flag=$route->defaultRunRouteCallback($path_info);
        return $flag;
    }
}