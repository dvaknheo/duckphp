<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\View;

class CallableView extends View
{
    public $options = [
        'callable_view_head' => null,
        'callable_view_foot' => null,
        'callable_view_class' => null,
        'callable_view_prefix' => null,
        'callable_view_skip_replace' => false,
    ];
    public function __construct()
    {
        $this->options = array_replace_recursive($this->options, (new parent())->options); //merge parent's options;
        parent::__construct();
    }
    //@override
    /**
     *
     * @param array $options
     * @param object $context
     * @return $this
     */
    public function init(array $options, object $context = null)
    {
        parent::init($options, $context);
        if (!$this->options['callable_view_skip_replace']) {
            View::G(static::G());
        }
        return $this;
    }
    /**
     *
     * @param  string $func
     * @return ?callable
     */
    protected function viewToCallback($func)
    {
        $ret = null;
        $func = str_replace('/', '_', $this->options['callable_view_prefix'].$func);
        $ret = ($this->options['callable_view_class'])?[$this->options['callable_view_class'],$func]:$func;
        if (!is_callable($ret)) {
            return null;
        }
        return $ret;
    }
    //@override
    public function _Show(array $data, string $view): void
    {
        $callback = $this->viewToCallback($view);
        if (null === $callback) {
            parent::_Show($data, $view);
            return;
        }
        $header = $this->viewToCallback($this->options['callable_view_head']?:$this->head_file);
        $footer = $this->viewToCallback($this->options['callable_view_foot']?:$this->foot_file);
        if (null !== $header) {
            ($header)($data);
        }
        ($callback)($data);
        if (null !== $footer) {
            ($footer)($data);
        }
    }
    //@override
    public function _Display(string $view, ?array $data = null): void
    {
        $func = $this->viewToCallback($view);
        if (null !== $func) {
            ($func)($data);
            return;
        }
        parent::_Display($view, $data);
    }
}
