<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class AutoLoader
{
    public $options = [
        'path' => '',
        'namespace' => '',
        'path_namespace' => 'app',
        'skip_app_autoload' => false,
        
        'autoload_cache_in_cli' => false,
        'autoload_path_namespace_map' => [],
    ];
    protected $namespace;
    protected $path_namespace;

    public $is_inited = false;
    public $namespace_paths = [];
    
    protected $is_running = false;
    
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
    public function __construct()
    {
    }
    public function init(array $options, object $context = null)
    {
        if ($this->is_inited) {
            return $this;
        }
        $this->is_inited = true;
        
        $this->options = array_replace_recursive($this->options, $options);
        if (empty($this->options['path'])) {
            $path = realpath(getcwd().'/../');
            $this->options['path'] = $path;
        }
        $path = rtrim($this->options['path'], '/').'/';
        
        $this->namespace = $this->options['namespace'];

        if (DIRECTORY_SEPARATOR === '/') {
            if (substr($this->options['path_namespace'], 0, 1) === '/') {
                $this->path_namespace = rtrim($this->options['path_namespace'], '/').'/';
            } else {
                $this->path_namespace = $path.rtrim($this->options['path_namespace'], '/').'/';
            }
        } else { // @codeCoverageIgnoreStart
            if (substr($this->options['path_namespace'], 1, 1) === ':') {
                $this->path_namespace = rtrim($this->options['path_namespace'], '\\').'\\';
            } else {
                $this->path_namespace = $path.rtrim($this->options['path_namespace'], '\\').'\\';
            } // @codeCoverageIgnoreEnd
        }
        if (!$this->options['skip_app_autoload'] && !empty($this->namespace)) {
            $this->assignPathNamespace($this->path_namespace, $this->namespace);
        }
        
        $this->assignPathNamespace($this->options['autoload_path_namespace_map']);
        
        return $this;
    }
    public function isInited(): bool
    {
        return $this->is_inited;
    }
    public function run()
    {
        if ($this->is_running) {
            return;
        }
        $this->is_running = true;
        
        if ($this->options['autoload_cache_in_cli']) {
            $this->cacheClasses();
        }
        spl_autoload_register(self::class.'::AutoLoad'); // phpstan can't use static::class :(
    }
    public function runAutoLoader()
    {
        //proxy to run();
        return $this->run();
    }
    public static function AutoLoad(string $class): void
    {
        static::G()->_Autoload($class);
    }
    public function _Autoload(string $class):void
    {
        foreach ($this->namespace_paths as $base_dir => $prefix) {
            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                continue;
            }
            
            $relative_class = substr($class, strlen($prefix));
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (!file_exists($file)) {
                continue;
            }
            include_once $file;
            return;
        }

        return;
    }
    public function assignPathNamespace($input_path, $namespace = null)
    {
        if (!is_array($input_path)) {
            $pathes = [$input_path => $namespace];
        } else {
            $pathes = $input_path;
        }
        $ret = [];
        foreach ($pathes as $path => $namespace) {
            $path = ($path === '')?$path:rtrim((string)$path, '/').'/';
            $namespace = rtrim($namespace, '\\').'\\';
            $ret[$path] = $namespace;
        }
        $this->namespace_paths = array_merge($this->namespace_paths, $ret);
    }
    public function cacheClasses()
    {
        $ret = [];
        foreach ($this->namespace_paths as $source => $name) {
            $source = realpath($source);
            if (false === $source) {
                continue;
            }
            $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator($directory);
            $files = \iterator_to_array($iterator, false);
            $ret += $files;
        }
        foreach ($ret as $file) {
            //if (opcache_is_script_cached($file)) {
            //    continue;
            //}
            try {
                \opcache_compile_file($file);
            } catch (\Throwable $ex) { //@codeCoverageIgnore
            }
        }
        return $ret;
    }
    public function cacheNamespacePath($path)
    {
        $ret = [];
        $source = realpath($path);
        if (false === $source) {
            return $ret;
        }
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        $ret += $files;
        foreach ($ret as $file) {
            //if (opcache_is_script_cached($file)) {
            //    continue;
            //}
            try {
                \opcache_compile_file($file);
            } catch (\Throwable $ex) { //@codeCoverageIgnore
            }
        }
        return $ret;
    }
    public function clear()
    {
        spl_autoload_unregister(self::class.'::AutoLoad'); // phpstan can't use static::class :(
    }
    public static function DuckPhpSystemAutoLoader(string $class): void //@codeCoverageIgnoreStart
    {
        $prefix = 'DuckPhp\\';
        
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            return;
        }
        if (strncmp("DuckPhp\\Core\\Helper\\", $class, strlen("DuckPhp\\Core\\Helper\\")) === 0) {
            return;
        }
        if (strncmp("DuckPhp\\Core\\Core_", $class, strlen("DuckPhp\\Core\\Core_")) === 0) {
            return;
        }
        $base_dir = dirname(__DIR__).'/';
        $relative_class = substr($class, strlen($prefix));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        include_once $file;
    }//@codeCoverageIgnoreEnd
}
