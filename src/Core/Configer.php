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
    ];
    protected $all_config = [];
    public function _Config($file_basename = 'config', $key = null, $default = null)
    {
        //TODO $filename_basename = '';
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
