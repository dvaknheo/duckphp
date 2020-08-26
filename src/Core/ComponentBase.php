<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class ComponentBase implements ComponentInterface
{
    use SingletonEx;
    
    public $options = [];
    protected $is_inited = false;
    public function __construct()
    {
    }
    public function init(array $options, ?object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        $this->initOptions($options);
        if ($context !== null) {
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
    }
    //for override
    protected function initContext(object $context)
    {
    }
    //helper
    protected function getComponenetPathByKey($path_key)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            if (substr($this->options[$path_key], 0, 1) === '/') {
                return rtrim($this->options[$path_key], '/').'/';
            } else {
                return $this->options['path'].rtrim($this->options[$path_key], '/').'/';
            }
        } else { // @codeCoverageIgnoreStart
            if (substr($this->options[$path_key], 1, 1) === ':') {
                return rtrim($this->options[$path_key], '\\').'\\';
            } else {
                return $this->options['path'].rtrim($this->options[$path_key], '\\').'\\';
            }
        } // @codeCoverageIgnoreStart
    }
}
