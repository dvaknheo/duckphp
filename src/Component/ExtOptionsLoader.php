<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class ExtOptionsLoader extends ComponentBase
{
    public static $all_ext_options;
    
    protected function get_ext_options_file()
    {
        $full_file = App::Root()->options['ext_options_file'];
        
        $path = App::Root()->options['path'];
        $path = ($path !== '') ? rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR : '';

        $is_abs = (DIRECTORY_SEPARATOR === '/') ?(substr($full_file, 0, 1) === '/'):(preg_match('/^(([a-zA-Z]+:(\\|\/\/?))|\\\\|\/\/)/', $full_file));
        $full_file = $is_abs ? $full_file : static::SlashDir($path).$full_file;
        return $full_file;
    }
    protected function get_all_ext_options($force = false)
    {
        if (!$force && isset(self::$all_ext_options)) {
            return self::$all_ext_options;
        }
        $full_file = $this->get_ext_options_file();
        if (!is_file($full_file)) {
            return [];
        }
        $all_ext_options = (function ($file) {
            return require $file;
        })($full_file);
        self::$all_ext_options = $all_ext_options;
        return self::$all_ext_options;
    }
    public function loadExtOptions($force = false, $class = null)
    {
        $class = $class ?? get_class(App::Current());
        $class = is_string($class)?$class:get_class($class);
        $all_options = $this->get_all_ext_options($force);
        $options = $all_options[$class] ?? [];
        $class::_()->options = array_replace_recursive($class::_()->options, $options);
        return $options;
    }
    public function saveExtOptions($options, $class = null )
    {
        $class = $class ?? get_class(App::Current());
        $class = is_string($class)?$class:get_class($class);
        
        $full_file = $this->get_ext_options_file();
        static::$all_ext_options = $this->get_all_ext_options(true);
        static::$all_ext_options[$class] = $options;
        
        $string = "<"."?php //". "regenerate by " . __CLASS__ . '->'.__FUNCTION__ ." at ". DATE(DATE_ATOM) . "\n";
        $string .= "return ".var_export(static::$all_ext_options, true) .';';
        file_put_contents($full_file, $string);
        
        clearstatcache();
    }
}
