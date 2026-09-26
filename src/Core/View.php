<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class View extends ComponentBase
{
    /** @var array<string, mixed> */
    public $options = [
        'path' => '',
        'path_view' => 'view',
        'view_skip_notice_error' => true,
    ];
    /** @var array<string, mixed> */
    public $data = [];

    /** @var ?string */
    protected $header_file;
    /** @var ?string */
    protected $footer_file;
    /** @var ?string */
    protected $view_file;
    /** @var ?int */
    protected $error_reporting_old = 0;
    protected $temp_view_file;
    protected $context_class = '';

    /**
     * @param array<string, mixed> $data
     */
    public static function Show(array $data = [], ?string $view = null)
    {
        static::_()->_Show($data, $view);
    }
    /**
     * @param array<string, mixed> $data
     */
    public static function Display(string $view, ?array $data = null): void
    {
        static::_()->_Display($view, $data);
    }
    /**
     * @param array<string, mixed> $data
     */
    public static function Render(string $view, ?array $data = null): string
    {
        return static::_()->_Render($view, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function _Show(array $data, string $view)
    {
        if ($this->options['view_skip_notice_error'] ?? false) {
            $this->error_reporting_old = error_reporting();
            error_reporting($this->error_reporting_old & ~E_NOTICE);
        }

        $this->view_file = $this->getViewFile($view);
        $this->header_file = $this->getViewFile($this->header_file);
        $this->footer_file = $this->getViewFile($this->footer_file);

        $this->data = array_merge($this->data, $data);

        unset($data);
        unset($view);
        extract($this->data);

        if ($this->header_file) {
            include $this->header_file;
        }

        include $this->view_file;

        if ($this->footer_file) {
            include $this->footer_file;
        }
        if ($this->options['view_skip_notice_error'] ?? false) {
            $this->error_reporting_old = error_reporting();
            error_reporting($this->error_reporting_old & ~E_NOTICE);
        }
    }
    /**
     * @param array<string, mixed> $data
     */
    public function _Display(string $view, ?array $data = null): void
    {
        $this->temp_view_file = $this->getViewFile($view);
        $data = isset($data)?$data:$this->data;
        unset($data['this']);
        //unset($data['GLOBALS']);
        extract($data);

        include $this->temp_view_file;
    }
    /**
     * @param array<string, mixed> $data
     */
    public function _Render(string $view, ?array $data = null): string
    {
        // @phpstan-ignore-next-line
        ob_implicit_flush(PHP_VERSION_ID < 80000 ? 0 : false);
        ob_start();
        $this->_Display($view, $data);
        $ret = ob_get_contents();
        ob_end_clean();
        return (string)$ret;
    }
    public function reset()
    {
        $this->header_file = null;
        $this->footer_file = null;
        $this->data = [];
        $this->view_file = null;
        $this->temp_view_file = null;
        $this->error_reporting_old = null;
    }
    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        return $this->data;
    }
    public function setViewHeaderFooter(?string $header_file, ?string $footer_file): void
    {
        $this->header_file = $header_file;
        $this->footer_file = $footer_file;
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

        $full_file = $this->context()->getOverrideableFile($this->options['path_view'], $file);

        return $full_file;
    }
}
