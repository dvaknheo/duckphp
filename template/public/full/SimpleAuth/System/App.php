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
        $this->checkInstall();
    }
    protected function checkInstall()
    {
        if(!Installer::G()->isInstalled()){
            throw new \ErrorException("`SimpleAuth` need install. run install command first. e.g. :`php auth.php SimpleAuth:install`\n");
        }
    }
    //////////////////////
    public function command_install()
    {
        echo "welcome to Use SimplAuth installer  --force  to force install\n";
        $parameters =  static::Parameter();
        if(count($parameters)==1 || ($parameters['help'] ?? null)){
            // echo "--force  to force install ;";
            //return;
        }
        $options = [
            'sql_dump_inlucde_tables' =>['Users'],
            'force' => $parameters['force']?? false,
            'path' => $this->getPath(),
            'path_sql_dump' => 'config',
        ];
        Installer::G()->init($options,$this);
        
        if(Installer::G()->isInstalled()){
           echo "You had been installed ";
           return; 
        }
        echo Installer::G()->run();        
        echo "Done \n";
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
