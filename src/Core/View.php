<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class View extends ComponentBase
{
    /** @var array */
    public $options = [
        'path' => '',
        'path_view' => 'view',
        'view_skip_notice_error' => true,
    ];
    /** @var array */
    public $data = [];
    
    /** @var ?string */
    protected $head_file;
    /** @var ?string */
    protected $foot_file;
    /** @var ?string */
    protected $view_file;
    /** @var ?int */
    protected $error_reporting_old = 0;
    protected $temp_view_file;
    protected $context_class = '';

    public static function Show(array $data = [], string $view = null): void
    {
        static::_()->_Show($data, $view);
    }
    public static function Display(string $view, ?array $data = null): void
    {
        static::_()->_Display($view, $data);
    }
    public static function Render(string $view, ?array $data = null): string
    {
        return static::_()->_Render($view, $data);
    }
    
    public function _Show(array $data, string $view): void
    {
        if ($this->context_class) {
            $this->context()->onBeforeOutput();
        }
        if ($this->options['view_skip_notice_error'] ?? false) {
            $this->error_reporting_old = error_reporting();
            error_reporting($this->error_reporting_old & ~E_NOTICE);
        }
        
        $view = $this->context_class ? $this->context()->adjustViewFile($view) : $view ;
        $this->view_file = $this->getViewFile($view);
        $this->head_file = $this->getViewFile($this->head_file);
        $this->foot_file = $this->getViewFile($this->foot_file);
        
        $this->data = array_merge($this->data, $data);
        
        unset($data);
        unset($view);
        extract($this->data);
        
        if ($this->head_file) {
            include $this->head_file;
        }
        
        include $this->view_file;
        
        if ($this->foot_file) {
            include $this->foot_file;
        }
        if ($this->options['view_skip_notice_error'] ?? false) {
            $this->error_reporting_old = error_reporting();
            error_reporting($this->error_reporting_old & ~E_NOTICE);
        }
    }
    public function _Display(string $view, ?array $data = null): void
    {
        $this->temp_view_file = $this->getViewFile($view);
        $data = isset($data)?$data:$this->data;
        unset($data['this']);
        //unset($data['GLOBALS']);
        extract($data);
        
        include $this->temp_view_file;
    }
    public function _Render(string $view, ?array $data = null): string
    {
        ob_implicit_flush(0);
        ob_start();
        $this->_Display($view, $data);
        $ret = ob_get_contents();
        ob_end_clean();
        return (string)$ret;
    }
    public function reset()
    {
        $this->head_file = null;
        $this->foot_file = null;
        $this->data = [];
        $this->view_file = null;
        $this->temp_view_file = null;
        $this->error_reporting_old = null;
    }
    public function getViewData(): array
    {
        return $this->data;
    }
    public function setViewHeadFoot(?string $head_file, ?string $foot_file): void
    {
        $this->head_file = $head_file;
        $this->foot_file = $foot_file;
    }
    /**
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function assignViewData($key, $value = null): void
    {
        if (is_array($key) && $value === null) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }
    protected function getViewFile(?string $view): string
    {
        if (empty($view)) {
            return '';
        }
        $file = (substr($view, -strlen('.php')) === '.php') ? $view : $view.'.php';
        $full_file = $this->extendFullFile($this->options['path'], $this->options['path_view'], $file);
        
        return $full_file;
    }
}
