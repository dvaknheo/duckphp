<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\SingletonEx;

class View implements ComponentInterface
{
    use SingletonEx;
    public $options = [
        'path' => '',
        'path_view' => 'view',
        'path_view_override' => '',
        'skip_view_notice_error' => true,
    ];
    public $path;
    public $data = [];
    
    protected $head_file;
    protected $foot_file;
    protected $view_file;
    protected $error_reporting_old;
    protected $is_inited = false;
    public function __construct()
    {
    }
    public function init(array $options, object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        if (substr($this->options['path_view'], 0, 1) === '/') {
            $this->path = rtrim($this->options['path_view'], '/').'/';
        } else {
            $this->path = $this->options['path'].rtrim($this->options['path_view'], '/').'/';
        }
        $this->is_inited = true;
        return $this;
    }
    public function isInited():bool
    {
        return $this->is_inited;
    }
    public static function Show($data = [], $view)
    {
        return static::G()->_Show($data, $view);
    }
    public static function Display($view, $data = null)
    {
        return static::G()->_Display($view, $data);
    }
    
    public function _Show($data = [], $view)
    {
        if ($this->options['skip_view_notice_error'] ?? false) {
            $this->error_reporting_old = error_reporting();
            error_reporting($this->error_reporting_old & ~E_NOTICE);
        }
        
        $this->view_file = $this->getViewFile($this->path, $view);
        $this->head_file = $this->getViewFile($this->path, $this->head_file);
        $this->foot_file = $this->getViewFile($this->path, $this->foot_file);
        
        $this->data = array_merge($this->data, $data);
        $data = null;
        $view = null;
        extract($this->data);
        
        if ($this->head_file) {
            include $this->head_file;
        }
        
        include $this->view_file;
        
        if ($this->foot_file) {
            include $this->foot_file;
        }
        if ($this->options['skip_view_notice_error'] ?? false) {
            $this->error_reporting_old = error_reporting();
            error_reporting($this->error_reporting_old & ~E_NOTICE);
        }
    }
    public function _Display($view, $data = null)
    {
        $this->view_file = $this->getViewFile($this->path, $view);
        $this->data = isset($data)?$data:$this->data;
        $data = null;
        $view = null;
        extract($this->data);
        
        include $this->view_file;
    }

    public function setViewWrapper($head_file, $foot_file)
    {
        $this->head_file = $head_file;
        $this->foot_file = $foot_file;
    }
    public function assignViewData($key, $value = null)
    {
        if (is_array($key) && $value === null) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }
    public function setOverridePath($path)
    {
        $this->options['path_view_override'] = $path;
    }
    protected function getViewFile($path, $view)
    {
        if (empty($view)) {
            return '';
        }
        $base_file = preg_replace('/\.php$/', '', $view).'.php';
        $file = $path.$base_file;
        if (($this->options['path_view_override'] ?? false) && !is_file($file)) {
            $file = $this->options['path_view_override'].$base_file;
        }
        return $file;
    }
}
