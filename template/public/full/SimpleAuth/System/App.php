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
    protected $is_plugin = false;

    //@override
    public $plugin_options = [
        //simple_auth_installed = true,
    ];
    //@override
    public $options = [
        'use_setting_file' => true, // 启用设置文件
        
        'error_404' =>'_sys/error-404',
        'error_500' => '_sys/error-exception',
        'ext' => [
        ],
        //simple_auth_installed = true,  // 这就安装了
    ];

    //
    public function __construct()
    {
        $this->plugin_options['plugin_path']=__DIR__.'/../';
        parent::__construct();
    }
    public function command_install()
    {
        $options = Console::G()->getCliParameters();
        if(count($options)==1 || $options['help']??null){
            echo "Usage: --host=? --port=? --dbname=? --username=? --password=? \n ";
        }
        $this->install($options);
        var_dump("Done!");
    }
    protected function onPluginModePrepare()
    {
        $this->is_plugin = true;
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
            throw new \ErrorException("simpleAuth` need install, run install command first. e.g. :`php auth.php SimpleAuth:install`\n");
        }
        
    }
    protected function install($options)
    {
        return Installer::G()->install($options);
    }
    protected function dumpSql()
    {
        $sqldumper_options = [
            'path'=>($this->plugin_context_class)::G()->options['path'],
            'sql_dump_inlucde_tables' =>['Users'],
        ];
        SqlDumper::G()->init($sqldumper_options, ($this->plugin_context_class)::G());
        SqlDumper::G()->run();
    }
//////////////////////
    public static function SessionManager()
    {
        return SessionManager::G();
    }

}
