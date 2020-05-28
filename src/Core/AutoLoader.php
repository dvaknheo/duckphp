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
            'namespace' => 'MY',
            'path_namespace' => 'app',
            
            'skip_system_autoload' => true,
            'skip_app_autoload' => false,
            
            'enable_cache_classes_in_cli' => false,
        ];
    protected $namespace;
    protected $path_namespace;

    public $is_inited = false;
    public $namespace_paths = [];
    
    protected $is_running = false;
    protected $enable_cache_classes_in_cli = false;
    
    protected static $_instances = [];
    //embed
    public static function G($object = null)
    {
        if (defined('__SINGLETONEX_REPALACER')) {
            $callback = __SINGLETONEX_REPALACER;
            return ($callback)(static::class, $object);
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
        
        $this->options = array_merge($this->options, $options);
        if (empty($this->options['path'])) {
            $path = realpath(getcwd().'/../');
            $this->options['path'] = $path;
        }
        $path = rtrim($this->options['path'], '/').'/';
        
        $this->namespace = $this->options['namespace'];
        
        if (substr($this->options['path_namespace'], 0, 1) === '/') {
            $this->path_namespace = rtrim($this->options['path_namespace'], '/').'/';
        } else {
            $this->path_namespace = $path.rtrim($this->options['path_namespace'], '/').'/';
        }
        
        $this->enable_cache_classes_in_cli = $this->options['enable_cache_classes_in_cli'];

        if (!$this->options['skip_app_autoload']) {
            $this->assignPathNamespace($this->path_namespace, $this->namespace);
        }
        if (!$this->options['skip_system_autoload']) {
            $this->assignPathNamespace(__DIR__, __NAMESPACE__); //TODO
        }
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
        
        if ($this->enable_cache_classes_in_cli) {
            $this->cacheClasses();
        }
        spl_autoload_register([$this, 'AutoLoad']);
    }
    public static function AutoLoad($class)
    {
        return static::G()->_autoload($class);
    }
    public function _autoload($class)
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
            return true;
        }
        return false;
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
                opcache_compile_file($file);
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
                opcache_compile_file($file);
            } catch (\Throwable $ex) { //@codeCoverageIgnore
            }
        }
        return $ret;
    }
    public function clear()
    {
        spl_autoload_unregister([$this,'AutoLoad']);
    }
}
