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
        'path_view_override' => '',
        'view_skip_notice_error' => true,
        'view_runtime' => '',
    ];
    /** @var array */
    public $data = [];
    
    /** @var ?string */
    protected $head_file;
    /** @var ?string */
    protected $foot_file;
    /** @var ?string */
    protected $view_file;
    /** @var int */
    protected $error_reporting_old;
    protected $temp_view_file;
    
    public static function Show(array $data = [], string $view = null): void
    {
        static::G()->_Show($data, $view);
    }
    public static function Display(string $view, ?array $data = null): void
    {
        static::G()->_Display($view, $data);
    }
    public static function Render(string $view, ?array $data = null): string
    {
        return static::G()->_Render($view, $data);
    }
    
    public function _Show(array $data, string $view): void
    {
        if ($this->options['view_skip_notice_error'] ?? false) {
            $this->runtime()->error_reporting_old = error_reporting();
            error_reporting($this->runtime()->error_reporting_old & ~E_NOTICE);
        }
        
        $this->runtime()->view_file = $this->getViewFile($view);
        $this->runtime()->head_file = $this->getViewFile($this->runtime()->head_file);
        $this->runtime()->foot_file = $this->getViewFile($this->runtime()->foot_file);
        
        $this->runtime()->data = array_merge($this->runtime()->data, $data);
        
        unset($data);
        unset($view);
        extract($this->runtime()->data);
        
        if ($this->runtime()->head_file) {
            include $this->runtime()->head_file;
        }
        
        include $this->runtime()->view_file;
        
        if ($this->runtime()->foot_file) {
            include $this->runtime()->foot_file;
        }
        if ($this->options['view_skip_notice_error'] ?? false) {
            $this->runtime()->error_reporting_old = error_reporting();
            error_reporting($this->runtime()->error_reporting_old & ~E_NOTICE);
        }
    }
    public function _Display(string $view, ?array $data = null): void
    {
        $this->runtime()->temp_view_file = $this->getViewFile($view);
        $data = isset($data)?$data:$this->runtime()->data;
        unset($data['this']);
        //unset($data['GLOBALS']);
        extract($data);
        
        include $this->runtime()->temp_view_file;
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
    public function runtime()
    {
        if ($this->options['view_runtime']) {
            return ($this->options['view_runtime'])();
        }
        return $this;
    }
    public function reset(): void
    {
        $runtime = $this->runtime();
        
        $runtime->head_file = null;
        $runtime->foot_file = null;
        $runtime->data = [];
        $runtime->view_file = null;
        $runtime->temp_view_file = null;
        $runtime->error_reporting_old = null;
    }
    public function getViewPath()
    {
        return $this->getComponentPathByKey('path_view');
    }
    public function getViewData(): array
    {
        return $this->runtime()->data;
    }
    public function setViewHeadFoot(?string $head_file, ?string $foot_file): void
    {
        $this->runtime()->head_file = $head_file;
        $this->runtime()->foot_file = $foot_file;
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
            $this->runtime()->data = array_merge($this->runtime()->data, $key);
        } else {
            $this->runtime()->data[$key] = $value;
        }
    }
    protected function getViewFile(?string $view): string
    {
        if (empty($view)) {
            return '';
        }
        $base_file = preg_replace('/\.php$/', '', $view).'.php';
        $path = $this->getViewPath();
        $file = $path.$base_file;
        if (($this->options['path_view_override'] ?? false) && !is_file($file)) {
            $file = $this->options['path_view_override'].$base_file;
        }
        return $file;
    }
}
