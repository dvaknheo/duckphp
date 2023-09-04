<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class ComponentBase // implements ComponentInterface
{
    public $options = [];
    protected $is_inited = false;
    protected $context_class = '';
    protected $init_once = false;
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
        if ($this->init_once && $this->is_inited && !($options['force'] ?? false)) {
            return $this;
        }
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        $this->initOptions($options);
        if ($context !== null) {
            $this->context_class = get_class($context);
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
    public function reset()
    {
        ////for override
    }
    public function install(array $options, ?object $context = null)
    {
        //for override
    }
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
    protected function getComponentPathByKey($path_key, $path_key_parent = 'path'): string
    {
        $main_path = $this->options[$path_key_parent];
        $sub_path = $this->options[$path_key];
        return $this->getComponentPath($sub_path, $main_path);
    }
    protected function getComponentPath($sub_path, $main_path): string
    {
        $is_abs_path = false;
        if (DIRECTORY_SEPARATOR === '/') {
            //Linux
            if (substr($sub_path, 0, 1) === '/') {
                $is_abs_path = true;
            }
        } else { // @codeCoverageIgnoreStart
            // Windows
            if (preg_match('/^(([a-zA-Z]+:(\\|\/\/?))|\\\\|\/\/)/', $sub_path)) {
                $is_abs_path = true;
            }
        }   // @codeCoverageIgnoreEnd
        if ($is_abs_path) {
            return rtrim($sub_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        } else {
            return $main_path.rtrim($sub_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }
    }
}
