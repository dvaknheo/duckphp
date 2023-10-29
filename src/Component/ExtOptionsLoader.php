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

    public function installWithExtOptions($class, $options)
    {
        $options['install'] = DATE(DATE_ATOM);
        $this->options = array_replace_recursive($this->options, $options);
        $this->saveExtOptions($class, $options);
    }
    protected function get_ext_options_file()
    {
        $full_file = App::Root()->options['ext_options_file'];
        $full_file = static::IsAbsPath($full_file) ? $full_file : static::SlashDir(App::Root()->options['path']).$full_file;
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
    public function loadExtOptions()
    {
        $class = App::Phase();
        $all_options = $this->get_all_ext_options();
        $options = $all_options[$class] ?? [];
        App::Current()->options = array_replace_recursive(App::Current()->options, $options);
    }
    protected function saveExtOptions($class, $options)
    {
        $full_file = $this->get_ext_options_file();
        
        $all_options = $this->get_all_ext_options(true);
        $all_options[$class] = $options;
        
        $string = "<"."?php //". "regenerate by " . __CLASS__ . '->'.__METHOD__ ." at ". DATE(DATE_ATOM) . "\n";
        $string .= "return ".var_export($all_options, true) .';';
        file_put_contents($full_file, $string);
        clearstatcache();
    }
}
