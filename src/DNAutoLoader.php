<?php
namespace DNMVCS;

class DNAutoLoader
{
    use DNSingleton;
    const DEFAULT_OPTIONS=[
            'path'=>null,
            'namespace'=>'MY',
            'path_namespace'=>'app',
            
            'skip_system_autoload'=>true,
            'skip_app_autoload'=>false,
        ];
    protected $namespace;
    protected $path_namespace;

    public $namespace_paths=[];
    
    protected $is_inited=false;
    protected $is_running=false;
    public function init($options=[], $context=null)
    {
        if ($this->is_inited) {
            return $this;
        }
        $this->is_inited=true;
        
        $options=array_intersect_key(array_merge(static::DEFAULT_OPTIONS, $options), static::DEFAULT_OPTIONS);
        
        if (!isset($options['path']) || !$options['path']) {
            $path=realpath(getcwd().'/../');
            $options['path']=$path;
        }
        $path=rtrim($options['path'], '/').'/';
        
        $this->namespace=$options['namespace'];
        $this->path_namespace=$path.rtrim($options['path_namespace'], '/').'/';
        
        if (!$options['skip_app_autoload']) {
            $this->assignPathNamespace($this->path_namespace, $this->namespace);
        }
        if (!$options['skip_system_autoload']) {
            $this->assignPathNamespace(__DIR__, 'DNMVCS');
        }
        
        return $this;
    }
    public function run()
    {
        if ($this->is_running) {
            return;
        }
        $this->is_running=true;
        spl_autoload_register([$this,'_autoload']);
    }
    public function _autoload($class)
    {
        foreach ($this->namespace_paths as $base_dir =>$prefix) {
            if ($prefix!=='') {
                $prefix .='\\';
            }
            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                continue;
            }
            
            $relative_class = substr($class, strlen($prefix));
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            if (!file_exists($file)) {
                continue;
            }
            require $file;
            return true;
        }
        return false;
    }
    public function assignPathNamespace($path, $namespace=null)
    {
        if (is_array($path)&& $namespace===null) {
            foreach ($path as $k=>$v) {
                $path[$k]=($v==='')?$v:rtrim($v, '/').'/';
            }
            $this->namespace_paths=array_merge($this->paths, $path);
        } else {
            $path=($path==='')?$path:rtrim($path, '/').'/';
            $this->namespace_paths[$path]=$namespace;
        }
    }
    public function cacheClasses()
    {
        $ret=[];
        foreach ($this->namespace_paths as $source=>$name) {
            if ($name===__NAMESPACE__) {
                continue;
            }
            $source=realpath($source);
            $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator($directory);
            $files = \iterator_to_array($iterator, false);
            $ret+=$files;
        }
        foreach ($ret as $file) {
            if (opcache_is_script_cached($file)) {
                continue;
            }
            @opcache_compile_file($file);
        }
        return $ret;
    }
}
