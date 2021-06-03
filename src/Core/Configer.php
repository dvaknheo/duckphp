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
        
        'setting_file' => 'setting',
        'setting_file_ignore_exists' => false,
        'use_setting_file' => false,
        'use_env_file' => false,
        'config_ext_file_map' => [],
    ];
    protected $path;
    protected $all_config = [];
    protected $setting = [];
    
    //@override
    protected function initOptions(array $options)
    {
        $this->path = parent::getComponenetPathByKey('path_config');
        
        $this->setting = $this->options['setting'] ?? [];
        $this->all_config = $this->options['all_config'] ?? [];
        
        if ($this->options['use_env_file']) {
            $env_setting = parse_ini_file(realpath($this->options['path']).'/.env');
            $env_setting = $env_setting?:[];
            $this->setting = array_merge($this->setting, $env_setting);
        }
        if ($this->options['use_setting_file']) {
            $setting_file = $this->options['setting_file'];
            $full_setting_file = $this->path.$setting_file.'.php';
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
    private function exitWhenNoSettingFile($full_setting_file, $setting_file)
    {
        if ($this->options['setting_file_ignore_exists']) {
            return;
        }
        throw new \ErrorException('DuckPhp: no Setting File');
    }
    
    public function _Config($key, $file_basename = 'config')
    {
        $config = $this->_LoadConfig($file_basename);
        return isset($config[$key])?$config[$key]:null;
    }
    public function _LoadConfig($file_basename = 'config')
    {
        if (isset($this->all_config[$file_basename])) {
            return $this->all_config[$file_basename];
        }
        $full_file = $this->path.$file_basename.'.php';
        if (isset($this->options['config_ext_file_map'][$file_basename]) && !is_file($full_file)) {
            $full_file = $this->options['config_ext_file_map'][$file_basename];
            $config = $this->loadFile($full_file);
        } else {
            $config = $this->loadFile($full_file);
        }
        $this->all_config[$file_basename] = $config;
        return $config;
    }
    protected function loadFile($file)
    {
        return require $file;
    }
}
