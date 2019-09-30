<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class AutoLoader
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
            'path'=>null,
            'namespace'=>'MY',
            'path_namespace'=>'app',
            
            'skip_system_autoload'=>true,
            'skip_app_autoload'=>false,
            
            'enable_cache_classes_in_cli'=>false,
        ];
    protected $namespace;
    protected $path_namespace;

    public $is_inited=false;
    public $namespace_paths=[];
    
    protected $is_running=false;
    protected $enable_cache_classes_in_cli=false;
    public function init($options=[], $context=null)
    {
        if ($this->is_inited) {
            return $this;
        }
        $this->is_inited=true;
        
        $options=array_merge(static::DEFAULT_OPTIONS, $options);
        
        if (!isset($options['path']) || !$options['path']) {
            $path=realpath(getcwd().'/../');
            $options['path']=$path;
        }
        $path=rtrim($options['path'], '/').'/';
        
        $this->namespace=$options['namespace'];
        //
        
        if (substr($options['path_namespace'], 0, 1)==='/') {
            $this->path_namespace=rtrim($options['path_namespace'], '/').'/';
        } else {
            $this->path_namespace=$path.rtrim($options['path_namespace'], '/').'/';
        }
        
        $this->enable_cache_classes_in_cli=$options['enable_cache_classes_in_cli'];

        if (!$options['skip_app_autoload']) {
            $this->assignPathNamespace($this->path_namespace, $this->namespace);
        }
        if (!$options['skip_system_autoload']) {
            $this->assignPathNamespace(__DIR__, __NAMESPACE__);
        }
        
        return $this;
    }
    public function run()
    {
        if ($this->is_running) {
            return;
        }
        $this->is_running=true;
        
        if ($this->enable_cache_classes_in_cli) {
            $this->cacheClasses();
        }
        spl_autoload_register([$this,'_autoload']);
    }
    public function _autoload($class)
    {
        foreach ($this->namespace_paths as $base_dir =>$prefix) {
            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                continue;
            }
            
            $relative_class = substr($class, strlen($prefix));
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            if (!file_exists($file)) {
                continue;
            }
            include $file;
            return true;
        }
        return false;
    }
    public function assignPathNamespace($input_path, $namespace=null)
    {
        if (!is_array($input_path)) {
            $pathes=[$input_path=>$namespace];
        } else {
            $pathes=$input_path;
        }
        $ret=[];
        foreach ($pathes as $path=>$namespace) {
            $path=($path==='')?$path:rtrim((string)$path, '/').'/';
            $namespace=rtrim($namespace, '\\').'\\';
            $ret[$path]=$namespace;
        }
        $this->namespace_paths=array_merge($this->namespace_paths, $ret);
    }
    public function cacheClasses()
    {
        $ret=[];
        foreach ($this->namespace_paths as $source=>$name) {
            $source=realpath($source);
            if (false===$source) {
                continue;
            }
            $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator($directory);
            $files = \iterator_to_array($iterator, false);
            $ret+=$files;
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
        $ret=[];
        $source=realpath($path);
        if (false===$source) {
            return $ret;
        }
        $directory = new \RecursiveDirectoryIterator($source, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);
        $ret+=$files;
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
    public function cleanUp()
    {
        spl_autoload_unregister([$this,'_autoload']);
    }
}
