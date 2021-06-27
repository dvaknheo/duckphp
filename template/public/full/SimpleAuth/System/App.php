<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;

use DuckPhp\DuckPhp;
use DuckPhp\Component\AppPluginTrait;
use DuckPhp\Component\Console;
use DuckPhp\Ext\SqlDumper;

class App extends DuckPhp
{
    use AppPluginTrait;
    public $is_plugin = false;

    //@override
    public $plugin_options = [
        // simple_auth_installed = false,
    ];
    //@override
    public $options = [
        // simple_auth_installed = false,
    ];
    public function __construct()
    {
        $this->plugin_options['plugin_path']=realpath(__DIR__.'/../');
        parent::__construct();
    }
    
    protected function onPluginModeInit()
    {
        $this->is_plugin = true;
        self::G(static::G());
        Console::G()->regCommandClass(static::class,  'SimpleAuth');
    }
    protected function onBeforeRun()
    {
        $this->checkInstall($this->options['simple_auth_installed'] ?? false);
    }
    protected function onPluginModeBeforeRun()
    {
        $this->checkInstall($this->plugin_options['simple_auth_installed'] ?? false);
    }
    protected function checkInstall($flag)
    {
        if(!$flag  && !static::Setting('simple_auth_installed')){
            throw new \ErrorException("SimpleAuth` need install, run install command first. e.g. :`php auth.php SimpleAuth:install`\n");
        }
    }
    //////////////////////
    public function command_install()
    {
        $options = Console::G()->getCliParameters();
        if(count($options)==1 || $options['help']??null){
            echo "Usage: --host=? --port=? --dbname=? --username=? --password=? \n ";
            return;
        }
        $options['path'] = static::IsPluginMode() ? $this->plugin_options['plugin_path']:$this->options['path']
        Installer::G()->install($options);
    }
    ////////
    public static function IsPluginMode()
    {
        return static::G()->is_plugin;
    }
    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
