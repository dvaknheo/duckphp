<?php

declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class ExtOptionsLoader extends ComponentBase
{
    public $options = [
        'ext_options_file_enable' => true,
        'ext_options_file_name' => 'DuckPhpExtData.config.json',
        'ext_options_allow_init_replace' => true,
        'ext_options_to_parent_options' => ['installed', 'redis', 'database', 'local_redis', 'local_database'],
        'ext_options_to_parent_prefix' => ['redis_', 'database_'],
    ];
    public static $all_ext_options = null;
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        
        if (!isset(self::$all_ext_options)) {
            $full_file = $this->get_ext_options_file();
            if (!is_file($full_file)) {
                return;
            }
            $this->fill_all_ext_options($full_file);
        }
        $class = App::Current()->getOverridingClass();
        $ext_options = self::$all_ext_options[$class] ?? null;
        if (empty($ext_options)) {
            return;
        }
        $this->replaceParentOptions($ext_options);
    }
    protected function replaceParentOptions($ext_options)
    {
        if (!$this->options['ext_options_allow_init_replace']) {
            return;
        }
        $app = App::Current();
        $app->options['ext_options'] = $ext_options;
        foreach ($this->options['ext_options_to_parent_options'] as $key) {
            if (isset($ext_options[$key])) {
                $app->options[$key] = $ext_options[$key];
            }
        }
        foreach ($this->options['ext_options_to_parent_prefix'] as $prefix) {
            foreach ($ext_options as $key => $value) {
                if (substr($key, 0, strlen($prefix)) === $prefix) {
                    $app->options[$key] = $value;
                }
            }
        }
    }
    protected function get_ext_options_file()
    {
        $full_file = App::Root()->options['ext_options_file_name'] ?? $this->options['ext_options_file_name'];
        
        $path = static::SlashDir(App::Root()->options['path']);
        $path_runtime = static::SlashDir(App::Root()->options['path_runtime']);
        $path_runtime = static::IsAbsPath($path_runtime) ? $path_runtime : $path.$path_runtime;
        $is_abs = (DIRECTORY_SEPARATOR === '/') ?(substr($full_file, 0, 1) === '/'):(preg_match('/^(([a-zA-Z]+:(\\|\/\/?))|\\\\|\/\/)/', $full_file));
        $full_file = $is_abs ? $full_file : static::SlashDir($path_runtime).$full_file;
        
        return $full_file;
    }
    protected function fill_all_ext_options($full_file)
    {
        $all_ext_options = json_decode(file_get_contents($full_file),true);
        self::$all_ext_options = $all_ext_options;
    }
    public function saveData($options)
    {
        $full_file = $this->get_ext_options_file();
        $class = App::Current()->getOverridingClass();
        $ext_options = array_replace_recursive(self::$all_ext_options[$class] ?? [], $options);
        self::$all_ext_options[$class] = $ext_options;
        $all_ext_options = self::$all_ext_options;
        $all_ext_options['__date'] = date('Y-m-d H:i:s');
        
        $string = json_encode($all_ext_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        file_put_contents($full_file, $string);
        clearstatcache();

        $this->replaceParentOptions($ext_options);
    }
}
