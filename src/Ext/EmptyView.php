<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\View;

class EmptyView extends View
{
    public $options = [
        'empty_view_key_view' => 'view',
        'empty_view_key_skip_head_foot' => 'skip_head_foot',
        'empty_view_key_wellcome_class' => 'Main/',
        'empty_view_trim_view_wellcome' => true,
        'empty_view_skip_replace' => false,
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
        if (!$this->options['empty_view_skip_replace']) {
            View::G(static::G());
        }
        return $this;
    }
    //@override
    public function _Show(array $data, string $view): void
    {
        $this->data = array_merge($this->data, $data);
        if ($this->options['empty_view_trim_view_wellcome'] ?? true) {
            $prefix = $this->options['empty_view_key_wellcome_class'] ?? 'Main/';
            if (substr($view, 0, strlen($prefix)) === $prefix) {
                $view = substr($view, strlen($prefix));
            }
        }
        $this->data[$this->options['empty_view_key_view']] = $view;
    }
    //@override
    public function _Display(string $view, ?array $data = null): void
    {
        $this->data = isset($data)?$data:$this->data;
        $this->data[$this->options['empty_view_key_skip_head_foot'] ?? 'skip_head_foot'] = true;
        $this->data[$this->options['empty_view_key_view'] ?? 'view'] = $view;
    }
}
