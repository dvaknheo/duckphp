<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\View;

class JsonView extends View
{
    public $options = [
        'json_view_skip_replace' => false,
        'json_view_skip_vars' => [],
    ];
    protected $context_class = null;
    public function __construct()
    {
        $this->options = array_replace_recursive($this->options, (new parent())->options); //merge parent's options;
        parent::__construct();
    }
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
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
        if (!$this->options['json_view_skip_replace']) {
            View::G(static::G());
        }
        return $this;
    }
    //@override
    public function _Show(array $data, string $view): void
    {
        foreach ($this->options['json_view_skip_vars'] as $v) {
            unset($data[$v]);
        }
        $this->context_class::ExitJson($data);
    }
    //@override
    public function _Display(string $view, ?array $data = null): void
    {
        foreach ($this->options['json_view_skip_vars'] as $v) {
            unset($data[$v]);
        }
        $this->context_class::ExitJson($data);
    }
}
