<?php
namespace DNMVCS\Core;

use DNMVCS\Core\AutoLoader;
use DNMVCS\Core\Configer;
use DNMVCS\Core\View;
use DNMVCS\Core\Route;
use DNMVCS\Core\SuperGlobal;

trait AppPluginTrait
{
    public $plugin_options=[
    
        'plugin_path'=>null,
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
    public function initAsPlugin(array $options=[], ?object $context=null)
    {
        $this->plugin_options=array_intersect_key(array_replace_recursive($this->plugin_options, $options)??[], $this->plugin_options);
        //override me
        return $this->defaultInitAsPlugin($options, $context);
    }
    protected function defaultInitAsPlugin(array $options=[], ?object $context=null)
    {
        $class=static::class;
        $t=explode('\\',$class);
        $t_class=array_pop($t);
        $t_base=array_pop($t);
        $namespace=implode('\\',$t);
        
        $myfile=(new \ReflectionClass(static::class))->getFileName();
        $root=substr($myfile,0,-strlen($t_class)-strlen($t_base)-5);
        
        $this->context_path=$context->options['path'];
        $this->path_view_override  =rtrim($this->context_path .$this->plugin_options['plugin_path_namespace'].'/'.$this->plugin_options['plugin_path_view'], '/').'/';
        $this->path_config_override=rtrim($this->context_path .$this->plugin_options['plugin_path_namespace'].'/'.$this->plugin_options['plugin_path_conifg'], '/').'/';

        $setting_file=$context->options['setting_file']??'setting';
        if ($this->plugin_options['plugin_search_config']) {
            $this->plugin_options['plugin_files_conifg']=$this->searchAllPluginFile($this->path_config_override, $setting_file);
        }
        
        foreach ($this->plugin_options['plugin_files_conifg'] as $name) {
            $config_data=$this->includeFileForPluginConfig($this->path_config_override.$name.'.php');
            Configer::G()->prependConfig($name, $config_data);
        }
        Route::G()->addRouteHook([static::class,'PluginRouteHook'], $this->plugin_options['plugin_routehook_position']);
        return $this;
    }
    protected function includeFileForPluginConfig($file)
    {
        return include $file;
    }
    protected function searchAllPluginFile($path, $setting_file='')
    {
        $setting_file=!empty($setting_file)?$path.$setting_file.'.php':'';
        $flags = \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS ;
        $directory =new \RecursiveDirectoryIterator($path, $flags);
        $it = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($it, '/^.+\.php$/i', \RecursiveRegexIterator::MATCH);
        foreach ($regex as $k =>$_) {
            if ($k===$setting_file) {
                continue;
            }
            if (substr($k, -strlen('.sample.php'))==='.sample.php') {
                continue;
            }
            $k=substr($regex->getSubPathName(), 0, -4);
            $ret[]=$k;
        }
        return $ret;
    }

    public static function PluginRouteHook($path_info)
    {
        return static::G()->_PluginRouteHook($path_info);
    }
    public function _PluginRouteHook($path_info)
    {
        $this->runAsPlugin();
        View::G()->setOverridePath($this->path_view_override);
        $route=new Route();
        $options=$this->options;
        $options['namespace']=$options['namespace']??$this->plugin_options['plugin_namespace'];
        $route->init($options)->bindServerData(SuperGlobal::G()->_SERVER);
        $route->path_info=$path_info;
        $flag=$route->defaultRunRouteCallback($path_info);
        return $flag;
    }
    protected function runAsPlugin()
    {
        // ovverride md;
    }
}
