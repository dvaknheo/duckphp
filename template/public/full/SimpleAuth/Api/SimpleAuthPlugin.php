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
            'simple_auth_check_installed' => true,
            'simple_auth_table_prefix' => '',
            'simple_auth_session_prefix' => '',
    ];
    public function __construct()
    {
        $this->plugin_options['plugin_path'] = realpath(__DIR__.'/../').'/';
        $this->plugin_options['plugin_search_config'] = false;
        
        parent::__construct();
    }
    protected function onPluginModeInit()
    {
        $this->is_plugin = true;
        App::G(static::G());
        
        //copy options
        foreach($this->options as $k => $v){
            if(isset($this->plugin_options[$k])){
                $this->options[$k]= $this->plugin_options[$k];
            }
        }
        Console::G()->regCommandClass(static::class,  'SimpleAuth');
    }
    protected function onPluginModeBeforeRun()
    {
        $this->checkInstall();
    }
    public function getPath()
    {
        return $this->plugin_options['plugin_path'];
    }
}