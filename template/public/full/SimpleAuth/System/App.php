<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    //@override
    public $options = [
        'simple_auth_installed' => false,  // 检查安装
        'simple_auth_table_prefix' => '',   // 表前缀
        'simple_auth_session_prefix' => '',  // Session 前缀
    ];
    public function __construct()
    {
        parent::__construct();
    }
    public function getTablePrefix()
    {
        return $this->options['simple_auth_table_prefix'];
    }
    public function getSessionPrefix()
    {
        return $this->options['simple_auth_session_prefix'];
    }
    ///////////////////
    protected function onBeforeRun()
    {
        //$this->checkInstall();
    }
    ////[[[[
    protected function checkInstall()
    {
        //$this->getInstaller()->checkInstall();
    }
    public function install($parameters)
    {
        //return $this->getInstaller()->install($parameters);
    }
    ////////////////////////
    protected function getInstaller()
    {
        $options = [
            'installer_table_prefix' => $this->options[ 'duckadmin_table_prefix'],
        ];
        
        if ($this->options['simple_auth_installed'] || static::Setting('simple_auth_installed') ){
            return;
        }
        $has_database = (static::Setting('database') ||  static::Setting('database_list')) ? true : false;
        return Installer::G()->init($options,$this);
    }
    ////]]]]
    //////////////////////
    public function command_install()
    {
        echo "welcome to Use SimplAuth installer  --force  to force install\n";
        $parameters =  static::Parameter();
        if(count($parameters)==1 || ($parameters['help'] ?? null)){
            // echo "--force  to force install ;";
            //return;
        }
        echo $this->install($parameters);
        echo "Done \n";
    }
}
