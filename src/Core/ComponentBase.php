<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Core;

use DuckPhp\Core\App;
use DuckPhp\Core\SingletonExTrait;

class ComponentBase // implements ComponentInterface
{
    use SingletonExTrait;

    public $options = [];
    protected $is_inited = false;
    protected $context_class = null;
    protected $init_once = false;
    public function __construct()
    {
    }
    protected static $_instances = [];

    public function context()
    {
        return App::_(); // this is feature
    }
    /**
     * @param array<string, mixed> $options
     * @param object|null $context
     * @return static
     * @param array<string, mixed> $options
     */
    public function init(array $options, ?object $context = null)
    {
        if ($this->init_once && $this->is_inited && !($options['__force__'] ?? false)) {
            return $this;
        }

        $this->options = array_intersect_key(array_replace_recursive($this->options, $options), $this->options);
        $this->initOptions($options);
        if ($context !== null) {
            $this->initContext($context);
        }
        $this->is_inited = true;
        return $this;
    }
    /**
     * @param array<string, mixed> $options
     */
    public function reInit(array $options, ?object $context = null)
    {
        $options['__force__'] = true;
        return $this->init($options, $context);
    }
    public function isInited(): bool
    {
        return $this->is_inited;
    }
    //for override
    /**
     * for override
     * @param array<string, mixed> $options
     */
    protected function initOptions(array $options):void
    {
    }
    //for override
    /**
     * for override
     *
     */
    protected function initContext(object $context):void
    {
        // $this->context_class = get_class($context);
    }
    //helper
    protected static function IsAbsPath($path)
    {
        $is_abs = preg_match('#^(?:/|[a-zA-Z]:[\\\\/]|\\\\{2})#', $path ?? '') > 0;
        return $is_abs;
    }
    protected static function SlashDir($path)
    {
        $path = ($path !== '') ? rtrim($path, '/\\').DIRECTORY_SEPARATOR : '';
        return $path;
    }
}
