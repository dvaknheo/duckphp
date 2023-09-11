<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class Configer extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_config' => 'config',
        
        'setting' => [],
        'all_config' => [],
        
        'setting_file' => 'setting.php',
        'setting_file_ignore_exists' => true,
        'setting_file_enable' => true,
        'path_config_override' => '',

        'use_env_file' => false,
        'path_config_override_from' => null,
    ];
    protected $all_config = [];
    protected $setting = [];
    
    //@override
    protected function initOptions(array $options)
    {
        $this->setting = $this->options['setting'] ?? [];
        $this->all_config = $this->options['all_config'] ?? [];
        
        if ($this->options['use_env_file']) {
            $env_setting = parse_ini_file(realpath($this->options['path']).'/.env');
            $env_setting = $env_setting?:[];
            $this->setting = array_merge($this->setting, $env_setting);
        }
        if ($this->options['setting_file_enable']) {
            $setting_file = $this->options['setting_file'];
            $path = parent::getComponentPathByKey('path_config');
            $full_setting_file = $this->getAbsPath($path, $setting_file);
            if (!is_file($full_setting_file)) {
                $this->exitWhenNoSettingFile($full_setting_file, $setting_file);
            } else {
                $setting = $this->loadFile($full_setting_file);
                $this->setting = array_merge($this->setting, $setting);
            }
        }
    }
    public function _Setting($key)
    {
        return $this->setting[$key] ?? null;
    }
    protected function exitWhenNoSettingFile($full_setting_file, $setting_file)
    {
        if ($this->options['setting_file_ignore_exists']) {
            return;
        }
        throw new \ErrorException('DuckPhp: no Setting File');
    }
    
    public function _Config($key = null, $default = null, $file_basename = 'config')
    {
        //TODO $filename_basename = '';
        $config = $this->_LoadConfig($file_basename);
        if (!isset($key)) {
            return $config ?? $default;
        }
        return isset($config[$key])?$config[$key]:$default;
    }
    protected function _LoadConfig($file_basename = 'config')
    {
        if (isset($this->all_config[$file_basename])) {
            return $this->all_config[$file_basename];
        }
        $path = parent::getComponentPathByKey('path_config');
        $full_file = $path.$file_basename.'.php';
        if (isset($this->options['path_config_override_from']) && !is_file($full_file)) {
            $file = $this->options['path_config_override_from'].$file_basename.'.php';
            if (is_file($file)) {
                $full_file = $file;
            }
        }
        $config = $this->loadFile($full_file);
        
        $this->all_config[$file_basename] = $config;
        return $config;
    }
    protected function loadFile($file)
    {
        return require $file;
    }
    protected function getAbsPath($parent_path, $path)
    {
        $is_abs_path = false;
        if (DIRECTORY_SEPARATOR === '/') {
            //Linux
            if (substr($path, 0, 1) === '/') {
                $is_abs_path = true;
            }
        } else { // @codeCoverageIgnoreStart
            // Windows
            if (preg_match('/^(([a-zA-Z]+:(\\|\/\/?))|\\\\|\/\/)/', $path)) {
                $is_abs_path = true;
            }
        }   // @codeCoverageIgnoreEnd
        if ($is_abs_path) {
            return $path;
        } else {
            return $parent_path.$path;
        }
    }
}
