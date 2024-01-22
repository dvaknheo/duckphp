<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp\Core;

use DuckPhp\Core\Console;
use DuckPhp\Core\EventManager;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\Route;
use DuckPhp\Core\Runtime;

trait InstallerTrait
{
    /**
     * Install
     */
    public function command_install($force=false)
    {
        //
    }
    protected function do_command_install($force=false)
    {
        if(!ExtOptionsLoader::_()->isInited()){
            ExtOptionsLoader::_()->init(App::Current()->options,App::Current());
        }
        if(!DatabaseInstaller::_()->isInited()){
            DatabaseInstaller::_()->init(App::Current()->options,App::Current());
        }
        if(!SqlDumper::_()->isInited()){
            SqlDumper::_()->init(App::Current()->options,App::Current());
        }
        if(!RedisInstaller::_()->isInited()){
            RedisInstaller::_()->init(App::Current()->options,App::Current());
        }
        
        //////////////////////////
        if ($this->is_root) {
            //
        }
        $flag = DatabaseInstaller::_()->callResetDatabase();
        if(!$flag){
            return;
        }
        
        ///////////////////////
        $options = ExtOptionsLoader::_()->loadExtOptions(true, $this);
        $options['install'] = DATE(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($options, $this);
        
        SqlDumper::_()->install();
        
        echo "App Version $version  Main Install Done , install ext Apps\n";
        ///////////////////////////
        foreach($this->options['app'] as $app => $options){
            echo "Installing App $app \n";
            // controller_url_prefix
            // controller_resource_prefix
            try{
                $app::_()->command_install($force);
            }catch(\Exception $ex){
                var_dump("Install failed");
            }
            echo "Install done\n";
        }
        echo "Install All Done.\n";
        return;
    }
    /**
     * Config
     */
    public function command_config($force=false)
    {
        //
    }
    protected function do_command_config($force=false)
    {
    }
}