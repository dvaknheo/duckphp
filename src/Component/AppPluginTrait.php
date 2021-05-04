<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\Configer;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;

trait AppPluginTrait
{
    // public $plugin_options = [] => in parent
    
    public $onPluginModePrepare;
    public $onPluginModeInit;
    public $onPluginModeBeforeRun;
    public $onPluginModeAfterRun;
    
    protected $plugin_context_class = '';
    protected $plugin_route_old = null;
    protected $plugin_view_old = null;
    protected $plugin_view_path = null; //TODO remove
    
    /////////
    //for override
    protected function onPluginModePrepare()
    {
        if ($this->onPluginModePrepare) {
            return ($this->onPluginModePrepare)();
        }
    }
    //for override
    protected function onPluginModeInit()
    {
        if ($this->onPluginModeInit) {
            return ($this->onPluginModeInit)();
        }
    }
    //for override
    protected function onPluginModeBeforeRun()
    {
        if ($this->onPluginModeBeforeRun) {
            return ($this->onPluginModeBeforeRun)();
        }
    }
    //for override
    public function onPluginModeAfterRun()
    {
        if ($this->onPluginModeAfterRun) {
            return ($this->onPluginModeAfterRun)();
        }
    }
    //callback
    public static function PluginModeRouteHook($path_info)
    {
        return static::G()->_PluginModeRouteHook($path_info);
    }
    ////////
    public function pluginModeInit(array $plugin_options, object $context = null)
    {
        $plugin_options_default = [
            'plugin_path' => null,
            'plugin_namespace' => null,
            
            'plugin_url_prefix' => '',
            
            'plugin_path_conifg' => 'config',
            'plugin_path_view' => 'view',
            'plugin_path_document' => 'public',
            
            'plugin_routehook_position' => 'append-outter',
            
            'plugin_files_config' => [],
            
            'plugin_view_options' => [],
            'plugin_route_options' => [],
            'plugin_component_class_view' => '',
            'plugin_component_class_route' => '',
            
            'plugin_enable_readfile' => false,
            'plugin_search_config' => true,
            'plugin_use_singletonex_route' => true,
            'plugin_injected_helper_map' => '',
        ];
        $this->plugin_options = $this->plugin_options ?? [];
        $this->plugin_options = array_replace_recursive($plugin_options_default, $this->plugin_options, $plugin_options ?? []);
        $this->plugin_context_class = get_class($context);
        
        $this->pluginModeInitBasePath();
        $this->onPluginModePrepare();
        
        //View , Configer;
        $this->plugin_view_path = View::G()->getViewPath() . str_replace('\\', DIRECTORY_SEPARATOR, $this->plugin_options['plugin_namespace']).DIRECTORY_SEPARATOR;
        $setting_file = $context->options['setting_file'] ?? 'setting';
        $ext_config_files = $this->pluginModeInitConfigFiles($setting_file);
        if (!empty($ext_config_files)) {
            $old_data = Configer::G()->$options['config_ext_file_map'] ?? [];
            Configer::G()->$options['config_ext_file_map'] = array_merge($old_data, $ext_config_files);
        }
        
        //clone Helper
        if ($this->plugin_options['plugin_injected_helper_map']) {
            $this->plugin_context_class::G()->cloneHelpers($this->plugin_options['plugin_namespace'], $this->plugin_options['plugin_injected_helper_map']);
        }
        
        Route::G()->addRouteHook([static::class,'PluginModeRouteHook'], $this->plugin_options['plugin_routehook_position']);
        
        $this->onPluginModeInit();
        
        return $this;
    }
    protected function pluginModeInitBasePath()
    {
        if (empty($this->plugin_options['plugin_namespace']) || empty($this->plugin_options['plugin_path'])) {
            $class = static::class;
            $t = explode('\\', $class);
            $t_class = array_pop($t);
            $t_base = array_pop($t);
            $namespace = implode('\\', $t);
            if (empty($this->plugin_options['plugin_namespace'])) {
                $this->plugin_options['plugin_namespace'] = $namespace;
            }
            if (empty($this->plugin_options['plugin_path'])) {
                $file = (new \ReflectionClass($class))->getFileName();
                $this->plugin_options['plugin_path'] = realpath(dirname(dirname(dirname($file)))).DIRECTORY_SEPARATOR;
            }
        }
        if (false !== strpos($this->plugin_options['plugin_path'], '~')) {
            $file = (new \ReflectionClass($this->plugin_context_class))->getFileName();
            $dir = dirname($file);
            $this->plugin_options['plugin_path'] = str_replace('~', $dir, $this->plugin_options['plugin_path']);
        }
    }
    protected function pluginModeInitConfigFiles($setting_file)
    {
        $path_config_override = $this->pluginModeGetPath('plugin_path_conifg');
        if ($this->plugin_options['plugin_search_config']) {
            $this->plugin_options['plugin_files_config'] = $this->pluginModeSearchAllPluginFile($path_config_override, $setting_file);
        }
        
        $ret = [];
        foreach ($this->plugin_options['plugin_files_config'] as $name) {
            $file = $path_config_override.$name.'.php';
            $ret[$name] = $file;
        }
        return $ret;
    }
    protected function pluginModeSearchAllPluginFile($path, $setting_file = '')
    {
        $ret = [];
        $setting_file = !empty($setting_file) ? $path.$setting_file . '.php' : '';
        $flags = \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS ;
        $directory = new \RecursiveDirectoryIterator($path, $flags);
        $it = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($it, '/^.+\.php$/i', \RecursiveRegexIterator::MATCH);
        foreach ($regex as $k => $_) {
            if ($k === $setting_file) {
                continue;
            }
            if (substr($k, -strlen('.sample.php')) === '.sample.php') {
                continue;
            }
            $k = substr($regex->getSubPathName(), 0, -4);
            $ret[] = $k;
        }
        return $ret;
    }
    //////////////
    private function pluginModeCheckPathInfo($path_info)
    {
        $flag = $this->plugin_options['plugin_url_prefix'] ?? false;
        if (!$flag) {
            return true;
        }
        $prefix = '/'.trim($this->plugin_options['plugin_url_prefix'], '/').'/';
        $l = strlen($prefix);
        if (substr($path_info, 0, $l) !== $prefix) {
            return false;
        }
        return true;
    }
    //callback
    protected function _PluginModeRouteHook($path_info)
    {
        $flag = $this->pluginModeCheckPathInfo($path_info);
        if (!$flag) {
            return false;
        }
        $this->pluginModeReplaceDynamicComponent();
        $this->pluginModeInitDynamicComponent();
        
        $this->onPluginModeBeforeRun();
        
        $ret = Route::G()->run();
        if (!$ret && $this->plugin_options['plugin_enable_readfile']) {
            $ret = $this->pluginModeReadFile($path_info);
        }
        if (!$ret) {
            $this->pluginModeClear();
            return false;
        }
        $this->onPluginModeAfterRun();
        $this->pluginModeClear();
        return $ret;
    }
    protected function pluginModeReplaceDynamicComponent()
    {
        $this->plugin_view_old = View::G();
        $this->plugin_route_old = Route::G();
        
        $view_class = $this->plugin_options['plugin_component_class_view'] ? : View::class;
        $route_class = $this->plugin_options['plugin_component_class_route'] ? : Route::class;
        View::G(new $view_class());
        Route::G(new $route_class());
    }
    protected function pluginModeInitDynamicComponent()
    {
        $view_options = $this->plugin_options['plugin_view_options'];
        $view_options['path_view'] = $this->plugin_view_path;
        $view_options['path_view_override'] = $this->pluginModeGetPath('plugin_path_view');
        View::G()->init($view_options);
        
        $route_options = $this->plugin_options['plugin_route_options'];
        $route_options['namespace'] = $this->plugin_options['plugin_namespace'];
        $route_options['controller_path_prefix'] = $this->plugin_options['plugin_url_prefix'];
        $route_options['controller_use_singletonex'] = $this->plugin_options['plugin_use_singletonex_route'];
        Route::G()->init($route_options);
    }
    protected function pluginModeReadFile($path_info)
    {
        $path_document = $this->pluginModeGetPath('plugin_path_document');
        $file = urldecode(substr($path_info, strlen($this->plugin_options['plugin_url_prefix'])));
        if (false !== strpos($file, '../')) {
            return false;
        }
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            return false;
        }
        $file = $path_document.$file;
        if (!is_file($file)) {
            return false;
        }
        ($this->plugin_context_class)::header('Content-Type: '.mime_content_type($file)); // :(
        echo file_get_contents($file);
        return true;
    }
    public function pluginModeClear()
    {
        View::G($this->plugin_view_old);
        Route::G($this->plugin_route_old);
        $this->plugin_view_old = null;
        $this->plugin_route_old = null;
    }
    /////////////////////////////
    public function pluginModeGetOldRoute()
    {
        return $this->plugin_route_old;
    }
    public function pluginModeGetOldView()
    {
        return $this->plugin_route_old;
    }
    private function pluginModeGetPath($path_key, $path_key_parent = 'plugin_path'): string
    {
        if (DIRECTORY_SEPARATOR === '/') {
            if (substr($this->plugin_options[$path_key], 0, 1) === '/') {
                return rtrim($this->plugin_options[$path_key], '/').'/';
            } else {
                return $this->plugin_options[$path_key_parent].rtrim($this->plugin_options[$path_key], '/').'/';
            }
        } else { // @codeCoverageIgnoreStart
            if (substr($this->plugin_options[$path_key], 1, 1) === ':') {
                return rtrim($this->plugin_options[$path_key], '\\').'\\';
            } else {
                return $this->plugin_options[$path_key_parent].rtrim($this->plugin_options[$path_key], '\\').'\\';
            } // @codeCoverageIgnoreEnd
        }
    }
}
