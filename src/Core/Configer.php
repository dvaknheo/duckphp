<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class Configer
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'path'=>'',
        'path_config'=>'config',
        
        'setting'=>[],
        'all_config'=>[],
        'setting_file'=>'setting',
        'skip_setting_file'=>false,
        'skip_env_file'=>true,
    ];
    protected $base_path;
    protected $path;
    protected $is_inited=false;
    protected $all_config=[];
    protected $setting=[];
    protected $setting_file='setting';
    protected $skip_setting_file=false;
    protected $skip_env_file=true;
    public function init($options=[], $context=null)
    {
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, $options);
        $this->base_path=$options['path']??'';
        
        if (substr($options['path_config'], 0, 1)==='/') {
            $this->path=rtrim($options['path_config'], '/').'/';
        } else {
            $this->path=$options['path'].rtrim($options['path_config'], '/').'/';
        }
        $this->setting=$options['setting']??[];
        $this->all_config=$options['all_config']??[];
        $this->setting_file=$options['setting_file']??'setting';
        $this->skip_setting_file=$options['skip_setting_file']??false;
        $this->skip_setting_file=$options['skip_setting_file']??false;
        $this->skip_env_file=$options['skip_env_file']??false;
        return $this;
    }

    public function _Setting($key)
    {
        if ($this->is_inited) {
            return $this->setting[$key]??null;
        }
        if (!$this->skip_env_file) {
            $env_setting=parse_ini_file(realpath($this->base_path).'/.env');
            $env_setting=$env_setting?:[];
            $this->setting=array_merge($this->setting, $env_setting);
        }
        if (!$this->skip_setting_file) {
            $full_setting_file=$this->path.$this->setting_file.'.php';
            if (!is_file($full_setting_file)) {
                // @codeCoverageIgnoreStart
                echo "<h1> Class '". static::class."' Fatal: No setting file[ ".$full_setting_file.' ]!</h1>';
                echo '<h2>change '.$this->setting_file.'.sample.php to '. $this->setting_file.".php !</h2>"; //// @codeCoverageIgnore
                echo "<h2> Or turn on  options ['skip_setting_file']</h2>"; //
                exit;
                // @codeCoverageIgnoreEnd
            }
            $setting=$this->loadFile($full_setting_file);
            $this->setting=array_merge($this->setting, $setting);
        }
        $this->is_inited=true;
        return $this->setting[$key]??null;
    }
    
    public function _Config($key, $file_basename='config')
    {
        $config=$this->_LoadConfig($file_basename);
        return isset($config[$key])?$config[$key]:null;
    }
    public function _LoadConfig($file_basename='config')
    {
        if (isset($this->all_config[$file_basename])) {
            return $this->all_config[$file_basename];
        }
        $full_file=$this->path.$file_basename.'.php';
        $config=$this->loadFile($full_file);
        $this->all_config[$file_basename]=$config;
        return $config;
    }
    public function setConfig($name,$data)
    {
        $this->all_config[$name]=$data;
    }
    protected function loadFile($file)
    {
        return require $file;
    }
}
