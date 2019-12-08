<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\AutoLoader;
use DuckPhp\Core\Configer;
use DuckPhp\Core\View;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;

trait AppPluginTrait
{
    public $plugin_options = [
        'plugin_path_namespace' => null,
        'plugin_namespace' => null,
        
        'plugin_routehook_position' => 'append-outter',
        
        'plugin_path_conifg' => 'config',
        'plugin_path_view' => 'view',
        
        'plugin_search_config' => false,
        'plugin_files_config' => [],
    ];
    protected $path_view_override = '';
    protected $path_config_override = '';
    public function initAsPlugin(array $options, object $context = null)
    {
        //override me
        return $this->defaultInitAsPlugin($options, $context);
    }
    protected function pluginModeInitOptions($options)
    {
        $this->plugin_options = array_intersect_key(array_replace_recursive($this->plugin_options, $options) ?? [], $this->plugin_options);
        $class = static::class;
        
        if (!isset($this->plugin_options['plugin_namespace']) || !isset($this->plugin_options['plugin_path_namespace'])) {
            $t = explode('\\', $class);
            $t_class = array_pop($t);
            $t_base = array_pop($t);
            $namespace = implode('\\', $t);
            if (!isset($this->plugin_options['plugin_namespace'])) {
                $this->plugin_options['plugin_namespace'] = $namespace;
            }
            if (!isset($this->plugin_options['plugin_path_namespace'])) {
                $myfile = (new \ReflectionClass($class))->getFileName();
                $path = substr($myfile, 0, -strlen($t_class) - strlen($t_base) - 6); //6='//.php';
                $this->plugin_options['plugin_path_namespace'] = $path;
            }
        }
    }
    protected function defaultInitAsPlugin(array $options, object $context = null)
    {
        $this->pluginModeInitOptions($options);
        
        $setting_file = $context->options['setting_file'] ?? 'setting';
        
        $this->path_view_override = rtrim($this->plugin_options['plugin_path_namespace'].$this->plugin_options['plugin_path_view'], '/').'/';
        $this->path_config_override = rtrim($this->plugin_options['plugin_path_namespace'].$this->plugin_options['plugin_path_conifg'], '/').'/';

        if ($this->plugin_options['plugin_search_config']) {
            $this->plugin_options['plugin_files_config'] = $this->searchAllPluginFile($this->path_config_override, $setting_file);
        }
        
        foreach ($this->plugin_options['plugin_files_config'] as $name) {
            $config_data = $this->includeFileForPluginConfig($this->path_config_override.$name.'.php');
            Configer::G()->prependConfig($name, $config_data);
        }
        Route::G()->addRouteHook([static::class,'PluginRouteHook'], $this->plugin_options['plugin_routehook_position']);
        return $this;
    }
    protected function includeFileForPluginConfig($file)
    {
        return include $file;
    }
    protected function searchAllPluginFile($path, $setting_file = '')
    {
        $setting_file = !empty($setting_file)?$path.$setting_file.'.php':'';
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

    public static function PluginRouteHook($path_info)
    {
        return static::G()->_PluginRouteHook($path_info);
    }
    public function _PluginRouteHook($path_info)
    {
        //TODO clone Helper
        $this->runAsPlugin();
        
        View::G()->setOverridePath($this->path_view_override);
        $route = new Route();
        $options = $this->options;
        $options['namespace'] = $options['namespace'] ?? $this->plugin_options['plugin_namespace'];
        $route->init($options)->bindServerData(SuperGlobal::G()->_SERVER);
        $route->path_info = $path_info;
        $flag = $route->defaultRunRouteCallback($path_info);
        return $flag;
    }
    protected function runAsPlugin()
    {
        // ovverride md;
    }
}
