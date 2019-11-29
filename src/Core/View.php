<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class View
{
    use SingletonEx;
    public $options=[
        'path'=>'',
        'path_view'=>'view',
        'override_path'=>'',
    ];
    public $path;
    public $data=[];
    
    protected $head_file;
    protected $foot_file;
    protected $view_file;
    
    public function init($options=[], $context=null)
    {
        $this->options=array_intersect_key(array_replace_recursive($this->options, $options)??[], $this->options);
        if (substr($this->options['path_view'], 0, 1)==='/') {
            $this->path=rtrim($this->options['path_view'], '/').'/';
        } else {
            $this->path=$this->options['path'].rtrim($this->options['path_view'], '/').'/';
        }
    }
    public function _Show($data=[], $view)
    {
        $this->view_file=$this->getViewFile($this->path,$view);
        $this->head_file=$this->getViewFile($this->path,$this->head_file);
        $this->foot_file=$this->getViewFile($this->path,$this->foot_file);
        
        $this->data=array_merge($this->data, $data);
        $data=null;
        $view=null;
        extract($this->data);
        
        if ($this->head_file) {
            include $this->head_file;
        }
        
        include $this->view_file;
        
        if ($this->foot_file) {
            include $this->foot_file;
        }
    }
    public function _ShowBlock($view, $data=null)
    {
        $this->view_file=$this->getViewFile($this->path, $view);
        $this->data=isset($data)?$data:$this->data;
        $data=null;
        $view=null;
        extract($this->data);
        
        include $this->view_file;
    }

    public function setViewWrapper($head_file, $foot_file)
    {
        $this->head_file=$head_file;
        $this->foot_file=$foot_file;
    }
    public function assignViewData($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->data=array_merge($this->data, $key);
        } else {
            $this->data[$key]=$value;
        }
    }
    protected function getViewFile($path, $view)
    {
        if(!$view){
            return '';
        }
        $base_file=preg_replace('/\.php$/','',$view).'.php';
        $file=$path.$base_file;
        if($this->options['override_path'] && !is_file($file)){
            $file=$this->options['override_path'].$base_file;
        }
        return $file;
    }
}
