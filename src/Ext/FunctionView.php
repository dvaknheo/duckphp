<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\View;

class FunctionView extends View
{
    public $prefix='view_';
    public $head_callback;
    public $foot_callback;
    
    private $callback;
    
    public function init($options=[], $context=null)
    {
        $ret=parent::init($options, $context);
        $options=$context->options;
        $this->head_callback=$options['function_view_head']??'';
        $this->foot_callback=$options['function_view_foot']??'';
        return $ret;
    }
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
        
        
        if ($this->head_callback) {
            if (is_callable($this->head_callback)) {
                ($this->head_callback)($this->data);
            }
        } else {
            if ($this->head_file) {
                $this->head_file=rtrim($this->head_file, '.php').'.php';
                include($this->path.$this->head_file);
            }
        }
        
        $this->callback=$this->prefix.str_replace('/', '__', preg_replace('/^Main\//', '', $this->view));
        if (is_callable($this->callback)) {
            ($this->callback)($this->data);
        } else {
            if (!is_file($this->view_file)) {
                //echo "DNMVCS FunctionView: Not callback {$this->callback}; not file $this->view_file";
                return;
            }
            include($this->view_file);
        }
        
        if ($this->head_callback) {
            if (is_callable($this->foot_callback)) {
                ($this->foot_callback)($this->data);
            }
        } else {
            if ($this->foot_file) {
                $this->foot_file=rtrim($this->foot_file, '.php').'.php';
                include($this->path.$this->foot_file);
            }
        }
    }
    public function _ShowBlock($view, $data=null)
    {
        $this->view=$view;
        $this->data=array_merge($this->data, $data);
        $data=null;
        $view=null;
        extract($this->data);
        
        $this->callback=$this->prefix.str_replace('/', '__', preg_replace('/^Main\//', '', $this->view));
        if (is_callable($this->callback)) {
            ($this->callback)($this->data);
        } else {
            if (!is_file($this->view_file)) {
                echo "NMVCS FunctionView ShowBlock: Not callback {$this->callback}; No file {$this->view_file}";
                return;
            }
            include($this->view_file);
        }
    }
}
