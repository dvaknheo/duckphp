<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Api;

use DuckPhp\Component\AppPluginTrait;
use DuckPhp\Component\Console;
use SimpleAuth\System\App;

class SimpleAuthPlugin extends App
{
    use AppPluginTrait;
    public $is_plugin = false;

    //@override
    public $plugin_options = [
        // simple_auth_installed = false,
    ];
    public function __construct()
    {
        $this->plugin_options['plugin_path'] = realpath(__DIR__.'/../').'/';
        parent::__construct();
    }
    protected function onPluginModeInit()
    {
        $this->is_plugin = true;
        App::G(static::G());
        Console::G()->regCommandClass(static::class,  'SimpleAuth');
    }
    protected function onPluginModeBeforeRun()
    {
        $this->checkInstall($this->plugin_options['simple_auth_installed'] ?? false);
    }
    protected function getPath()
    {
        return $this->plugin_options['plugin_path'];
    }
}