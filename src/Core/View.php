<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class View
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'path'=>'',
        'path_view'=>'view',
    ];
    protected $head_file;
    protected $foot_file;
    protected $view_file;
    
    public $path;
    public $data=[];
    public $view=null;
    
    
    public function _Show($data=[], $view)
    {
        $this->view=$view;
        $this->data=array_merge($this->data, $data);
        $data=null;
        $view=null;
        extract($this->data);
        
        if (isset($this->before_show_handler)) {
            ($this->before_show_handler)($data, $this->view);
        }
        $this->prepareFiles();
        
        
        if ($this->head_file) {
            include $this->path.$this->head_file;
        }
        
        include $this->view_file;
        
        if ($this->foot_file) {
            include $this->path.$this->foot_file;
        }
    }
    public function _ShowBlock($view, $data=null)
    {
        $this->view_file=$this->path.rtrim($view, '.php').'.php';
        $this->data=isset($data)?$data:$this->data;
        $data=null;
        $view=null;
        extract($this->data);
        
        include $this->view_file;
    }
    protected function prepareFiles()
    {
        $this->view_file=$this->path.rtrim($this->view, '.php').'.php';
        if ($this->head_file) {
            $this->head_file=rtrim($this->head_file, '.php').'.php';
        }
        if ($this->foot_file) {
            $this->foot_file=rtrim($this->foot_file, '.php').'.php';
        }
    }
    public function init($options=[], $context=null)
    {
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, $options);
        if (substr($options['path_view'], 0, 1)==='/') {
            $this->path=rtrim($options['path_view'], '/').'/';
        } else {
            $this->path=$options['path'].rtrim($options['path_view'], '/').'/';
        }
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
}
