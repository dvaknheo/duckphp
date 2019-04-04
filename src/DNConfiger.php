<?php
namespace DNMVCS;

class DNConfiger
{
    use DNSingleton;

    public $path;
    protected $setting_file_basename='setting';
    protected $setting=[];
    protected $all_config=[];
    protected $is_inited=false;
    public function init($path, $options)
    {
        $this->path=$path;
        $this->setting=$options['setting']??[];
        $this->all_config=$options['all_config']??[];
        $this->setting_file_basename=$options['setting_file_basename']??'setting';
    }
    public function _Setting($key)
    {
        if ($this->is_inited || !$this->setting_file_basename) {
            return $this->setting[$key]??null;
        }
        $basename=$this->setting_file_basename;
        $full_config_file=$this->path.$basename.'.php';
        if (!is_file($full_config_file)) {
            echo '<h1>'.'DNMVCS Fatal: no setting file['.$full_config_file.']!,change '.$basename.'.sample.php to '.$basename.'.php !'.'</h1>';
            exit;
        }
        $this->setting=$this->loadFile($basename, false);
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
