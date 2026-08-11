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
        'data_file_enable' => true,
        'data_file_json_file' => 'DuckPhpData.config.json',
        'data_file_bump_allowed' => true,
        'data_file_bump_keys' => ['installed' => true, 'redis' => true, 'database' => true, 'local_redis' => true, 'local_database' => true],
        'data_file_bump_prefix_keys' => ['redis_' => true, 'database_' => true],
    ];
    public $all_ext_options = null;
    protected function getRoot()
    {
        $last_phase = App::Phase(App::Root()->getThisPhaseName());
        $root = ExtOptionsLoader::_();
        App::Phase($last_phase);
        return $root;
    }
    /**
     * @param array<string, mixed> $options
     * @param object|null $context
     * @return $this
     */
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        $root = $this->getRoot();

        if (!isset($root->all_ext_options)) {
            $full_file = $this->get_ext_options_file();
            if (!is_file($full_file)) {
                $root->all_ext_options = [];
                return $this;
            }
            $this->fill_all_ext_options($full_file);
        }
        $phase = App::_()->getThisPhaseName();
        $ext_options = $root->all_ext_options[$phase] ?? [];

        $this->bumpOptions($ext_options);
        return $this;
    }
    /**
     * @param array<string, mixed> $ext_options
     */
    public function bumpOptions(array $ext_options): void
    {
        if (empty($ext_options)) {
            return;
        }
        if (!$this->options['data_file_bump_allowed']) {
            return;
        }
        $app = App::_();
        $app->options['data'] = $ext_options;
        foreach ($this->options['data_file_bump_keys'] as $key => $enabled) {
            if ($enabled) {
                $app->options[$key] = $ext_options[$key] ?? null;
            }
        }
        foreach ($this->options['data_file_bump_prefix_keys'] as $prefix => $enabled) {
            if (!$enabled) {
                continue;
            }
            foreach ($ext_options as $key => $value) {
                if (substr($key, 0, strlen($prefix)) === $prefix) {
                    $app->options[$key] = $value;
                }
            }
        }
    }
    protected function get_ext_options_file(): string
    {
        $full_file = $this->options['data_file_json_file'] ?? $this->getRoot()->options['data_file_json_file'];
        $path_runtime = App::_()->getRuntimePath();
        $is_abs = (DIRECTORY_SEPARATOR === '/') ?(substr($full_file, 0, 1) === '/'):(preg_match('/^(([a-zA-Z]+:(\\|\/\/?))|\\\\|\/\/)/', $full_file));
        $full_file = $is_abs ? $full_file : $path_runtime.$full_file;

        return $full_file;
    }
    protected function fill_all_ext_options(string $full_file): void
    {
        $all_ext_options = json_decode(''.file_get_contents($full_file), true);
        $this->getRoot()->all_ext_options = $all_ext_options;
    }
    /**
     * @param array<string, mixed> $options
     */
    public function saveExtOptions(array $options): void
    {
        $full_file = $this->get_ext_options_file();
        $phase = App::_()->getThisPhaseName();
        $root = $this->getRoot();
        $options['__class__'] = get_class(App::_());

        $ext_options = array_replace_recursive($root->all_ext_options[$phase] ?? [], $options);
        $root->all_ext_options[$phase] = $ext_options;
        $all_ext_options = $root->all_ext_options;
        $all_ext_options['__date__'] = date('Y-m-d H:i:s');

        $string = json_encode($all_ext_options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        file_put_contents($full_file, $string);
        clearstatcache();

        $this->bumpOptions($ext_options);
    }
}
