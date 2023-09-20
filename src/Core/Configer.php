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
            $this->dealWithSettingFile();
        }
    }
    protected function dealWithSettingFile()
    {
        if (static::IsAbsPath($this->options['setting_file'])) {
            $full_file = $this->options['setting_file'];
        } elseif (static::IsAbsPath($this->options['path_config'])) {
            $full_file = static::SlashDir($this->options['path_config']) . $this->options['setting_file'];
        } else {
            $full_file = static::SlashDir($this->options['path']) . static::SlashDir($this->options['path_config']) . $this->options['setting_file'];
        }
        if (!is_file($full_file)) {
            $this->exitWhenNoSettingFile($full_file, $this->options['setting_file']);
        } else {
            $setting = $this->loadFile($full_file);
            $this->setting = array_merge($this->setting, $setting);
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
        
        $file = $file_basename.'.php';
        $full_file = ComponentBase::GetFileFromSubComponent($this->options, 'config', $file);
        if (!$full_file) {
            $this->all_config[$file_basename] = [];
            return [];
        }
        $config = $this->loadFile($full_file);
        
        $this->all_config[$file_basename] = $config;
        return $config;
    }
    protected function loadFile($file)
    {
        return require $file;
    }
}
