<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\Configer;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;

trait AppPluginTrait
{
    // public $plugin_options = [] => in parent
    
    protected $plugin_context_class = '';
    protected $plugin_route_old = null;
    protected $plugin_view_old = null;
    protected $plugin_view_path = null; //TODO remove
    protected $plugin_old_component_map = [];
    private $old_controller_map = [];
    /////////
    //for override
    protected function onPluginModePrepare()
    {
        ($this->plugin_context_class)::FireEvent([static::class, __FUNCTION__]);
    }
    //for override
    protected function onPluginModeInit()
    {
        ($this->plugin_context_class)::FireEvent([static::class, __FUNCTION__]);
    }
    //for override
    protected function onPluginModeBeforeRun()
    {
        ($this->plugin_context_class)::FireEvent([static::class, __FUNCTION__]);
    }
    //for override
    public function onPluginModeAfterRun()
    {
        ($this->plugin_context_class)::FireEvent([static::class, __FUNCTION__]);
    }
    public function onPluginModeException()
    {
        ($this->plugin_context_class)::FireEvent([static::class, __FUNCTION__]);
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
            
            'plugin_files_config' => [],
            
            'plugin_view_options' => [],
            'plugin_route_options' => [],
            'plugin_component_class_view' => '',
            'plugin_component_class_route' => '',
            
            'plugin_routehook_position' => 'append-outter',

            'plugin_search_config' => true,
            'plugin_injected_helper_map' => '',
            
            
            'plugin_enable_readfile' => false,
            'plugin_readfile_prefix' => '',
            
            'plugin_init_override_parent' => true,
            'plugin_init_override_to_options' => true,
            'plugin_init_regist_console' => true,
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
            $old_data = Configer::G()->options['config_ext_file_map'] ?? [];
            Configer::G()->options['config_ext_file_map'] = array_merge($old_data, $ext_config_files);
        }
        
        Route::G()->addRouteHook([static::class,'PluginModeRouteHook'], $this->plugin_options['plugin_routehook_position']);
        
        if ($this != $context && $this->plugin_options['plugin_init_override_to_options']) {
            foreach ($this->options as $k => $v) {
                if (isset($this->plugin_options[$k])) {
                    $this->options[$k] = $this->plugin_options[$k];
                }
            }
            $this->options['path'] = $this->plugin_options['plugin_path'];
            $this->options['namespace'] = $this->plugin_options['plugin_namespace'];
        }
        if ($this->plugin_options['plugin_init_override_parent']) {
            parent::G($this);
        }
        if ($this->plugin_options['plugin_init_regist_console']) {
            Console::G()->regCommandClass(static::class, $this->plugin_options['plugin_namespace']);
        }
        //clone Helper
        if ($this->plugin_options['plugin_injected_helper_map']) {
            $context->cloneHelpers($this->plugin_options['plugin_namespace'], $this->plugin_options['plugin_injected_helper_map']);
        }
        
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
        //
        $this->pluginModeReplaceDynamicComponent();
        $this->pluginModeInitDynamicComponent();
        
        $this->onPluginModeBeforeRun();
        
        $ret = false;
        try {
            $ret = Route::G()->run();
            if (!$ret && $this->plugin_options['plugin_enable_readfile']) {
                $ret = $this->pluginModeReadFile($path_info);
            }
        } catch (\Throwable $ex) {
            $this->onPluginModeException();
            ExceptionManager::CallException($ex);
            $this->onPluginModeAfterRun();
            return true;
        }
        if ($ret) {
            $this->onPluginModeAfterRun();
        }
        $this->pluginModeClear();
        return $ret;
    }
    protected function pluginModeReplaceDynamicComponent()
    {
        $this->old_controller_map = Route::G()->options['controller_class_map'];
        $classes = $this->plugin_context_class::G()->getDynamicComponentClasses();
        foreach ($classes as $class) {
            $object = $class::G();
            $this->plugin_old_component_map[$class] = $object;
            $class::G(clone $object);
        }
    }
    protected function pluginModeInitDynamicComponent()
    {
        $view_class = $this->plugin_options['plugin_component_class_view'] ? : View::class;
        $route_class = $this->plugin_options['plugin_component_class_route'] ? : Route::class;
        View::G(new $view_class());
        Route::G(new $route_class());
        
        $view_options = $this->plugin_options['plugin_view_options'];
        $view_options['path_view'] = $this->plugin_view_path;
        $view_options['path_view_override'] = $this->pluginModeGetPath('plugin_path_view');
        View::G()->init($view_options);
        
        $route_options = $this->plugin_options['plugin_route_options'];
        $route_options['namespace'] = $this->plugin_options['plugin_namespace'];
        $route_options['controller_url_prefix'] = $this->plugin_options['plugin_url_prefix'];
        $route_options['controller_class_map'] = $this->old_controller_map;
        $route_options['controller_resource_prefix'] = $this->plugin_options['plugin_readfile_prefix'];
        
        Route::G()->init($route_options);
        

        if (!empty($this->plugin_options['plugin_url_prefix'])) {
            $prefix = '/'.trim($this->plugin_options['plugin_url_prefix'], '/').'/';
            $path_info = Route::PathInfo();
            $path_info = substr($path_info, strlen($prefix));
            Route::PathInfo($path_info);
        }
    }
    protected function pluginModeReadFile($path_info)
    {
        $path_document = $this->pluginModeGetPath('plugin_path_document');
        $file = urldecode(substr($path_info, strlen($this->plugin_options['plugin_url_prefix'])));
        
        $prefix = '/'.$this->plugin_options['plugin_readfile_prefix'];
        
        if (!empty($prefix) && (substr($file, 0, strlen($prefix)) !== $prefix)) {
            return false;
        }
        if (!empty($prefix)) {
            $file = substr($file, strlen($prefix));
        }
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
        ($this->plugin_context_class)::header('Content-Type: ' . ($this->plugin_context_class)::mime_content_type($file));
        echo file_get_contents($file);
        return true;
    }
    public function pluginModeClear()
    {
        //TODO 恢复path_info
        foreach ($this->plugin_old_component_map as $class => $object) {
            $class::G($object);
        }
        $this->plugin_old_component_map = [];
    }
    /////////////////////////////
    public function pluginModeGetOldComponent($class)
    {
        return $this->plugin_old_component_map[$class] ?? null;
    }
    protected function pluginModeGetPath($path_key, $path_key_parent = 'plugin_path'): string
    {
        $main_path = $this->plugin_options[$path_key_parent];
        $sub_path = $this->plugin_options[$path_key];
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
