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
        // simple_auth_installed = false,
    ];
    protected function onBeforeRun()
    {
        $this->checkInstall($this->options['simple_auth_installed'] ?? false);
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
        $options = static::Parameters();
        if(count($options)==1 || $options['help']??null){
            echo "Usage: --host=? --port=? --dbname=? --username=? --password=? \n ";
            return;
        }
        $tips = [
            'host' =>'input houst',
            'port' =>'port',
        ];
        $options['path'] = $this->getPath();
        Installer::G()->install($options);
    }
    protected function getPath()
    {
        return $this->options['path'];
    }
    public function getTablePrefix()
    {
        return static::Config('table_prefix','SimpleAuth')??'';
    }
    public function getSessionPrefix()
    {
        return static::Config('session_prefix','SimpleAuth')??'';
    }
    
    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
