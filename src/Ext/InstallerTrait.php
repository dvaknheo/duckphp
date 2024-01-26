<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp\Ext;

use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\SqlDumper;
use DuckPhp\Core\App;
use DuckPhp\Core\Console;
use DuckPhp\Core\EventManager;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\Route;
use DuckPhp\Core\Runtime;

trait InstallerTrait
{
    /**
     * Install. power by DuckPhp\Ext\InstallerTrait
     */
    public function command_install($force = false)
    {
        return $this->do_command_install($force);
    }
    /**
     * Config. power by DuckPhp\Ext\InstallerTrait
     */
    public function command_config($force = false)
    {
        return $this->do_commmand_config($force);
    }
    /**
     * Dump sql. power by DuckPhp\Ext\InstallerTrait
     */
    public function command_dumpsql()
    {
        //SqlDumper
    }
    ///////////////
    protected function do_command_config($force = false)
    {
        $args = ['install_need_database' => false,'install_need_redis' => false];
        
        $args = $this->rec_apps(App::Current(), function ($app, $args) {
            $install_need_database = isset($app->options['install_need_database']) ? $app->options['install_need_database'] : true;
            $install_need_redis = isset($app->options['install_need_redis']) ? $app->options['install_need_redis'] : true;
            $args['install_need_database'] = $args['install_need_database'] || $install_need_database;
            $args['install_need_redis'] = $args['install_need_redis'] || $install_need_redis;
            
            return $args;
        }, $args);
        if ($args['install_need_database']) {
            echo "need database, config now: ";
            DatabaseInstaller::_()->callResetDatabase($force);
        }
        if ($args['install_need_redis']) {
            echo "need redis, config now   : ";
            RedisInstaller::_()->callResetRedis($force);
        }
        echo "config database, redis done.";
    }
    protected function do_command_install($force = false)
    {
        $classes = [
            ExtOptionsLoader::class,
            DatabaseInstaller::class,
            RedisInstaller::class,
            SqlDumper::class,
        ];
        foreach ($classes as $class) {
            if (!$class::_()->isInited()) {
                $class::_()->init(App::Current()->options, App::Current());
            }
        }
        //////////////////////////
        if ($this->is_root) {
            $this->do_command_config($force = false);
        }
        //////////////////////////
        
        echo "Installing App (".get_class($this)."):\n";
        
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, $this);
        $desc = $this->options['install_input_desc'] ?? '';
        $desc = "----\n".$desc."\n----\n";
        $default_options = $this->options['install_options'] ?? [];
        $default_options = array_replace_recursive($this->options, $ext_options, $default_options);
        $input_options = Console::_()->readLines($default_options, $desc);
        
        $ext_options = array_replace_recursive($ext_options, $input_options);
        $this->options = array_replace_recursive($this->options, $ext_options);
        
        //SqlDumper::_()->run();
        //RouteHookrewrite::_()->cloneResource($force,$info);
        
        $this->do_install($ext_options);
        $ext_options['install'] = DATE(DATE_ATOM);
        ExtOptionsLoader::_()->saveExtOptions($ext_options, $this);
        $this->on_install();
        
        if (!empty($this->options['app'])) {
            echo "\nInstall child apps\n----------------\n";
        }
        ///////////////////////////
        foreach ($this->options['app'] as $app => $options) {
            $last_phase = App::Phase($app);
            try {
                $app::_()->command_install($force); //configed?,child
            } catch (\Exception $ex) {
                $msg = $ex->getErrorMesage();
                var_dump("Install failed: $msg \n");
            }
            App::Phase($last_phase);
        }
        if ($this->is_root) {
            echo "Install All Done.\n";
        }
        return;
    }
    protected function do_install($ext_options)
    {
        //
    }
    protected function on_install()
    {
        var_dump($this->options);
        return;
    }
    //////////////////
    protected function rec_apps($object, $callback, $args)
    {
        $args = $callback($object, $args);
        foreach ($object->options['app'] as $app => $options) {
            $args = $this->rec_apps($app::_(), $callback, $args);
        }
        return $args;
    }
}
