<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp\Ext;

use DuckPhp\Core\Console;
use DuckPhp\Core\EventManager;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\App;
use DuckPhp\Core\Route;
use DuckPhp\Core\Runtime;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\SqlDumper;

trait InstallerTrait
{
    /**
     * Install. power by DuckPhp\Ext\InstallerTrait
     */
    public function command_install($force=false)
    {
        return $this->do_command_install($force);
    }
    /**
     * Config. power by DuckPhp\Ext\InstallerTrait
     */
    public function command_config($force=false)
    {
        return $this->do_commmand_config($force);
    }
    ///////////////
    protected function do_command_config($force=false)
    {
        DatabaseInstaller::_()->callResetDatabase($force);
        RedisInstaller::_()->callResetRedis($force);
        echo "config database, redis done.";
    }
    protected function do_command_install($force=false)
    {
        $classes =[
            ExtOptionsLoader::class,
            DatabaseInstaller::class,
            RedisInstaller::class,
            SqlDumper::class,
        ];
        foreach ($classes as $class) {
            if(!$class::_()->isInited()){
                $class::_()->init(App::Current()->options,App::Current());
            }
        }
        //////////////////////////
        if($this->is_root){
        $args =['need_database'=>false,'need_redis'=>false];
        
        $args = $this->rec_apps(App::Current(), function($app,$args){
            $need_database = isset($app->options['need_database']) ? $app->options['need_database'] : true;
            $need_redis = isset($app->options['need_redis']) ? $app->options['need_redis'] : true;
            $args['need_database'] = $args['need_database'] || $need_database;
            $args['need_redis'] = $args['need_redis'] || $need_redis;
            
            return $args;
        }, $args);
        if ($args['need_database']){
            echo "need database, config now: ";
            DatabaseInstaller::_()->callResetDatabase($force);
        }
        if ($args['need_redis']){
            echo "need redis, config now   : ";
            RedisInstaller::_()->callResetRedis($force);
        }
        }
        //////////////////////////
        
        echo "Installing App(".get_class($this)."):\n";
        
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, $this);

 $desc = <<<EOT
----
aa
----    
EOT;
$default_options  = [
    //
];
        //$default_options =array_replace_resc($this->options);
        $input_options = Console::_()->readLines($default_options, $desc);
        
        //sqldump
        //routehookrewrite;
        
        //////
        
        $options['install'] = DATE(DATE_ATOM);
        //ExtOptionsLoader::_()->saveExtOptions($options, $this);
        SqlDumper::_()->install();
        
        
        if (!empty($this->options['app'])) {
            echo "\nApp Version Install Done , install child Apps\n----------------\n";
        }
        ///////////////////////////
        foreach($this->options['app'] as $app => $options){
            $last_phase = App::Phase($app);
            try{
                $app::_()->command_install($force); //configed?,child
            }catch(\Exception $ex){
                $msg = $ex->getErrorMesage();
                var_dump("Install failed: $msg \n");
            }
            App::Phase($last_phase);
        }
        if($this->is_root){
            echo "Install All Done.\n";
        }
        return;
    }
    protected function on_install()
    {
        return;
    }
    //////////////////
    protected function rec_apps($object,$callback, $args)
    {
        $args = $callback($object,$args);
        foreach ($object->options['app'] as $app => $options) {
            $args = $this->rec_apps($app::_(),$callback,$args);
        }
        return $args;
    }
}