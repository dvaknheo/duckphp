<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Core;

class ComponentBase
{
    use SingletonEx;
    
    public $options=[];
    protected $is_inited = false;
    public function __construct()
    {
    }
    public function init(array $options, ?object $context = null)
    {
        $this->initOptions($options);
        if ($context !==null) {
            $this->initContext($context);
        }
        $this->is_inited = true;
        return $this;
    }
    public function isInited(): bool
    {
        return $this->is_inited;
    }
    //for override
    protected function initOptions(array $options)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
    }
    //for override
    protected function initContext(object $context)
    {
        return;
    }
}
