<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\SingletonTrait;

class ComponentBase // implements ComponentInterface
{
    use SingletonTrait;
    
    public $options = [];
    protected $is_inited = false;
    protected $context_class = '';
    protected $init_once = false;
    public function __construct()
    {
    }
    protected static $_instances = [];

    public function context()
    {
        return App::Current();
        //return ($this->context_class)::_();
    }
    public function init(array $options, ?object $context = null)
    {
        //if ($this->is_inited && ($this->options['init_once'] ?? ($options['init_once'] ?? false))) {
        //    return $this;
        //}

        if ($this->init_once && $this->is_inited && !($options['force_new_init'] ?? false)) {
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
    //for override
    protected function initOptions(array $options)
    {
    }
    //for override
    protected function initContext(object $context)
    {
    }
    //helper
    protected static function IsAbsPath($path)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            //Linux
            if (substr($path, 0, 1) === '/') {
                return true;
            }
        } else { // @codeCoverageIgnoreStart
            // Windows
            if (preg_match('/^(([a-zA-Z]+:(\\|\/\/?))|\\\\|\/\/)/', $path)) {
                return true;
            }
        }   // @codeCoverageIgnoreEnd
        return false;
    }
    protected static function SlashDir($path)
    {
        $path = ($path !== '') ? rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR : '';
        return $path;
    }
    public function extendFullFile($path_main, $path_sub, $file, $use_override = true)
    {
        $context = $this->context();
        if ($context) {
            return $context->getOverrideableFile($path_sub, $file, $use_override);
        }
        
        if (static::IsAbsPath($file)) {
            $full_file = $file;
        } elseif (static::IsAbsPath($path_sub)) {
            $full_file = static::SlashDir($path_sub) . $file;
        } else {
            $full_file = static::SlashDir($path_main) . static::SlashDir($path_sub) . $file;
        }
        return $full_file;
    }
}
