<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class Configer extends ComponentBase
{
    public $options = [
        'path' => '',
        'path_config' => 'config',
    ];
    protected $all_config = [];
    public function _Config($file_basename = 'config', $key = null, $default = null)
    {
        //TODO $filename_basename = '';
        if (!$this->is_inited) {
            $this->init(App::Current()->options, App::Current());
        }
        $config = $this->_LoadConfig($file_basename);
        if (!isset($key)) {
            return $config ?? $default;
        }
        return isset($config[$key])?$config[$key]:$default;
    }
    protected function _LoadConfig($file_basename)
    {
        if (isset($this->all_config[$file_basename])) {
            return $this->all_config[$file_basename];
        }
        
        $file = $file_basename.'.php';
        $full_file = $this->extendFullFile($this->options['path'], $this->options['path_config'], $file);
        if (!is_file($full_file)) {
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
