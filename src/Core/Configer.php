<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class Configer
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'path'=>null,
        'path_config'=>null,
        
        'setting'=>[],
        'all_config'=>[],
        'setting_file'=>'setting',
        'skip_setting_file'=>false,
    ];
    protected $path;
    protected $is_inited=false;
    protected $all_config=[];
    protected $setting=[];
    protected $setting_file='setting';
    protected $skip_setting_file=false;
    public function init($options=[], $context=null)
    {
        $this->path=($options['path']??'').rtrim($options['path_config'], '/').'/';
        
        $this->setting=$options['setting']??[];
        $this->all_config=$options['all_config']??[];
        $this->setting_file=$options['setting_file']??'setting';
        $this->skip_setting_file=$options['skip_setting_file']??false;
        return $this;
    }
    public function _Setting($key)
    {
        if ($this->is_inited || $this->skip_setting_file) {
            return $this->setting[$key]??null;
        }
        $this->setting=$this->loadFile($this->setting_file, false);
        if(!isset($this->setting) ){
            $full_setting_file=$this->path.$this->setting_file.'.php';
            if (!is_file($full_setting_file)) {
                echo "<h1> Class ". static::class.' Fatal: no setting file['.$full_setting_file.']!,change '.$this->setting_file.'.sample.php to '.$basename.".php !</h1>";
                echo "<h2> Or turn on  options ['skip_setting_file']</h2>";
                exit;
            }
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
        $config=$this->loadFile($file_basename, false);
        $this->all_config[$file_basename]=$config;
        return $config;
    }
    protected function loadFile($basename, $checkfile=true)
    {
        $file=$this->path.$basename.'.php';
        if ($checkfile && !is_file($file)) {
            return null;
        }
        $ret=(function ($file) {
            return include($file);
        })($file);
        return $ret;
    }
}
