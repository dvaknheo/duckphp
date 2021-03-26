<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class ComponentBase implements ComponentInterface
{
    public $options = [];
    protected $is_inited = false;
    public function __construct()
    {
    }
    protected static $_instances = [];
    //embed
    public static function G($object = null)
    {
        if (defined('__SINGLETONEX_REPALACER')) {
            return (__SINGLETONEX_REPALACER)(static::class, $object);
        }
        if ($object) {
            self::$_instances[static::class] = $object;
            return $object;
        }
        $me = self::$_instances[static::class] ?? null;
        if (null === $me) {
            $me = new static();
            self::$_instances[static::class] = $me;
        }
        
        return $me;
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
    /*
    public function checkInstall($context)
    {
        if($this->isInited){
            return;
        }
        $this->init($context->options,$context);
    }
    */
    //for override
    protected function initOptions(array $options)
    {
    }
    //for override
    protected function initContext(object $context)
    {
    }
    //helper
    protected function getComponenetPathByKey($path_key, $path_key_parent = 'path'): string
    {
        if (DIRECTORY_SEPARATOR === '/') {
            if (substr($this->options[$path_key], 0, 1) === '/') {
                return rtrim($this->options[$path_key], '/').'/';
            } else {
                return $this->options[$path_key_parent].rtrim($this->options[$path_key], '/').'/';
            }
        } else { // @codeCoverageIgnoreStart
            if (substr($this->options[$path_key], 1, 1) === ':') {
                return rtrim($this->options[$path_key], '\\').'\\';
            } else {
                return $this->options[$path_key_parent].rtrim($this->options[$path_key], '\\').'\\';
            } // @codeCoverageIgnoreEnd
        }
    }
}
